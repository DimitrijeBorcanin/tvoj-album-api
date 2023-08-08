<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $fillable = ["price", "delivery", "expense"];

    protected $casts = [
        'price' => 'float',
        'delivery' => 'float',
        'expense' => 'float',
    ];
}
