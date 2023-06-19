<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function template(){
        return $this->belongsTo(Template::class);
    }
}
