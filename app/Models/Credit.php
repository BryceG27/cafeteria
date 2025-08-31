<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'amount_available',
        'description',
    ];

    public function customer() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
