<?php

namespace App\Http\Controllers;

use App\Mail\AcceptedMail;
use App\Mail\DeliveryMail;
use App\Mail\DeniedMail;
use App\Mail\OrderedAdminMail;
use App\Mail\OrderedMail;
use App\Models\Album;
use App\Models\Config;
use App\Models\Order;
use App\Notifications\DeniedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OrderController extends Controller
{

    private $limit = 10;

    public function index(Request $request){
        $page = $request->page ?? 1;

        $orders = Order::with('album.template')->where('user_id', auth('sanctum')->user()->id);

        if($request->filters){
            if($request->filters["status"]){
                $orders = $this->applyStatus($request, $orders);
            }
        }
        
        $count = $orders->count();
        $orders = $orders->orderBy('ordered', 'desc')->offset(($page - 1) * $this->limit)->limit($this->limit)->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $orders,
            'pagination' => [
                'count' => $count,
                'page' => $page,
                'totalPages' => ceil($count / $this->limit)
            ]
        ], 200);
    }

    public function indexAdmin(Request $request){
        $page = $request->page ?? 1;

        $orders = Order::with('album.template');

        if($request->filters){
            if($request->filters["status"]){
                $orders = $this->applyStatus($request, $orders);
            }
        }
        
        $count = $orders->count();
        $orders = $orders->orderBy('ordered', 'desc')->offset(($page - 1) * $this->limit)->limit($this->limit)->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $orders,
            'pagination' => [
                'count' => $count,
                'page' => $page,
                'totalPages' => ceil($count / $this->limit)
            ]
        ], 200);
    }

    public function store(Request $request, Album $album){
        $validation = Validator::make(
            $request->all(),
            [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'phone' => 'required|string|regex:/^[+]?\d{9,13}$/',
                'address' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip' => 'required|integer|min:11000|max:40000',
                'email' => 'required|email',
                'quantity' => 'required|integer|min:1',
                'consent' => 'required|boolean',
                'comment' => 'nullable|string|max:500'
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
            DB::beginTransaction();

            $newAlbum = $album->replicate();
            $newAlbum->user_id = null;
            $newAlbum->save();

            $price = Config::first()->price;
            $order = Order::create($request->only('first_name', 'last_name', 'address', 'city', 'zip', 'phone', 'email', 'quantity', 'consent', 'comment') + ['user_id' => auth('sanctum')->user()->id, 'album_id' => $newAlbum->id, 'price' => $price, 'ordered' => now()]);

            foreach($album->stickers as $sticker){
                $image = $sticker->image;
                $imageParts = explode("/", $image);
                $imageParts2 = explode("_", $imageParts[2]);

                $newSticker = $sticker->replicate();
                $newSticker->album_id = $newAlbum->id;
                $newSticker->image = 'order_albums/' . $order->id . '/' . $order->id . '_' . $imageParts2[1];
                $newSticker->save();

                Storage::copy('images/albums/' . $album->id . '/' . $imageParts[2], 'images/order_albums/' . $order->id . '/' . $order->id . '_' . $imageParts2[1]);
            }

            Mail::to($order->email)->send(new OrderedMail($order));
            Mail::to(env("MAIL_FROM_ADDRESS"))->send(new OrderedAdminMail($order));

            DB::commit();

            return response()->json([
                'status' => true,
                'messages' => 'Uspešno.',
                'data' => $order
            ], 200);
        } catch (Throwable $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request, Order $order){
        try {
            DB::beginTransaction();
            if($request->status == "accepted"){
                $config = Config::first();
                $validation = Validator::make(
                    $request->all(),
                    [
                        'price' => ['required', 'numeric',
                            function($attribute, $value, $fail) use ($config) {
                                if($value > $config->price){
                                    $fail("Cena je veća od podešene!");
                                }
                            }
                        ],
                        'delivery' => ['required', 'numeric',
                            function($attribute, $value, $fail) use ($config) {
                                if($value > $config->delivery){
                                    $fail("Cena je veća od podešene!");
                                }
                            }
                        ],
                        'expense' => ['required', 'numeric']
                    ]
                );
        
                if ($validation->fails()) {
                    return response()->json([
                        'status' => false,
                        'messages' => 'Podaci su neispravni.',
                        'errors' => $validation->errors()
                    ], 422);
                }

                $order->price = $request->price + $request->delivery;
                $order->expense = $request->expense;
            }

            if($request->status == "denied"){
                $validation = Validator::make(
                    $request->all(),
                    [
                        'note' => 'required|string|max:255'
                    ]
                );
        
                if ($validation->fails()) {
                    return response()->json([
                        'status' => false,
                        'messages' => 'Podaci su neispravni.',
                        'errors' => $validation->errors()
                    ], 422);
                }

                $order->note = $request->note;
            }

            $order[$request->status] = now();
            $order->save();

            if($request->status == "accepted"){
                Mail::to($order->email)->send(new AcceptedMail($order));
            }

            if($request->status == "denied"){
                Mail::to($order->email)->send(new DeniedMail($order));
            }

            if($request->status == "delivery"){
                Mail::to($order->email)->send(new DeliveryMail($order));
            }

            if($request->status == "denied" || $request->status == "payment"){
                $album = Album::find($order->album_id);
                $album->stickers()->delete();
                $filesToDelete = Storage::allFiles('images/order_albums/' . $order->id);
                Storage::delete($filesToDelete);
                Storage::deleteDirectory('images/order_albums/' . $order->id);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'messages' => 'Uspešno.',
            ], 200);
        } catch (\Throwable $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function applyStatus($request, $orders){
        switch($request->filters["status"]){
            case "ordered":
                $orders = $orders->whereNull('cancelled')->whereNull('accepted')->whereNull('denied');
                break;
            case "accepted":
                $orders = $orders->whereNotNull('accepted')->whereNull('delivery');
                break;
            case "denied":
                $orders = $orders->whereNotNull('denied')->whereNull('delivery');
                break;
            case "delivery":
                $orders = $orders->whereNotNull('delivery')->whereNull('payment');
                break;
            case "payment":
                $orders = $orders->whereNotNull('payment');
                break;
            case "all":
                break;
            default:
                break;
        }

        return $orders;
    }

    public function downloadStickers(Order $order){
        $album = Album::find($order->album_id);
        if(!$album){
            return response()->json([
                'status' => false,
                'message' => "Album nije pronađen."
            ], 404);
        }

        $zip_file = $order->id;
        $zip = new \ZipArchive();
        

        $stickers = $album->stickers;
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach($stickers as $sticker){
            $zip->addFile(storage_path('app/images/' . $sticker->image), $sticker->image);
        }
        $zip->close();
        
        return response()->download($zip_file);
    }
}
