<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialMenu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'img_url',
        'product_id',
        'price',
        'active'
    ];

    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
