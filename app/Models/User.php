<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'child',
        'child_allergies',
        'email',
        'password',
        'user_group_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function validate(Request $request) {
        return $request->validate([
            'name'      => 'required|string|max:100',
            'surname'   => 'required|string|max:100',
            'child'     => 'nullable|string|max:100',
            'child_allergies' => 'nullable|string|max:255',
            'email'     => ['required','email', Rule::unique('users')->ignore($request->id)],
            'is_active' => ['boolean', Rule::requiredIf(function() use ($request) {
                return Auth::user()->user_group_id != 3;
            })],
            'user_group_id' => ['exists:user_groups,id', Rule::requiredIf(function() use ($request) {
                return Auth::user()->user_group_id != 3;
            })],
            'password' => 'nullable|required_if:id,!=,null|string|min:8|confirmed',
        ],[
            'password.required_if' => 'La password è richiesta durante la creazione di un nuovo utente.',
        ]);
    }

    public function user_group() {
        return $this->belongsTo(UserGroup::class);
    }
}
