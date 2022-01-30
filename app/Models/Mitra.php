<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory;
    protected $table = 'mitra';
    protected $fillable = ['id', 'user_id', 'city', 'created_at', 'updated_at'];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
