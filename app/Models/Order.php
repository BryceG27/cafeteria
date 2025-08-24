<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'total_amount',
        'order_date',
        'notes',
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
