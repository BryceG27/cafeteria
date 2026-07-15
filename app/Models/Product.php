<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
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
        return $this->belongsToMany(Menu::class, 'menu_product')->withPivot('quantity');
    }

    public static function get_beverage() : Builder {
        return SELF::where('product_type_id', 5)->where('is_active', true);
    }

    public function orders() {
        switch($this->product_type_id) {
            case 2:
                return $this->belongsTo(Order::class, 'first_dish');

            case 3:
                return $this->belongsTo(Order::class, 'second_dish');

            case 4:
                return $this->belongsTo(Order::class, 'side_dish');
        }
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|min:0|numeric',
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'Il campo nome è obbligatorio.',
            'category_id.required' => 'E\' necessario inserire una categoria.',
            'product_type_id.required' => 'E\' necessario inserire una tipologi.a',
        ]);
    }
}
