<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
    public function dashboard() {
        $orders = Order::where('status', '<>', 2)->with(['customer', 'first_dish', 'second_dish', 'side_dish', 'menu'])->orderBy('created_at', 'desc')->get()->map(function($order) {
                    $order->status_info = $order->get_status();
                    $order->child_name = $order->customer->child . " ";

                    $order->child_name .= count(explode(' ', $order->customer->child)) == 1 ? $order->customer->surname : '';
                    return $order;
                });

        $customers = User::where('user_group_id', 3)->where('is_active', true)->with('user_group', 'orders', 'payments')->get();
        return Inertia::render('Dashboard', compact('orders', 'customers'));
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
