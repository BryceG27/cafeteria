<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class SpecialMenu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'product_id',
        'price',
        'active'
    ];

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0|decimal:2',
            'active' => 'boolean'
        ], [
            'name.required' => 'Il campo nome è obbligatorio',
            'product_id.required' => 'E\' necessario inserire un prodotto da associare',
            'product_id.exists' => 'Il prodotto selezionato è inesistente.',
            'price.required' => 'E\' necessario inserire un prezzo.'
        ]);
    }
}
