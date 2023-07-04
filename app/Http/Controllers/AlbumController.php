<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Position;
use App\Models\Template;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request){
        $fileNames = [];
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:50',
                    'font_id' => 'required|exists:fonts,id',
                    'template_id' => 'required|exists:templates,id',
                    'stickers' => [
                        'required',
                        'array',
                        function (string $attribute, mixed $value, Closure $fail) use ($request){
                            $template = Template::find($request->template_id);
                            if($template == null){
                                $fail("Šablon ne postoji.");
                            }
    
                            if(count($value) != $template->sticker_no){
                                $fail("Broj sličica nije odgovarajući.");
                            }
                        }
                    ],
                    'stickers.*.position' => [
                        'required',
                        'distinct',
                        function (string $attribute, mixed $value, Closure $fail) use ($request){
                            $exists = Position::where('template_id', $request->template_id)->where('position', $value)->exists();
                            if(!$exists){
                                $fail("Pozicija ne postoji.");
                            }
                        }
                    ],
                    'stickers.*.image' => 'required|base64|mimes:jpeg|size:2000'
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }

            // DB::beginTransaction();

            // $album = Album::create($request->only('title', 'font_id', 'template_id') + ['user_id' => Auth::id()]);

            
            // $fileNameBase = $album->id . '_' . $album->title . '_' . $album->created_at . '_' . Auth::id();
            // foreach($request->stickers as $sticker){
            //     $fileName = $fileNameBase . '_' . $sticker->position . '_' . time() . $sticker->image->extension();
            //     $sticker->image->storeAs('public/images/' . $fileNameBase, $fileName);
            //     array_push($fileNames, 'public/images/' . $fileNameBase . '/' .$fileName);

            //     Position::create(['album_id' => $album->id, 'position' => $sticker->position, 'image' => $fileNameBase . '/' . $fileName]);
            // }

            // DB::commit();
        } catch (\Throwable $e){
            DB::rollBack();
            Storage::delete($fileNames);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
