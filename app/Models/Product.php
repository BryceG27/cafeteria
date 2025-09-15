<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'is_active',
        'category_id',
        'product_type_id'
    ];

    public function category() : belongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function type() : belongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function menus() : belongsToMany {
        dd($this);
        return $this->belongsToMany(Menu::class, 'menu_product')->withPivot('quantity');
    }

    public function orders() {
        switch($this->product_type_id) {
            case 2:
                return $this->belongsTo(Order::class, 'first_dish');
                break;

            case 3:
                return $this->belongsTo(Order::class, 'second_dish');
                break;

            case 4:
                return $this->belongsTo(Order::class, 'side_dish');
                break;
        }
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'required|boolean',
        ]);
    }
}
