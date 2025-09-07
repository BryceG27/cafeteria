<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function destroy(Credit $credit) {
        if($credit->amount_available < $credit->total) {
            $credit->update([
                'amount_available' => 0,
            ]);

            $message = 'Attenzione: il credito era stato parzialmente utilizzato, quindi l\'importo disponibile è stato azzerato ma il credito non è stato eliminato.';
        } else {
            $credit->delete();
            $message = 'Credito eliminato con successo.';
        }

        return redirect()->back()->with('message', $message);
    }

    public function update(Credit $credit, Request $request) {
        $data = $request->validate([
            'amount_available' => 'required|numeric|min:0',
        ]);

        $credit->update($data);

        return redirect()->back()->with('message', 'Credito aggiornato con successo.');
        
    }
}
