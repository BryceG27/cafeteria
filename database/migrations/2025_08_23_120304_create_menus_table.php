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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->date('start_date')->default(now());
            $table->date('end_date')->default(now()->addDays(7));
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 8, 2)->default(0.00);
            $table->decimal('second_menu_price', 8, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
