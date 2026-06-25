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
        Schema::create('special_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment("Short Name for the menù");
            $table->string('description')->nullable()->comment("What is included in the menù");
            $table->foreignIdFor(\App\Models\Product::class)->constrained();
            $table->double('price')->default(0.0);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_menus');
    }
};
