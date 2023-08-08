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

        $order = Order::where('user_id', $userId)->first();

        if($order->album_id != $albumId){
            return abort('403');
        }

        $imagePath = storage_path('app/images/' . $folder . '/' . $albumId . '/' . $fileName);
        try {
            return response()->file($imagePath);
        } catch (Throwable $e){
            return abort('404');
        }
    }
}

