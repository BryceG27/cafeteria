<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Inertia\Inertia;
use App\Models\Order;
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
                'orders' => Order::where('customer_id', Auth::user()->id)->where('status', '<>', 2)->orderBy('created_at', 'desc')->with(['first_dish', 'second_dish', 'side_dish'])->get(),
            ]);
        } else {
            return Inertia::render('Orders/Index', [
                'orders' => Order::with(['customer', 'first_dish', 'second_dish', 'side_dish'])->orderBy('created_at', 'desc')->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    return $order;
                }),
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
            'statuses' => Order::get_statuses(),
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
        $order->update(['status' => 2]);

        return redirect()->route('orders.index')->with('success', 'Ordine eliminato con successo.');
    }
}
