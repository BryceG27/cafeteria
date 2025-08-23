<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductCategoryController;

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/sign-in', [ProfileController::class, 'sign_in'])->name('profile.sign-in');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Users */
    Route::resource('users', UserController::class)->middleware(['auth', 'verified']);
    Route::put('/users/{user}/toggle-active', [UserController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('users.toggle-active');

    /* Customers */
    Route::resource('customers', CustomerController::class)->middleware(['auth', 'verified']);

    /* Products */
    Route::resource('products', ProductController::class)->middleware(['auth', 'verified'])->except(['update']);
    Route::post('/products/{product}', [ProductController::class, 'update'])->middleware(['auth', 'verified'])->name('products.update');
    Route::put('/products/{product}/toggle-active', [ProductController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('products.toggle-active');

    /* Product Categories */
    Route::resource('categories', CategoryController::class)->middleware(['auth', 'verified']);
    Route::put('/categories/{category}/toggle-active', [CategoryController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('categories.toggle-active');
});

require __DIR__.'/auth.php';
