<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'font_id', 'user_id', 'template_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function template(){
        return $this->belongsTo(Template::class);
    }

    public function font(){
        return $this->belongsTo(Font::class);
    }

    public function stickers(){
        return $this->hasMany(Sticker::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
