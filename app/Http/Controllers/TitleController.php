<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Title;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class TitleController extends Controller
{
    public function getByAlbum(Album $album){
        if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozovljeno.'
            ], 403);
        }
        
        $titles = Title::with('font')->where('album_id', $album->id)->orderBy('page')->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $titles
        ], 200);
    }

    public function show(Title $title){
        $album = $title->album;
        if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozovljeno.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $title
        ], 200);
    }

    public function store(Request $request){
        $album = Album::find($request->album_id);
        $template = $album->template;

        if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozovljeno.'
            ], 403);
        }

        $validation = Validator::make(
            $request->all(),
            [
                'album_id' => 'required|exists:albums,id',
                'page' => ['required', 'numeric',
                            function($attribute, $value, $fail) use ($template) {
                                if($value > $template->page_no){
                                    $fail("Stranica ne postoji!");
                                }
                            }
                        ],
            ]  
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        $title = Title::create($request->only('album_id', 'page'));
        $title = Title::find($title->id);
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $title
        ], 201);
    }

    public function changePage(Request $request, Title $title){
        $title->load('font');
        try {
            $album = Album::find($title->album_id);
            $template = $album->template;

            if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
                return response()->json([
                    'status' => false,
                    'messages' => 'Nedozovljeno.',
                    'data' => $title
                ], 403);
            }

            $validation = Validator::make(
                $request->all(),
                [
                    'page' => ['required', 'numeric',
                                function($attribute, $value, $fail) use ($template) {
                                    if($value > $template->page_no){
                                        $fail("Stranica ne postoji!");
                                    }
                                }
                            ]
                ]  
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors(),
                    'data' => $title
                ], 422);
            }

            $title->update($request->only('page'));
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno.',
                'data' => $title
            ], 201);
        } catch (Throwable $e){
            return response()->json([
                'status' => false,
                'message' => "Došlo je do greške!",
                'data' => $title
            ], 500);
        }
    }

    public function update(Request $request, Title $title){
        $title->load('font');
        try {
            $album = Album::find($title->album_id);
            $template = $album->template;

            if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
                return response()->json([
                    'status' => false,
                    'messages' => 'Nedozovljeno.',
                    'data' => $title
                ], 403);
            }

            $validation = Validator::make(
                $request->all(),
                [
                    'page' => ['required', 'numeric',
                                function($attribute, $value, $fail) use ($template) {
                                    if($value > $template->page_no){
                                        $fail("Stranica ne postoji!");
                                    }
                                }
                            ],
                    'content' => 'required|string|min:1|max:50',
                    'font_id' => 'required|exists:fonts,id',
                    'size' => 'required|numeric|min:1|max:6',
                    'color' => 'required|string|max:10',
                    'align' => 'required|string|max:10',
                    'width' => 'required|numeric|min:1|max:100',
                    'top' => 'required|numeric|min:5|max:95',
                    'left' => 'required|numeric|min:0|max:90'
                ]  
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors(),
                    'data' => $title
                ], 422);
            }

            $title->update($request->only('page', 'content', 'font_id', 'size', 'color', 'align', 'width', 'top', 'left'));

            $updatedTitle = Title::with('font')->where('id', $title->id)->first();
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno.',
                'data' => $updatedTitle
            ], 201);
        } catch (Throwable $e){
            return response()->json([
                'status' => false,
                'message' => "Došlo je do greške!",
                'data' => $title
            ], 500);
        }
    }

    public function destroy(Title $title){
        $album = Album::find($title->album_id);

        if((auth('sanctum')->user()->id != $album->user_id && auth('sanctum')->user()->id != $album->orders()->where('album_id', $album->id)->first()->user_id) && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozovljeno.',
            ], 403);
        }

        $title->delete();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
        ], 200);
    }
}
