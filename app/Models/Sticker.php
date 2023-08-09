<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sticker extends Model
{
    use HasFactory;

    protected $fillable = ['album_id', 'position', 'image'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['imageBase64'];

    public function getImageBase64Attribute() {
        // $imagePath = storage_path('app/images/' . $this->attributes['image']);
        // $image = "data:image/jpeg;base64,".base64_encode(file_get_contents($imagePath));
        // return $image;    
        return env("APP_URL") . "/images/" . $this->attributes["image"];
    }

    public function album(){
        return $this->belongsTo(Album::class);
    }
}
