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
                // 'orders' => Order::where('customer_id', Auth::user()->id)->orderBy('created_at', 'desc')->with(['products'])->paginate(10),
                'orders' => [],
            ]);
        } else {
            return Inertia::render('Orders/Index', [
                // 'orders' => Order::with(['customer', 'products'])->orderBy('created_at', 'desc')->paginate(10),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Orders/Create', [
            'menus' => Menu::where('is_active', true)->whereDate('start_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))->whereDate('end_date', '<=', \Carbon\Carbon::now()->format('Y-m-d'))->with('products')->get(),
            'order' => new Order()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
