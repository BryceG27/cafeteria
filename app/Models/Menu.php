<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'validity_date',
        'is_active',
        'price',
        'second_price'
    ];

    public function products() : BelongsToMany {
        return $this->belongsToMany(Product::class, 'menu_product')->withPivot('quantity');
    }

    public function orders() : HasMany {
        return $this->HasMany(Order::class);
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'validity_date' => 'nullable|date|after_or_equal:start_date,before_or_equal:validity_date',
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'second_price' => 'nullable|numeric|min:0',
        ]);
    }

    public function validate_products(Request $request) {
        $request->validate([
            'products' => 'array',
            'products.*.id' => 'exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
    }
}
