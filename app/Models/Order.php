<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'menu_id',
        'notes',
        'status',
        'subtotal_amount',
        'discount',
        'total_amount',
        'payment_method',
        'order_date',
        'first_dish',
        'second_dish',
        'side_dish',
    ];

    private $STATUSES = [
        [
            'value' => 0,
            'label' => 'In attesa',
            'color' => 'alt-secondary',
        ],
        [
            'value' => 1,
            'label' => 'Pagato',
            'color' => 'alt-success',
        ],
        [
            'value' => 2,
            'label' => 'Annullato',
            'color' => 'alt-danger',
        ]
    ];

    public static function validate(Request $request) {
        return $request->validate([
            'customer_id' => 'required|exists:users,id',
            'menu_id' => 'required|exists:menus,id',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|integer',
            'subtotal_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'first_dish_id' => 'nullable|required_if:second_dish,null|string|max:255|exists:products,id',
            'second_dish_id' => 'nullable|required_if:first_dish,null|string|max:255|exists:products,id',
            'side_dish_id' => 'nullable|string|max:255|exists:products,id',
        ]);
    }
    
    public function first_dish() {
        return $this->belongsTo(Product::class, 'first_dish_id');
    }

    public function second_dish() {
        return $this->belongsTo(Product::class, 'second_dish_id');
    }

    public function sider_dish() {
        return $this->belongsTo(Product::class, 'side_dish_id');
    }

    public function customer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function products() : BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function get_status() {
        foreach (self::$STATUSES as $status) {
            if ($status['value'] == $this->status) {
                return $status;
            }
        }

        return null;
    }
}
