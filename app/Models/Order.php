<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id', 
        'price', 
        'expense', 
        'email', 
        'first_name', 
        'last_name', 
        'phone', 
        'address', 
        'city', 
        'zip', 
        'note', 
        'ordered', 
        'accepted', 
        'delivery', 
        'payment',
        'quantity',
        'consent',
        'user_id'
    ];

    public function album(){
        return $this->belongsTo(Album::class);
    }
}
