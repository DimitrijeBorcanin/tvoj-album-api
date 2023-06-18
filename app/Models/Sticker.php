<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sticker extends Model
{
    use HasFactory;

    protected $fillable = ['album_id', 'position', 'image'];

    public function album(){
        return $this->belongsTo(Album::class);
    }
}
