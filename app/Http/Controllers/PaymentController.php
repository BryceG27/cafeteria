<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Stripe\Stripe;
use Inertia\Inertia;
use App\Models\Order;
use PayPal\Api\Payer;
use App\Models\Credit;
use PayPal\Api\Amount;
use App\Models\Payment;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use App\Services\StripePaymentRegistrar;
use Illuminate\Http\Request;
use PayPal\Api\RedirectUrls;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment as PayPalPayment;
use Stripe\Webhook;
use UnexpectedValueException;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::where('user_id', auth()->id())->with(['orders', 'method'])->get()->map(function($payment) {
                $payment->status_info = $payment->get_status();
                return $payment;
            }),
            'credits' => Credit::where('user_id', auth()->id())->get(),
            'orders' => Order::where('customer_id', auth()->id())->where('status', '!=', 2)->get()->map(function($order) {
                $order->status_info = $order->get_status();
                return $order;
            }),
        ]);
    }

    public function store_by_admin(Request $request) {
        $validate = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        Credit::create([
            'amount_available' => $validate['amount'],
            'total' => $validate['amount'],
            'user_id' => $validate['user_id'],
            'description' => 'Credito aggiunto dall\'amministratore. ' . ($validate['notes'] ?? ''),
        ]);

        return redirect()->back()->with('message', 'Pagamento e credito aggiunti con successo.');
    }

    public function stripe_webhook(Request $request, StripePaymentRegistrar $stripePaymentRegistrar) {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        Log::info('Stripe Webhook - Checkout Session in process', [
            'order_ids' => $event?->data?->object?->metadata?->orders ?? [],
            'user_id' => $event?->data?->object?->metadata?->user_id ?? null,
            'amount' => $event?->data?->object?->amount_total ?? null,
        ]);

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $payment = $stripePaymentRegistrar->registerFromSession($event->data->object);
            // Log::info('Stripe Webhook - Checkout Session processed', [
            //     'event_id' => $event->id,
            //     'session_id' => $event->data->object->id ?? null,
            //     'payment_id' => $payment?->id,
            // ]);
        }

        return response('Webhook handled', 200);
    }

    public function checkout(Order $order, Request $request) {
        switch ($request->payment_method) {
            case 1:
                return $this->checkout_with_credit($order);
                break;

            case 2:
                return $this->checkout_with_paypal($order);
                break;

            case 3:
                return $this->checkout_with_stripe($order, $request->boolean('receipt_requested'));
                break;

            default:
                return redirect()->back()->withErrors('Metodo di pagamento non valido.');
                break;
        }
    }

    public function checkout_multiple(Request $request) {
        $orders = Order::whereIn('id', $request->orders)->where('customer_id', auth()->id())->where('status', '!=', 2)->get();

        switch ($request->payment_method) {
            case 1:
                return $this->checkout_with_credit($orders);
                break;

            case 2:
                return $this->checkout_with_paypal($orders);
                break;

            case 3:
                return $this->checkout_with_stripe($orders, $request->boolean('receipt_requested'));
                break;

            default:
                return redirect()->back()->withErrors('Metodo di pagamento non valido.');
                break;
        }
    }

    public function checkout_with_stripe($orders, bool $receiptRequested = false) {
        $orders = $orders instanceof Order ? collect([$orders]) : $orders;
        $orders->load([
            'customer',
            'first_dish',
            'second_dish',
            'side_dish',
            'beverage',
            'special_menu.product',
        ]);

        $customer = $orders[0]->customer;
        $payment_name = '';

        if(count($orders) == 1) {
            if($orders[0]->menu)
                $payment_name = "Ordine #{$orders[0]->id} - menù: " . $orders[0]->menu->name . " " . Carbon::create($orders[0]->menu->validity_date)->setTimezone('Europe/Rome')->format('d/m/Y');
            else if($orders[0]->special_menu)
                $payment_name = "Ordine #{$orders[0]->id} - menù special: " . $orders[0]->special_menu->name . " " . Carbon::create($orders[0]->order_date)->setTimezone('Europe/Rome')->format('d/m/Y');

            $to_be_paid = $orders[0]->to_be_paid * 100;
            $success_url = route('orders.confirm-order', ['order' => $orders[0]->id]) . '?payment_method=3&session_id={CHECKOUT_SESSION_ID}';
            $ids = $orders[0]->id;
        } else {
            $payment_name = "Ordini Multipli | #" . implode(' #', $orders->pluck('id')->toArray());
            $to_be_paid = 0;
            $ids = [];
            $orders->map(function($order) use (&$payment_name, &$to_be_paid, &$ids) {
                if($order->menu) 
                    $payment_name .= " - " . $order->menu->name . " " . Carbon::create($order->menu->validity_date)->setTimezone('Europe/Rome')->format('d/m/Y');
                else if($order->special_menu)
                    $payment_name .= " - " . $order->special_menu->name . " " . Carbon::create($order->special_menu->order_date)->setTimezone('Europe/Rome')->format('d/m/Y');

                $to_be_paid += $order->to_be_paid * 100;
                $ids[] = $order->id;
            });
            
            $ids = implode('-', $ids);
            $success_url = route('orders.confirm-orders') . "?orders=$ids&payment_method=3&session_id={CHECKOUT_SESSION_ID}";
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $metadata = [
            'orders' => (string) $ids,
            'user_id' => (string) auth()->id(),
            'receipt_requested' => $receiptRequested ? '1' : '0',
            'customer_name' => (string) ($customer?->name ?? ''),
            'customer_surname' => (string) ($customer?->surname ?? ''),
            'customer_email' => (string) ($customer?->email ?? ''),
            'customer_child' => (string) ($customer?->child ?? ''),
        ];

        // Stripe limits metadata values to 500 characters and keys to 40 characters.
        // Keeping one key per order makes the product summary immediately readable
        // in the Stripe dashboard and preserves the relationship with the order ID.
        foreach ($orders as $order) {
            $products = collect([
                'Primo' => $order->first_dish,
                'Secondo' => $order->second_dish,
                'Contorno' => $order->side_dish,
                'Bevanda' => $order->beverage,
            ]);

            if ($order->special_menu?->product) {
                $products->prepend($order->special_menu->product, 'Prodotto menù special');
            }

            $productSummary = $products
                ->filter()
                ->map(fn ($product, $type) => "{$type}: {$product->name} (#{$product->id})")
                ->implode(' | ');

            $metadataKey = "order_{$order->id}_products";
            $metadata[$metadataKey] = mb_substr($productSummary, 0, 500);
        }

        $lineItems = $orders
            ->flatMap(fn (Order $order) => $this->getStripeLineItems($order))
            ->values()
            ->all();

        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'payment_intent_data' => [
                'description' => $payment_name,
                'metadata' => $metadata,
            ],
            'invoice_creation' => [
                'enabled' => true
            ],
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url' => route('orders.payment-not-completed'),
            'metadata' => $metadata,
        ];

        if ($customer?->email) {
            $sessionData['customer_email'] = $customer->email;
        }

        if ($receiptRequested && $customer?->email) {
            $sessionData['payment_intent_data']['receipt_email'] = $customer->email;
        }

        $session = Session::create($sessionData);

        return response()->json([
            'id' => $session->id
        ]);
    }

    private function getStripeLineItems(Order $order): array
    {
        $orderPrefix = "Ordine #{$order->id}";
        $items = [];

        if ($order->special_menu) {
            $productNames = collect([
                $order->special_menu->product?->name,
                $order->beverage?->name,
            ])->filter()->implode(' + ');

            $items[] = [
                'name' => "$orderPrefix - {$order->special_menu->name}" . ($productNames ? ": {$productNames}" : ''),
                'amount' => $this->stripeAmount($order->special_menu->price),
            ];
        } elseif ($order->menu) {
            $baseProductNames = collect([
                $order->first_dish?->name,
                $order->side_dish?->name,
            ])->filter()->implode(' + ');

            if ($order->first_dish && $order->second_dish) {
                $items[] = [
                    'name' => "$orderPrefix - {$baseProductNames}",
                    'amount' => $this->stripeAmount($order->menu->price),
                ];
                $items[] = [
                    'name' => "$orderPrefix - {$order->second_dish->name}",
                    'amount' => $this->stripeAmount($order->menu->second_price),
                ];
            } else {
                $baseProductNames = collect([
                    $order->first_dish?->name,
                    $order->second_dish?->name,
                    $order->side_dish?->name,
                ])->filter()->implode(' + ');

                $items[] = [
                    'name' => "$orderPrefix - {$baseProductNames}",
                    'amount' => $this->stripeAmount($order->menu->price),
                ];
            }

            if ($order->beverage) {
                $items[] = [
                    'name' => "$orderPrefix - {$order->beverage->name}",
                    'amount' => $this->stripeAmount($order->beverage->price),
                ];
            }
        }

        $total = $this->stripeAmount($order->to_be_paid);
        $items = collect($items)->filter(fn ($item) => $item['amount'] > 0)->values();

        if ($items->isEmpty()) {
            $items->push([
                'name' => $orderPrefix,
                'amount' => $total,
            ]);
        } else {
            // Apply any discount or rounding difference to the first line item.
            $firstItem = $items->first();
            $firstItem['amount'] += $total - $items->sum('amount');
            $items->put(0, $firstItem);

            // Never send a zero/negative Stripe line item if the order discount
            // is greater than the first product amount.
            if ($items[0]['amount'] <= 0) {
                $items = collect([[
                    'name' => $orderPrefix,
                    'amount' => $total,
                ]]);
            }
        }

        return $items->map(fn ($item) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['name'],
                ],
                'unit_amount' => $item['amount'],
            ],
            'quantity' => 1,
        ])->all();
    }

    private function stripeAmount($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function checkout_with_credit($orders) {
        $credit_available = Credit::where('user_id', auth()->id())->where('amount_available', '>', 0)->get();

        $to_be_paid = $orders->sum('to_be_paid');
        $total = 0;

        foreach($orders as $order) {
            foreach ($credit_available as $credit) {
                if($credit->amount_available >= $order->to_be_paid) {
                    // The available credit is enough to cover the order total
                    $total += $order->to_be_paid;
                    $to_be_paid -= $order->to_be_paid;

                    // Deduct the used amount from the credit
                    $credit->amount_available -= $order->to_be_paid;
                    $credit->save();

                    // Update the order status to Paid
                    $order->to_be_paid = 0;
                    $order->status = 1;
                    $order->payment_method = 1;
                    $order->save();

                    continue 2;
                } else {
                    // Deduct the used amount from the credit (which will be zero now)
                    $total += $credit->amount_available;
                    $to_be_paid -= $credit->amount_available;
                    $order->to_be_paid = $order->to_be_paid - $credit->amount_available;
                    $credit->amount_available = 0;
                    $credit->save();
                    $order->save();
                }
            }
        }

        $notes = $to_be_paid > 0 ? "Pagamento parziale effettuato con credito residuo." : "Pagamento effettuato con credito residuo.";

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'amount' => $total,
            'payment_date' => Carbon::now()->format('Y-m-d'),
            'notes' => $notes,
            'status' => 1,
            'payment_method_id' => 1,
        ]);

        $orders->each(function($order) use ($payment) {
            $order->payments()->attach($payment->id, ['amount' => $order->total_amount - $order->to_be_paid]);
        });

        $ids = [];

         $orders->where('to_be_paid', '>', 0)->each(function($order) use (&$ids) {
            $ids[] = $order->id;
        });

        if($to_be_paid > 0) {
            $to_be_paid = number_format($to_be_paid, 2, ',', '.');
            return redirect()->route('orders.index', ['orders' => $ids])->withErrors("Scarico da credito effettuato. Restante da pagare: {$to_be_paid} €.");
        }

        return redirect()->route('orders.index')->with('message', 'Pagamento effettuato con successo utilizzando il credito residuo.');
    }

    public function checkout_with_paypal(Order $order) {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),
                env('PAYPAL_CLIENT_SECRET')
            )
        );

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(number_format($order->to_be_paid, 2, '.', ''));
        $amount->setCurrency('EUR');

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setDescription("Pagamento menù: ". $order->menu->name . " " . Carbon::create($order->menu->validity_date)->setTimezone('Europe/Rome')->format('d/m/Y'));

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('orders.confirm-order', ['order' => $order->id]) . '?payment_method=2')
                     ->setCancelUrl(route('orders.payment-not-completed', ['order' => $order->id]));

        $payment = new PayPalPayment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);


        try {
            $payment->create(
                $apiContext
            );

            return redirect($payment->getApprovalLink());
            
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors('Errore durante la creazione del pagamento PayPal: ' . $ex->getMessage());
        }
    }

    public function to_check() {
        // SELECT
        //     p.id,
        //     p.amount,
        //     p.user_id,
        //     op.order_id,
        //     op.payment_id,
        //     op.amount
        // FROM
        //     payments p
        //     LEFT JOIN order_payment op ON op.payment_id = p.id
        // WHERE
        //     p.id <= 7341
        //     AND op.id IS NOT NULL
        // ORDER BY
        //     p.id DESC

        $payments = Payment::whereHas('orders')->with('orders', 'customer')/* ->where('id', '<=', 6742) */->orderBy('id', 'desc')->paginate(300);

        return Inertia::render('Payments/ToCheck', [
            'payments' => $payments
        ]);
    }

    public function to_delete(Request $request) {
        Payment::whereIn('id', $request->payments)->delete();

        return back()->with('success', "Pagamenti cancellati");
    }
}
