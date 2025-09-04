<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Stripe\Stripe;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Credit;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::where('user_id', auth()->id())->with('order', 'method')->get()->map(function($payment) {
                $payment->status_info = $payment->get_status();
                return $payment;
            }),
            'credits' => Credit::where('user_id', auth()->id())->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function store_by_admin(Request $request) {
        $validate = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validate['status'] = 1; // Completed
        $validate['order_id'] = null;

        Payment::create($validate);

        Credit::create([
            'amount_available' => $validate['amount'],
            'total' => $validate['amount'],
            'user_id' => $validate['user_id'],
            'description' => 'Credito aggiunto dall\'amministratore. ' . ($validate['notes'] ?? ''),
        ]);

        return redirect()->back()->with('message', 'Pagamento e credito aggiunti con successo.');
    }

    public function checkout(Order $order, Request $request) {
        switch ($request->payment_method) {
            case 1:
                $this->checkout_with_credit($order);
                break;

            case 2:
                
                break;

            case 3:
                $this->checkout_with_stripe($order);
                break;

            default:
                return redirect()->back()->withErrors('Metodo di pagamento non valido.');
                break;
        }
    }

    public function checkout_with_stripe(Order $order) {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $order->menu->name . " " . Carbon::create($order->menu->validity_date)->format('d/m/Y')
                    ],
                    'unit_amount' => $order->to_be_paid * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('orders.confirm-order', ['order' => $order->id]) . '?payment_method=3&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('orders.payment-not-completed', ['order' => $order->id]),
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
            ]
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function checkout_with_credit(Order $order) {
        $credit_available = Credit::where('user_id', auth()->id())->where('amount_available', '>', 0)->get();
        $total = 0;

        foreach ($credit_available as $credit) {
            if($credit->amount_available >= $order->to_be_paid) {
                // The available credit is enough to cover the order total
                $total += $order->to_be_paid;

                // Deduct the used amount from the credit
                $credit->amount_available -= $order->to_be_paid;
                $credit->save();

                // Update the order status to Paid
                $order->status = 1;
                $order->save();

                break;
            } else {
                // The available credit is not enough, use it all and continue to the next credit
                $total += $credit->amount_available;
                
                // Deduct the used amount from the credit (which will be zero now)
                $order->to_be_paid -= $credit->amount_available;
                $credit->amount_available = 0;
                $credit->save();
                $order->save();
            }
        }

        Payment::create([
            'user_id' => auth()->id(),
            'amount' => $total,
            'payment_date' => Carbon::now()->format('Y-m-d'),
            'notes' => 'Pagamento parziale effettuato con credito residuo. Credito #' . $credit->id,
            'order_id' => $order->id,
            'status' => 1,
            'payment_method_id' => 1,
        ]);


        if($total < $order->to_be_paid)
            $to_be_paid = number_format($order->to_be_paid, 2, ',', '.');
            return redirect()->back()->withErrors("Scarico da credito effettuato. Restante da pagare: {$to_be_paid} €.");

        return redirect()->route('orders.index')->with('message', 'Pagamento effettuato con successo utilizzando il credito residuo.');
    }
}
