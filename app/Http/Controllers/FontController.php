<?php

namespace App\Http\Controllers;

use App\Models\Font;
use Illuminate\Http\Request;

class FontController extends Controller
{
    public function index(){
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => Font::all()
        ], 200);
    }
}
