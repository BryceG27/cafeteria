<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'child' => 'required|string|max:255',
            'has_allergies' => 'nullable|boolean',
            'child_allergies' => 'nullable|required_if:has_allergies,true|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'child_allergies.required_if' => 'Il campo allergie/intolleranze è obbligatorio',
            'email.lowercase' => 'L\'email deve essere in minuscolo.',
        ]);

        dd($validated);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        $validated['user_group_id'] = 3;

        $user = User::create($validated);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('orders.index')->with('message', 'Registrazione avvenuta con successo! Modificare il profilo per aggiungere altre informazioni.');
    }
}
