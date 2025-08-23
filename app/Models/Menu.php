<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'start_date',
        'end_date',
        'is_active',
        'price',
    ];

    public function products() : BelongsToMany {
        return $this->belongsToMany(Product::class, 'menu_product')->withPivot('quantity');
    }
}
