<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;


Route::get('/update', function() {
    Artisan::call('migrate');

    return 'Updated';
});

Route::get('/migrate-fresh', function() {
    Artisan::call('migrate:fresh', ['--seed' => true]);

    return 'Migrated Fresh with Seed';
});

Route::get('/seed', function() {
    Artisan::call('db:seed');

    return 'Seeded';
});

Route::get('/storage-link', function() {
    Artisan::call('storage:link');

    return 'Storage link created';
});

Route::post('/sign-in', [ProfileController::class, 'sign_in'])->name('profile.sign-in');

Route::middleware(['auth' , 'verified'])->group(function () {
    /* Orders */
    Route::resource('orders', OrderController::class)->middleware(['auth', 'verified'])->except(['index', 'show']);
    Route::get('/orders/index', [OrderController::class, 'index'])->middleware(['auth', 'verified'])->name('orders.index');
    Route::get('/orders/{order}/confirm', [OrderController::class, 'confirm'])->middleware(['auth', 'verified'])->name('orders.confirm-order');
    Route::get('/orders/confirm/multiples', [OrderController::class, 'confirm_multiples'])->middleware(['auth', 'verified'])->name('orders.confirm-orders');
    Route::get('/orders/payments/payment-not-completed', [OrderController::class, 'payment_not_completed'])->middleware(['auth', 'verified'])->name('orders.payment-not-completed');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('payments', PaymentController::class)->middleware(['auth', 'verified'])->except(['show', 'edit', 'update', 'destroy']);
    Route::post('/payments/store/by-admin', [PaymentController::class, 'store_by_admin'])->middleware(['auth', 'verified'])->name('payments.store-by-admin');

    Route::patch('/credits/{credit}/update', [CreditController::class, 'update'])->middleware(['auth', 'verified'])->name('credits.update');
    Route::delete('/credits/{credit}/destroy', [CreditController::class, 'destroy'])->middleware(['auth', 'verified'])->name('credits.destroy');

    Route::post('/payments/{order}/checkout', [PaymentController::class, 'checkout'])->middleware(['auth', 'verified'])->name('payments.checkout');
    Route::post('/payments/orders/checkout-multiple', [PaymentController::class, 'checkout_multiple'])->middleware(['auth', 'verified'])->name('payments.checkout-multiple');
    
    Route::get('/', function () {
        if(auth()->user()->user_group_id == 3)
            return redirect(route('orders.index'));
        else
            return redirect(route('dashboard'));
    })->name('home');
});


Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');
    
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Users */
    Route::resource('users', UserController::class)->middleware(['auth', 'verified']);
    Route::put('/users/{user}/toggle-active', [UserController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('users.toggle-active');

    /* Customers */
    Route::resource('customers', CustomerController::class)->middleware(['auth', 'verified']);

    /* Products */
    Route::resource('products', ProductController::class)->middleware(['auth', 'verified'])->except(['update']);
    Route::get('/products/filter/by-date', [ProductController::class, 'filter'])->name('products.filter-by-date');
    Route::post('/products/{product}', [ProductController::class, 'update'])->middleware(['auth', 'verified'])->name('products.update');
    Route::put('/products/{product}/toggle-active', [ProductController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('products.toggle-active');

    /* Product Categories */
    Route::resource('categories', CategoryController::class)->middleware(['auth', 'verified']);
    Route::put('/categories/{category}/toggle-active', [CategoryController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('categories.toggle-active');

    /* Menus */
    Route::resource('menus', MenuController::class)->middleware(['auth', 'verified'])->except(['show']);
    Route::put('/menus/{menu}/toggle-active', [MenuController::class, 'toggle_active'])->middleware(['auth', 'verified'])->name('menus.toggle-active');
    Route::get('/menus/get/test', [MenuController::class, 'get_test'])->name('menus.get-test');

    Route::put('/orders/{order}/update-status', [OrderController::class, 'change_status'])->middleware(['auth', 'verified'])->name('orders.update-status');

});

require __DIR__.'/auth.php';
