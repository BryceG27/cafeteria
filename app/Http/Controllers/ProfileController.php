<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use Inertia\Response;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
    public function dashboard() {
        $ordered_food = [];

        $orders = Order::where('status', '<>', 2)->whereBetween('order_date', [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->endOfMonth()->format('Y-m-d')])->with(['customer', 'first_dish', 'second_dish', 'side_dish', 'menu'])->orderBy('created_at', 'desc')->get()->map(function($order) use (&$ordered_food) {
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
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validate = User::validate($request);

        if ($request->user()->isDirty('email'))
            $request->user()->email_verified_at = null;

        $request->user()->update($validate);

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Register a new account
     */
    public function sign_in(Request $request) {
        dd($request->all());

        $data = User::validate($request);
    }
}
