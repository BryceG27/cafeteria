<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Menu;
use App\Models\User;
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
    public function index(Request $request)
    {
        if(Auth::user()->user_group_id == 3) {
            $orders_to_be_paid = [];
            if($request->has('orders'))
                $orders_to_be_paid = Order::whereIn('id', $request->orders)->where('to_be_paid', '>', 0)->with('menu')->get();

            return Inertia::render('Orders/CustomerIndex', [
                'credits' => Auth::user()->credits,
                'orders' => Order::where('customer_id', Auth::user()->id)->where('status', '<>', 2)->orderBy('created_at', 'desc')->with(['first_dish', 'second_dish', 'side_dish', 'menu'])->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    return $order;
                }),
                'orders_to_be_paid' => $orders_to_be_paid,
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

    public function dashboard() {
        $ordered_food = [];

        $orders = Order::where('status', '<>', 2)->whereBetween('order_date', [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->endOfMonth()->format('Y-m-d')])->with(['customer', 'first_dish', 'second_dish', 'side_dish', 'menu', 'payments'])->orderBy('created_at', 'desc')->get()->map(function($order) use (&$ordered_food) {
                    $order->status_info = $order->get_status();

                    $order->child_name = $order->customer->child . " ";
                    $order->child_name .= count(explode(' ', $order->customer->child)) == 1 ? $order->customer->surname : '';

                    if ($order->first_dish) {
                        if (! array_key_exists($order->first_dish->id, $ordered_food))
                            $ordered_food[$order->first_dish->id] = ['name' => $order->first_dish->name, 'count' => 0];
                        $ordered_food[$order->first_dish->id]['count']++;
                        $ordered_food[$order->first_dish->id]['ordered_by'][] = $order->customer->child;
                    }

                    if ($order->second_dish) {
                        if (! array_key_exists($order->second_dish->id, $ordered_food))
                            $ordered_food[$order->second_dish->id] = ['name' => $order->second_dish->name, 'count' => 0];
                        $ordered_food[$order->second_dish->id]['count']++;
                        $ordered_food[$order->second_dish->id]['ordered_by'][] = $order->customer->child;
                    }

                    if ($order->side_dish) {
                        if (! array_key_exists($order->side_dish->id, $ordered_food))
                            $ordered_food[$order->side_dish->id] = ['name' => $order->side_dish->name, 'count' => 0];
                        $ordered_food[$order->side_dish->id]['count']++;
                        $ordered_food[$order->side_dish->id]['ordered_by'][] = $order->customer->child;
                    }

                    return $order;
                });

        $orders_month_ago = Order::where('status', '<>', 2)->whereBetween('order_date', [Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'), Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d')])->orderBy('created_at', 'desc')->get();

        arsort($ordered_food);
        $ordered_food = array_slice($ordered_food, 0, 5);

        $payments = Payment::where('status', 1)->get();

        $customers = User::where('user_group_id', 3)->where('is_active', true)->with('user_group', 'orders', 'payments')->get();
        return Inertia::render('Dashboard', compact('orders', 'customers', 'ordered_food', 'orders_month_ago', 'payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek(CARBON::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
        $equal_or_after = Carbon::now()->setTimezone('Europe/Rome')->format('H:i') >= '10:00' ? '>' : '>=';

        if(in_array(Carbon::now()->locale('it_IT')->dayName, ['sabato', 'domenica'])) {
            $startDate = Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
            $endDate = Carbon::now()->addWeek()->endOfWeek(CARBON::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
            // $equal_or_after = '<>';
        }

        $menus = Menu::where('is_active', true)->whereDate('start_date', '>=', $startDate)->whereDate('end_date', '<=', $endDate)->whereDate('validity_date', $equal_or_after, Carbon::now()->format('Y-m-d'))->orderBy('validity_date')->with('products')->get();

        return Inertia::render('Orders/Create', [
            'menus' => $menus,
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
        $ids = [ $order->id ];

        return redirect()->route('orders.index', ['orders' => $ids])->with('success', 'Ordine creato con successo.');
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
       $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek(CARBON::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d');

        if(in_array(Carbon::now()->locale('it_IT')->dayName, ['sabato', 'domenica'])) {
            $startDate = Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
            $endDate = Carbon::now()->addWeek()->endOfWeek(CARBON::SUNDAY)->setTimezone('Europe/Rome')->format('Y-m-d');
        }

        $equal_or_after = Carbon::now()->setTimezone('Europe/Rome')->format('H:i') >= '10:00' ? '>' : '>=';

        $menus = Menu::where('is_active', true)->whereDate('start_date', '>=', $startDate)->whereDate('end_date', '<=', $endDate)->whereDate('validity_date', $equal_or_after, Carbon::now()->format('Y-m-d'))->orderBy('validity_date')->with('products')->get();

        return Inertia::render('Orders/Edit', [
            'menus' => $menus,
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
                foreach ($order->payments as $payment) {
                    Credit::create([
                        'user_id' => $order->customer_id,
                        'total' => $payment->pivot->amount,
                        'amount_available' => $payment->pivot->amount,
                        'description' => 'Credito generato per pagamento #' . $payment->id . ' relativo all\'ordine #' . $order->id,
                    ]);
                }

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

    public function confirm_multiples(Request $request) {
        $orders = Order::whereIn('id', explode('-', $request->orders))->get();
        if($orders->isEmpty())
            return redirect()->route('orders.index')->withErrors('Nessun ordine selezionato. Ordini non confermati.');

        $payment_method = PaymentMethod::find($request->payment_method);

        $payment = Payment::create([
            'user_id' => $orders[0]->customer_id,
            'amount' => $orders->sum('total_amount'),
            'payment_date' => \Carbon\Carbon::now()->setTimezone('Europe/Rome')->format('Y-m-d'),
            'notes' => "Pagamento effettuato tramite {$payment_method->name} per ordini: " . implode(', ', $orders->pluck('id')->toArray()),
            'status' => 1,
            'payment_method_id' => $request->payment_method,
            'stripe_session_id' => $request->session_id ?? null,
            'transaction_id' => $request->payment_method == 2 ? $this->set_paypal_payment($request) : null,
        ]);

        foreach ($orders as $order) {
            $order->update([
                'status' => 1,
                'to_be_paid' => 0,
                'payment_method' => $request->payment_method
            ]);

            $order->payments()->attach($payment->id, ['amount' => $order->total_amount - $order->to_be_paid]);
        }

        return redirect()->route('orders.index')->with('message', 'Pagamenti registrati con successo. Grazie!');
    }

    public function payment_not_completed() {
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
