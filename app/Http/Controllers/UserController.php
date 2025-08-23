<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('user_group_id', '>=', Auth::user()->user_group_id)->where('user_group_id', '<>', 3)->with('user_group')->get();
        $user_groups = UserGroup::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'user_groups' => $user_groups
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = new User();

        return Inertia::render('Users/Create', [
            'user' => $user,
            'user_groups' => UserGroup::where('id', '>', 1)->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = User::validate($request);
        $validate['password'] = Hash::make($validate['password']);

        User::create($validate);

        return redirect()->route('users.index')->with('message', 'Utente creato con successo.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'user_groups' => UserGroup::where('id', '>', 1)->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validate = User::validate($request);
        if ($request->filled('password'))
            $validate['password'] = Hash::make($validate['password']);
        else
            $validate['password'] = $user->password; // Keep the old password if not provided

        $user->update($validate);

        return redirect()->route('users.index')->with('message', 'Utente aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('message', 'Utente eliminato con successo.');
    }

    public function toggle_active(User $user) {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        return redirect()->back()->with('success', 'Stato utente aggiornato con successo.');
    }
}
