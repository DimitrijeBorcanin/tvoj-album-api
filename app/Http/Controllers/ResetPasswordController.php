<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function sendEmail(Request $request){
        $validation = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );
     
        if($status != Password::RESET_LINK_SENT){
            return response()->json([
                'status' => false,
                'message' => __($status)
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => __($status)
        ], 200);
    }

    public function resetPassword(Request $request){
        $validation = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|confirmed|string|min:8|max:150',
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );

        if($status != Password::PASSWORD_RESET){
            return response()->json([
                'status' => false,
                'message' => __($status)
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => __($status)
        ], 200);
    }
}
