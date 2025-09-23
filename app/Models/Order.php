<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'to_be_paid',
        'payment_method',
        'order_date',
        'first_dish_id',
        'second_dish_id',
        'side_dish_id',
    ];

    private static $STATUSES = [
        [
            'value' => 0,
            'label' => 'In attesa',
            'color' => 'secondary',
        ],
        [
            'value' => 1,
            'label' => 'Pagato',
            'color' => 'info',
        ],
        [
            'value' => 2,
            'label' => 'Annullato',
            'color' => 'danger',
        ],
        [
            'value' => 3,
            'label' => 'Completato',
            'color' => 'success',
        ],

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
            'first_dish_id' => 'nullable|required_if:second_dish_id,null|max:255|exists:products,id',
            'second_dish_id' => 'nullable|required_if:first_dish_id,null|max:255|exists:products,id',
            'side_dish_id' => 'nullable|max:255|exists:products,id',
        ], [
            'first_dish_id.required_if' => 'Il campo primo piatto è obbligatorio se non è stato selezionato un secondo piatto.',
            'second_dish_id.required_if' => 'Il campo secondo piatto è obbligatorio se non è stato selezionato un primo piatto.',
            'customer_id.required' => 'Il campo cliente è obbligatorio.',
            'customer_id.exists' => 'Il cliente selezionato non esiste.',
            'menu_id.required' => 'Il campo menù è obbligatorio.',
            'menu_id.exists' => 'Il menù selezionato non esiste.',
            'notes.string' => 'Il campo note deve essere una stringa.',
            'notes.max' => 'Il campo note non deve superare i :max caratteri.',
            'status.integer' => 'Il campo stato deve essere un numero intero.',
            'subtotal_amount.numeric' => 'Il campo importo totale deve essere un numero.',
            'subtotal_amount.min' => 'Il campo importo totale deve essere almeno :min.',
            'discount.numeric' => 'Il campo sconto deve essere un numero.',
            'discount.min' => 'Il campo sconto deve essere almeno :min.',
            'total_amount.numeric' => 'Il campo importo da pagare deve essere un numero.',
            'total_amount.min' => 'Il campo importo da pagare deve essere almeno :min.',
            'payment_method.string' => 'Il campo metodo di pagamento deve essere una stringa.',
            'payment_method.max' => 'Il campo metodo di pagamento non deve superare i :max caratteri.',
            'order_date.required' => 'Il campo data ordine è obbligatorio.',
            'order_date.date' => 'Il campo data ordine non è una data valida.',
            'first_dish_id.exists' => 'Il primo piatto selezionato non esiste.',
            'first_dish_id.max' => 'Il campo primo piatto non deve superare i :max caratteri.',
            'second_dish_id.exists' => 'Il secondo piatto selezionato non esiste.',
            'second_dish_id.max' => 'Il campo secondo piatto non deve superare i :max caratteri.',
            'side_dish_id.exists' => 'Il contorno selezionato non esiste.',
            'side_dish_id.max' => 'Il campo contorno non deve superare i :max caratteri.',
        ]);
    }
    
    public function first_dish() {
        return $this->belongsTo(Product::class, 'first_dish_id');
    }

    public function second_dish() {
        return $this->belongsTo(Product::class, 'second_dish_id');
    }

    public function side_dish() {
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

    public function payments() : BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'order_payment')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    public function menu() : BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
        
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
