<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function Symfony\Component\String\b;

class VerificationController extends Controller
{
    public function verify($userId, Request $request)
    {
        try {
            if (!$request->hasValidSignature()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Url je netačan/istekao.'
                ], 401);
            }

            $user = User::findOrFail($userId);

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            return redirect()->to('/');
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resend(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email'
                ]
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Podaci su neispravni.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if ($user == null) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Korisnik ne postoji.'
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Email je već potvrđen.'
                ], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => true,
                'message' => 'Email je poslat.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
