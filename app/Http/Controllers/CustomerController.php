<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Customers/Index', [
            'customers' => User::get_customers()->orderBy('surname')->with('user_group', 'orders', 'payments')->get()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $customer)
    {
        if($customer->user_group_id != 3)
            return redirect()->back()->withErrors('Utente non valido');

        $customer->orders = $customer->orders()->orderBy('created_at', 'desc')->get()->map(function($order) {
            $order->status_info = $order->get_status();
            $order->load(['menu', 'special_menu.product', 'first_dish', 'second_dish', 'side_dish', 'beverage']);

            return $order;
        });

        $customer->payments = $customer->payments()->with(['method', 'orders'])->orderBy('created_at', 'desc')->get()->map(function($payment) {
            $payment->status_info = $payment->get_status();
            return $payment;
        });

        return Inertia::render('Customers/Show', [
            'customer' => $customer->load('user_group',  'credits'),
            'order_statuses' => Order::get_statuses(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('message', 'Cliente eliminato con successo');
    }

    public function load_credit(User $customer) {
        
    }
}
