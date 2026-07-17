<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stripe\StripeObject;

class StripePaymentRegistrar
{
    public function __construct(private StripePaymentResolver $stripePaymentResolver)
    {
    }

    public function registerFromSessionId(?string $sessionId): ?Payment
    {
        $session = $this->stripePaymentResolver->getSessionFromId($sessionId);

        return $this->registerFromSession($session);
    }

    public function registerFromSession(StripeObject|array|null $session): ?Payment
    {
        if (!$session || !$this->getSessionValue($session, 'id')) {
            return null;
        }

        if ($this->getSessionValue($session, 'payment_status') !== 'paid') {
            return null;
        }

        $orders = $this->getOrdersFromSession($session);

        if ($orders->isEmpty()) {
            return null;
        }

        $sessionId = $this->getSessionValue($session, 'id');

        return DB::transaction(function () use ($session, $sessionId, $orders) {
            $paymentData = [
                'user_id' => $orders->first()->customer_id,
                'amount' => $this->getAmountFromSession($session) ?? $orders->sum('to_be_paid'),
                'payment_date' => Carbon::now()->setTimezone('Europe/Rome')->format('Y-m-d'),
                'notes' => $orders->count() === 1
                    ? "Pagamento effettuato tramite Stripe. Ordine: {$orders->first()->id}"
                    : 'Pagamento effettuato tramite Stripe per ordini: ' . $orders->pluck('id')->implode(', '),
                'status' => 1,
                'payment_method_id' => 3,
                'stripe_payment_id' => $this->stripePaymentResolver->getPaymentIntentIdFromSession($session),
            ];

            if (Schema::hasColumn('payments', 'receipt_requested')) {
                $paymentData['receipt_requested'] = $this->receiptWasRequested($session);
            }

            $payment = Payment::updateOrCreate(
                ['stripe_session_id' => $sessionId],
                $paymentData
            );

            $orders->each(function (Order $order) use ($payment) {
                $paidAmount = $order->to_be_paid;
                $isAttached = $order->payments()->where('payments.id', $payment->id)->exists();

                $order->update([
                    'status' => 1,
                    'to_be_paid' => 0,
                    'payment_method' => 3,
                ]);

                if (!$isAttached) {
                    $order->payments()->attach($payment->id, ['amount' => $paidAmount]);
                }
            });

            return $payment;
        });
    }

    private function getOrdersFromSession(StripeObject|array $session): Collection
    {
        $metadata = $this->getSessionValue($session, 'metadata');
        $orderIds = $metadata['orders'] ?? $metadata?->orders ?? null;

        if (!$orderIds) {
            return collect();
        }

        return Order::whereIn('id', explode('-', (string) $orderIds))->get();
    }

    private function getAmountFromSession(StripeObject|array $session): ?float
    {
        $amountTotal = $this->getSessionValue($session, 'amount_total');

        return is_numeric($amountTotal) ? $amountTotal / 100 : null;
    }

    private function receiptWasRequested(StripeObject|array $session): bool
    {
        $metadata = $this->getSessionValue($session, 'metadata');

        return ($metadata['receipt_requested'] ?? $metadata?->receipt_requested ?? null) === '1';
    }

    private function getSessionValue(StripeObject|array $session, string $key): mixed
    {
        return is_array($session) ? ($session[$key] ?? null) : ($session->{$key} ?? null);
    }
}
