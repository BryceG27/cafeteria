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
        if (Schema::hasColumn('payments', 'stripe_payment_id')) {
            return;
        }

        if (Schema::hasColumn('payments', 'notes')) {
            return;
        }

        if (Schema::hasColumn('payments', 'payment_date')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_payment_id')->nullable()->after('stripe_session_id');
            $table->string('notes')->nullable()->after('order_id');
            $table->date('payment_date')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('payments', 'stripe_payment_id')) {
            return;
        }

        if (!Schema::hasColumn('payments', 'notes')) {
            return;
        }

        if (!Schema::hasColumn('payments', 'payment_date')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('stripe_payment_id');
            $table->dropColumn('notes');
            $table->dropColumn('payment_date');
        });
    }
};
