<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function index(Album $album){
        if(auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->role_id != 1){
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
}
