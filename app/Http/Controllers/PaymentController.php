<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Credit;
use App\Models\Payment;
use Illuminate\Http\Request;

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
            })
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
}
