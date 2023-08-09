<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Sticker;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function index(Album $album){
        if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozovljeno.'
            ], 403);
        }

        $stickers = [];
        foreach($album->stickers as $sticker){
            $stickers[$sticker["position"]] = $sticker["imageBase64"];
        }

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $stickers
        ], 200);
    }

    public function show(Album $album, $position){
        if($album->user_id != auth('sanctum')->user()->id && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljeno.'
            ], 403);
        }

        $sticker = Sticker::where('album_id', $album->id)->where('position', $position)->first();

        if(!$sticker){
            return response()->json([
                'status' => true,
                'messages' => 'Ne postoji.',
                'data' => null
            ], 200);
        }    

        $imagePath = storage_path('app/images/' . $sticker->image);
        return response()->file($imagePath);

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $sticker->imageBase64
        ], 200);
    }
}
