<?php

namespace App\Http\Controllers\Services;

use App\Models\ResetPassword;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

class ResetPasswordService
{
    public function sendVerificationlink(object $user): void
    {
        Notification::send($user, new ResetPasswordNotification($this->generateVerificationLink($user['email'])));
    }

    public function verifyCode($data)
    {
        $resetRequest = ResetPassword::where('email', $data['email'])->where('code', $data['code'])->first();

        if (!$resetRequest) {
            return response()->json([
                'type' => 2,
                'status' => 'error',
                'message' => 'Invalid email or reset code'
            ]);
        }

        if ($resetRequest->expired_at < now()) {
            return response()->json([
                'type' => 3,
                'status' => 'error',
                'message' => 'Reset code has expired'
            ]);
        }

        $resetRequest->reset = true;
        $resetRequest->save();

        return response()->json([
            'type' => 1,
            'status' => 'success',
            'message' => 'Reset code verified successfully'
        ]);
    }

    public function resetPass($data)
    {
        $check = ResetPassword::where('email', $data['email'])->first();
        $user = User::where('email', $data['email'])->first();

        if (!$check || !$user) {
            return response()->json([
                'type' => 3,
                'status' => 'error',
                'message' => 'Invalid email or reset request'
            ]);
        }

        if ($check->reset) {
            $user->password = bcrypt($data['password']);
            $user->save();
            $check->delete();

            return response()->json([
                'type' => 1,
                'status' => 'success',
                'message' => 'Reset password immediately'
            ]);
        } else {
            return response()->json([
                'type' => 2,
                'status' => 'error',
                'message' => 'Please verify the reset code first'
            ]);
        }
    }

    public function generateVerificationLink(string $email): string
    {
        $checkIfCodeExists = ResetPassword::where('email', $email)->first();

        if ($checkIfCodeExists) {
            $checkIfCodeExists->delete();
        }

        $code = mt_rand(100000, 999999);
        $saveToken = ResetPassword::create([
            "email" => $email,
            "code" => $code,
            "reset" => false,
            "expired_at" => now()->addMinutes(5),
        ]);

        if ($saveToken) {
            return $code;
        }

        return '';
    }
}
