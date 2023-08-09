<?php

namespace App\Http\Controllers;

use App\Mail\OrderedAdminMail;
use App\Mail\OrderedMail;
use App\Models\Album;
use App\Models\Config;
use App\Models\Order;
use App\Models\Position;
use App\Models\Sticker;
use App\Models\Template;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AlbumController extends Controller
{
    public function index(){
        $albums = Album::where('user_id', auth('sanctum')->user()->id)->orderBy('title')->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $albums
        ], 200);
    }

    public function show(Album $album){
        if($album->user_id != auth('sanctum')->user()->id && auth('sanctum')->user()->role_id != 1){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljeno.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $album
        ], 200);
    }

    public function store(Request $request){
        if(!auth('sanctum')->check()){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljena akcija.'
            ], 401);
        }

        $count = Album::where('user_id', auth('sanctum')->user()->id)->count();
        if($count >= 5){
            return response()->json([
                'status' => false,
                'messages' => 'Maksimalan broj albuma.'
            ], 422);
        }

        $validation = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:50',
                'font_id' => 'required|exists:fonts,id',
                'template_id' => 'required|exists:templates,id'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $album = Album::create($request->only('title', 'font_id', 'template_id') + ['user_id' => auth('sanctum')->user()->id]);
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno napravljen album.',
                'data' => $album->id
            ], 201);
        } catch(Throwable $e){
            return response()->json([
                'status' => false,
                'message' => "Došlo je do greške."
            ], 500);
        }
    }

    public function update(Request $request, Album $album){
        if(auth('sanctum')->user()->id != $album->user_id){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljena akcija.'
            ], 403);
        }

        $validation = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:50',
                'font_id' => 'required|exists:fonts,id'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        $album->update($request->only('title', 'font_id'));

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno izmenjen album.'
        ], 200);
    }

    public function insertSticker(Request $request, Album $album){
        if(auth('sanctum')->user()->id != $album->user_id){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljena akcija.'
            ], 403);
        }

        $validation = Validator::make(
            $request->all(),
            [
                'image' => 'required',
                'position' => [
                    'required', 
                    function (string $attribute, mixed $value, Closure $fail) use ($album){
                        $exists = Position::where('template_id', $album->template_id)->where('position', $value)->exists();
                        if(!$exists){
                            $fail("Pozicija ne postoji.");
                        }
                    }
                ]
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        $fileNameBase = $album->id . '_';
        $image_parts = explode(";base64,", $request->image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_parts[1] = str_replace(' ', '+', $image_parts[1]);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $fileNameBase . $request->position . '.' . $image_type;
        
        try {
            DB::beginTransaction();

            $oldSticker = Sticker::where('album_id', $album->id)->where('position', $request->position)->first();
            if($oldSticker){
                $oldSticker->delete();
            }

            Storage::put('images/albums/' . $album->id . '/' . $fileName, $image_base64);
            $sticker = Sticker::create(['album_id' => $album->id, 'position' => $request->position, 'image' => 'albums/' . $album->id . '/' . $fileName]);

            if($oldSticker){
                Storage::delete('images/' . $oldSticker->image);
            }
            
            DB::commit();
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno otpremanje slike.',
                'data' => $sticker
            ], 201);
        } catch(Throwable $e){
            DB::rollBack();
            Storage::delete('images/albums/' . $album->id . '/' . $fileName);
            return response()->json([
                'status' => false,
                'messages' => "Došlo je do greške!",
            ], 500);
        }
    }

    public function deleteSticker(Request $request, Album $album){
        if(auth('sanctum')->user()->id != $album->user_id){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljena akcija.'
            ], 403);
        }

        try {
            $sticker = Sticker::where('album_id', $album->id)->where('position', $request->position)->first();
            if(!$sticker){
                return response()->json([
                    'status' => false,
                    'messages' => "Sličica nije pronađena!",
                ], 404);
            }

            DB::beginTransaction();
            $sticker->delete();
            DB::commit();
            Storage::delete('images/' . $sticker->image);
            
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno otpremanje slike.',
                'data' => $request->position
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'messages' => "Došlo je do greške!",
            ], 500);
        }
    }

    public function storeOld(Request $request){
        // $fileNames = [];

        if(!$request->is_order && !auth('sanctum')->check()){
            return response()->json([
                'status' => false,
                'messages' => 'Nedozvoljena akcija.'
            ], 403);
        }

        $count = Album::where('user_id', auth('sanctum')->user()->id)->count();
        if($count >= 5){
            return response()->json([
                'status' => false,
                'messages' => 'Maksimalan broj albuma.'
            ], 422);
        }

        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:50',
                    'font_id' => 'required|exists:fonts,id',
                    'template_id' => 'required|exists:templates,id',
                    'images' => [
                        'required',
                        'array',
                        function (string $attribute, mixed $value, Closure $fail) use ($request){
                            $template = Template::find($request->template_id);
                            if($template == null){
                                $fail("Šablon ne postoji.");
                            } else if(count($value) != $template->sticker_no){
                                // $fail("Broj sličica nije odgovarajući.");
                            }
                        }
                    ],
                    'images.*' => [
                        'required',
                        function (string $attribute, mixed $value, Closure $fail) use ($request){
                            $exists = Position::where('template_id', $request->template_id)->where('position', explode('.', $attribute)[1])->exists();
                            if(!$exists){
                                $fail("Pozicija ne postoji.");
                            }
                        }
                    ],
                    'is_order' => 'required|boolean',
                    'first_name' => 'required_if:is_order,true',
                    'last_name' => 'required_if:is_order,true',
                    'address' => 'required_if:is_order,true',
                    'city' => 'required_if:is_order,true',
                    'zip' => 'required_if:is_order,true',
                    'phone' => 'required_if:is_order,true',
                    'email' => 'required_if:is_order,true',
                    'quantity' => 'required_if:is_order,true',
                    'consent' => 'required_if:is_order,true'
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }

            DB::beginTransaction();

            if(auth('sanctum')->check()) {
                $album = Album::create($request->only('title', 'font_id', 'template_id') + ['user_id' => auth('sanctum')->user()->id]);

                $fileNameBase = $album->id . '_' . $album->title . '_';
                foreach($request->images as $key => $sticker){
                    $image_parts = explode(";base64,", $sticker);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_parts[1] = str_replace(' ', '+', $image_parts[1]);
                    $image_base64 = base64_decode($image_parts[1]);

                    $fileName = $fileNameBase . $key . '.' . $image_type;
                    Storage::put('images/albums/' . $album->id . '/' . $fileName, $image_base64);
                    // array_push($fileNames, 'images/albums/' . $album->id . '/' . $fileName);

                    Sticker::create(['album_id' => $album->id, 'position' => $key, 'image' => $album->id . '/' . $fileName]);
                }
            }
            
            

            if($request->is_order){
                $album2 = Album::create($request->only('title', 'font_id', 'template_id'));

                $fileNameBase = $album2->id . '_' . $album2->title . '_';
                foreach($request->images as $key => $sticker){
                    $image_parts = explode(";base64,", $sticker);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_parts[1] = str_replace(' ', '+', $image_parts[1]);
                    $image_base64 = base64_decode($image_parts[1]);

                    $fileName = $fileNameBase . $key . '.' . $image_type;
                    Storage::put('images/order_albums/' . $album2->id . '/' . $fileName, $image_base64);
                    // array_push($fileNames, 'images/albums/' . $album->id . '/' . $fileName);

                    Sticker::create(['album_id' => $album2->id, 'position' => $key, 'image' => $album2->id . '/' . $fileName]);
                }
                $price = Config::first()->price;
                $order = Order::create($request->only('first_name', 'last_name', 'address', 'city', 'zip', 'phone', 'email', 'quantity', 'consent') + ['user_id' => auth('sanctum')->user()->id, 'album_id' => $album2->id, 'price' => $price, 'ordered' => now()]);

                Mail::to($order->email)->send(new OrderedMail($order));
                Mail::to(env('MAIL_FROM'))->send(new OrderedAdminMail($order));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'messages' => 'Uspešno.'
            ], 201);
        } catch (\Throwable $e){
            DB::rollBack();
            $filesToDelete = Storage::allFiles('images/albums/' . $album->id);
            Storage::delete($filesToDelete);
            $filesToDelete2 = Storage::allFiles('images/order_albums/' . $album2->id);
            Storage::delete($filesToDelete2);
            Storage::deleteDirectory('images/albums/' . $album->id);
            Storage::deleteDirectory('images/order_albums/' . $album2->id);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPricing(){
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => Config::select('price', 'delivery')->first()
        ], 200);
    }

    public function destroy(Album $album){
        $album->delete();
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
        ], 200);
    }
}
