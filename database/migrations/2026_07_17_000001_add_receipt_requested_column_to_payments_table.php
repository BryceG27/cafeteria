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
        if (Schema::hasColumn('payments', 'receipt_requested')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('receipt_requested')->default(false)->after('stripe_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('payments', 'receipt_requested')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('receipt_requested');
        });
    }
};
