<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);
    }
}
