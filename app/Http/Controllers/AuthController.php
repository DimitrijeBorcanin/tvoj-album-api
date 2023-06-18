<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pogrešni kredencijali.'
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Email nije potvrđen.'
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Uspešna prijava.',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email|unique:users',
                    'password' => 'required|confirmed',
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

            $user = User::create($request->only('email', 'first_name', 'last_name', 'phone', 'address', 'city', 'zip') + ['password' => Hash::make($request->password)]);

            $user->sendEmailVerificationNotification();

            DB::commit();

            return response()->json($user);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
