<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    public function index(){
        $albums = Album::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $albums
        ], 200);
    }

    public function show(Album $album){
        if($album->user_id != Auth::id()){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljeno.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $album->load('stickers')
        ], 200);
    }
}
