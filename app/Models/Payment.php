<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    private static $STATUSES = [
        [
            'value' => 0,
            'label' => 'In attesa',
            'color' => 'secondary',
        ],
        [
            'value' => 1,
            'label' => 'Completato',
            'color' => 'info',
        ],
        [
            'value' => 2,
            'label' => 'Fallito',
            'color' => 'danger',
        ]
    ];

    protected $fillable = [
        'amount',
        'payment_method_id',
        'status',
        'user_id',
        'order_id',
        'credit_id',
    ];

    public function method() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function customer() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function get_status() {
        foreach (SELF::$STATUSES as $status) {
            if ($status['value'] == $this->status) {
                return $status;
            }
        }

        return null;
    }

    public static function get_statuses() {
        return SELF::$STATUSES;
    }
}
