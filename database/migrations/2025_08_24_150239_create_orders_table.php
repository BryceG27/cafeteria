<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->string('notes')->nullable();
            $table->integer('status')->default(0);
            $table->decimal('subtotal_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->date('order_date')->nullable();
            $table->foreignId('first_dish_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('second_dish_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('side_dish_id')->nullable()->constrained('products')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
