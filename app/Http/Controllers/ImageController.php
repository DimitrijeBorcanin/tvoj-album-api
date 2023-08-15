<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageController extends Controller
{
    public function show($folder, $albumId, $fileName){

        $userId = auth('sanctum')->user()->id;
        if(!$userId){
            return abort('403');
        }


        $exists = null;
        if($folder == "albums"){
            $exists = Album::where('user_id', $userId)->where('id', $albumId)->first();
        } else {
            $exists = Order::where('user_id', $userId)->where('id', $albumId)->first();
        }


        if(!$exists && auth('sanctum')->user()->role_id != 1){
            return abort('403');
        }

        $imagePath = storage_path('app/images/' . $folder . '/' . $albumId . '/' . $fileName);
        
        // $width = 750; // your max width
        // $height = 750; // your max height
        // $img = \Image::make($imagePath);

        // $img->height() > $img->width() ? $width=null : $height=null;
        // $img->resize($width, $height, function ($constraint) {
        //     $constraint->aspectRatio();
        // });
        try {
            return response()->file($imagePath);
        } catch (Throwable $e){
            return abort('404');
        }
    }
}

