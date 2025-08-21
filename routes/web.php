<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/sign-in', [ProfileController::class, 'sign_in'])->name('profile.sign-in');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class)->middleware(['auth', 'verified']);
    Route::put('/users/{user}/toggle-active', [UserController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('users.toggle-active');

    Route::resource('customers', CustomerController::class)->middleware(['auth', 'verified']);
});

require __DIR__.'/auth.php';
