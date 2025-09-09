<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Menu;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Credit;
use App\Models\Payment;
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use PayPal\Auth\OAuthTokenCredential;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Order $order = null)
    {
        if(Auth::user()->user_group_id == 3) {
            return Inertia::render('Orders/CustomerIndex', [
                'credits' => Auth::user()->credits,
                'orders' => Order::where('customer_id', Auth::user()->id)->where('status', '<>', 2)->orderBy('created_at', 'desc')->with(['first_dish', 'second_dish', 'side_dish', 'menu'])->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    return $order;
                }),
                'order' => $order?->load('menu'),
            ]);
        } else {
            return Inertia::render('Orders/Index', [
                'orders' => Order::with(['customer', 'first_dish', 'second_dish', 'side_dish', 'menu'])->orderBy('created_at', 'desc')->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    $order->child_name = $order->customer->child . " ";

                    $order->child_name .= count(explode(' ', $order->customer->child)) == 1 ? $order->customer->surname : '';
                    return $order;
                }),
                'order_statuses' => Order::get_statuses(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $startDate = Carbon::now()->startOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');

        if(in_array(Carbon::now()->locale('it_IT')->dayName, ['sabato', 'domenica'])) {
            $startDate = Carbon::now()->addWeek()->startOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
            $endDate = Carbon::now()->addWeek()->endOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
        }

        $equal_or_after = Carbon::now()->setTimezone('Europe/Rome')->format('H:i') >= '10:00' ? '>' : '>=';

        return Inertia::render('Orders/Create', [
            'menus' => Menu::where('is_active', true)->whereDate('start_date', $equal_or_after,  $startDate)->whereDate('end_date', '<=', $endDate)->orderBy('validity_date')->with('products')->get(),
            'order' => new Order(),
            'statuses' => Order::get_statuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Order::validate($request);

        $menu = Menu::with('products')->find($request->menu_id);
        $validated['subtotal_amount'] = $menu->price;
        $validated['total_amount'] = $menu->price;
        $validated['to_be_paid'] = $menu->price;

        if($request->first_dish_id && $request->second_dish_id) {
            $validated['subtotal_amount'] += $menu->second_price;
            $validated['total_amount'] += $menu->second_price;
            $validated['to_be_paid'] += $menu->second_price;
        }

        $validated['order_date'] = \Carbon\Carbon::create($request->order_date)->setTimezone('Europe/Rome')->format('Y-m-d');

        $order = Order::create($validated);

        return redirect()->route('orders.index', compact('order'))->with('success', 'Ordine creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $startDate = Carbon::now()->startOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');

        if(in_array(Carbon::now()->locale('it_IT')->dayName, ['sabato', 'domenica'])) {
            $startDate = Carbon::now()->addWeek()->startOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
            $endDate = Carbon::now()->addWeek()->endOfWeek()->setTimezone('Europe/Rome')->format('Y-m-d');
        }

        return Inertia::render('Orders/Edit', [
            'menus' => Menu::where('is_active', true)->whereDate('start_date', '>=',  $startDate)->whereDate('end_date', '<=', $endDate)->orderBy('validity_date')->with('products')->get(),
            'order' => $order->load('customer'),
            'order_statuses' => Order::get_statuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = Order::validate($request);

        $validated['total_amount'] = (Float)$order->subtotal_amount - ($request->discount ?? 0);
        $validated['to_be_paid'] = (Float)$order->subtotal_amount - ($request->discount ?? 0);

        $validated['order_date'] = \Carbon\Carbon::create($request->order_date)->setTimezone('Europe/Rome')->format('Y-m-d');

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Ordine aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $message = 'Ordine eliminato con successo.';

        if($order->payments->count() > 0) {
            if(Carbon::now()->format('Y-m-d H:i') < Carbon::create($order->order_date . '10:00:00')->format('Y-m-d H:i')) {
                Credit::create([
                    'user_id' => $order->customer_id,
                    'total' => $order->total_amount,
                    'amount_available' => $order->total_amount,
                    'description' => 'Creato generato per ordine #' . $order->id,
                ]);

                $message .= ' Credito generato per l\'importo di ' . number_format($order->total_amount, 2) . '€.';
            } else {
                $message .= ' Credito non emesso in quanto l\'eliminazione è avvenuta dopo le 10:00.';
            }
        }

        $order->update(['status' => 2]);

        return redirect()->route('orders.index')->with('message', $message);
    }

    public function change_status(Order $order, Request $request) {
        $request->validate([
            'status' => 'required|in:0,1,2,3'
        ]);

        if($request->status == 1 && $order->status != 1) {
            // If the order status was not "Paid" and is now set to "Paid", create a payment record
            Payment::create([
                'user_id' => $order->customer_id,
                'amount' => $order->total_amount,
                'payment_date' => \Carbon\Carbon::now()->setTimezone('Europe/Rome')->format('Y-m-d'),
                'notes' => 'Inserimento manuale pagamento dell\'ordine #' . $order->id,
                'order_id' => $order->id,
                'status' => 1,
                'payment_method_id' => 1,
            ]);
        } else if ($order->status == 1 && $request->status != 1) {
            // If the order status was "Paid" and is now changed to something else, refund the amount to user's credit
            Credit::create([
                'user_id' => $order->customer_id,
                'total' => $order->total_amount,
                'amount_available' => $order->total_amount,
                'description' => 'Creato generato per ordine #' . $order->id,
            ]);

            $order->to_be_paid = $order->total_amount;
        }

        $order->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('message', 'Stato ordine aggiornato con successo.');
    }

    public function confirm(Order $order, Request $request) {
        if($request->payment_method == 2) {

            $transactionid = $this->set_paypal_payment($request);

            if(!$transactionid)
                return redirect()->route('orders.index')->withErrors('Si è verificato un errore durante l\'elaborazione del pagamento PayPal. Ordine non confermato.');
        }

        $payment_method = PaymentMethod::find($request->payment_method);

        Payment::create([
            'user_id' => $order->customer_id,
            'amount' => $order->total_amount,
            'payment_date' => \Carbon\Carbon::now()->setTimezone('Europe/Rome')->format('Y-m-d'),
            'notes' => "Pagamento effettuato tramite {$payment_method->name}",
            'order_id' => $order->id,
            'status' => 1,
            'payment_method_id' => $request->payment_method,
            'stripe_session_id' => $request->session_id ?? null,
            'transaction_id' => $request->payment_method == 2 ? $transactionid : null,
        ]);

        $order->update([
            'status' => 1,
            'to_be_paid' => 0,
        ]);

        return redirect()->route('orders.index')->with('message', 'Pagamento registrato con successo. Grazie!');
    }

    public function payment_not_completed(Order $order) {
        return redirect()->route('orders.index')->withErrors('Il pagamento non è stato completato. Ordine non confermato.');
    }

    public function set_paypal_payment(Request $request) {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),
                env('PAYPAL_CLIENT_SECRET')
            )
        );

        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        $paypalPayment = \PayPal\Api\Payment::get($paymentId, $apiContext);
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            $result = $paypalPayment->execute($execution, $apiContext);
            $transactionid = $result->getTransactions()[0]->getRelatedResources()[0]->getSale()->getId();
            return $transactionid;
        } catch (\Exception $ex) {
            return false;
        }
    }

}
