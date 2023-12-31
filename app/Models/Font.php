<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Font extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function albums(){
        return $this->hasMany(Album::class);
    }
}
