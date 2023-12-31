<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(){
        $user = User::findOrFail(auth('sanctum')->user()->id);
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $user
        ], 200);
    }

    public function update(Request $request){
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'phone' => 'required|string|regex:/^[+]?\d{9,13}$/',
                    'address' => 'required|string|max:100',
                    'city' => 'required|string|max:100',
                    'zip' => 'required|integer|min:11000|max:40000'
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }
    
            auth('sanctum')->user()->update($validation->valid());

            return response()->json([
                'status' => true,
                'message' => 'Profil uspešno ažuriran.'
            ], 200);
        } catch (\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request){
        try {
            $user = User::find(Auth::id());
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pogrešna lozinka.'
                ], 422);
            }
    
            $validation = Validator::make(
                $request->all(),
                [
                    'password' => 'required|confirmed|string|min:8|max:150'
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }
    
            auth('sanctum')->user()->password = Hash::make($request->password);
            auth('sanctum')->user()->save();
    
            return response()->json([
                'status' => true,
                'messages' => 'Uspešno promenjena lozinka.'
            ], 200);
        } catch (\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request){
        try {
            DB::beginTransaction();
            if (!Auth::attempt(['email' => Auth::user()->email, 'password' => $request->old_password])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pogrešna lozinka.'
                ], 422);
            }

            Auth::user()->delete();
            DB::commit();
        } catch (\Throwable $e){
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
