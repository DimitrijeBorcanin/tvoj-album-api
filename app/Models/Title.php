<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    use HasFactory;

    protected $guarded = ["id", "created_at", "updated_at"];

    public function album(){
        return $this->belongsTo(Album::class);
    }

    public function font(){
        return $this->belongsTo(Font::class);
    }
}
