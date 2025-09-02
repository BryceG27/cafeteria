<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Menu;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Credit;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->user_group_id == 3) {
            return Inertia::render('Orders/CustomerIndex', [
                'orders' => Order::where('customer_id', Auth::user()->id)->where('status', '<>', 2)->orderBy('created_at', 'desc')->with(['first_dish', 'second_dish', 'side_dish', 'menu'])->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    return $order;
                }),
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
        return Inertia::render('Orders/Create', [
            'menus' => Menu::where('is_active', true)->whereDate('start_date', '<=', \Carbon\Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))->with('products')->get(),
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

        if($request->first_dish_id && $request->second_dish_id) {
            $validated['subtotal_amount'] += $menu->second_price;
            $validated['total_amount'] += $menu->second_price;
        }

        $validated['order_date'] = \Carbon\Carbon::create($request->order_date)->format('Y-m-d');

        Order::create($validated);

        return redirect()->route('orders.index')->with('success', 'Ordine creato con successo.');
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
        return Inertia::render('Orders/Edit', [
            'menus' => Menu::where('is_active', true)->whereDate('start_date', '<=', \Carbon\Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))->with('products')->get(),
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

        $menu = Menu::with('products')->find($request->menu_id);
        $validated['total_amount'] = (Float)$order->subtotal_amount - ($request->discount ?? 0);

        $validated['order_date'] = \Carbon\Carbon::create($request->order_date)->format('Y-m-d');

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Ordine aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $message = 'Ordine eliminato con successo.';
        if($order->has('payments')->count() > 0) {
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
                'payment_date' => \Carbon\Carbon::now()->format('Y-m-d'),
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
        }

        $order->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('message', 'Stato ordine aggiornato con successo.');
    }
}
