<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['isDelete'];

    public function userRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'users_id', 'role_id')
            ->withTimestamps();
    }

    public function mitra(): HasOne
    {
        return $this->hasOne(Mitra::class, 'user_id');
    }

    public function getIsDeleteAttribute() {
        $user = auth()->user();
        if ($user) {
            $roles = RoleUser::where('users_id', $user->id)->first();
            if ($roles->role_id == 1) {
                return true;
            } else {
                return false;
            }
        }
    }
}
