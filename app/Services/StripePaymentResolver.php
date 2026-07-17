<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeObject;

class StripePaymentResolver
{
    public function getPaymentIntentIdFromSessionId(?string $sessionId): ?string
    {
        $session = $this->getSessionFromId($sessionId);

        return $this->getPaymentIntentIdFromSession($session);
    }

    public function getSessionFromId(?string $sessionId): ?Session
    {
        if (!$sessionId) {
            return null;
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        return Session::retrieve($sessionId);
    }

    public function getPaymentIntentIdFromSession(StripeObject|array|null $session): ?string
    {
        if (!$session) {
            return null;
        }

        if (is_array($session)) {
            return isset($session['payment_intent']) && is_string($session['payment_intent'])
                ? $session['payment_intent']
                : null;
        }

        return is_string($session->payment_intent) ? $session->payment_intent : null;
    }
}
