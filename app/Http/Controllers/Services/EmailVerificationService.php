<?php

namespace App\Http\Controllers\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\EmailVerification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function sendVerificationlink(object $user): void
    {
        Notification::send($user, new EmailVerification($this->generateVerificationLink($user['email'])));
    }

    public function resendLink($email)
    {
        $user = User::where('email', $email)->first();

        $this->sendVerificationlink($user);
        return response()->json([
            'status' => 'success',
            'message' => 'Email verification link has been send'
        ]);
    }

    public function verifyToken(string $email, string $token)
    {
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if ($token) {
            if ($token->expired_at >= now()) {
                return $token;
            } else {
                return 'expired';
            }
        } else {
            return 'invalid';
        }
    }

    public function verifyEmail(string $email, string $token)
    {
        $user = User::where('email', $email)->first();

        if ($user->email_verified_at) {
            return 'already_verified';
        }
        $verifiedToken = $this->verifyToken($email, $token);

        if ($verifiedToken === 'expired' || $verifiedToken === 'invalid') {
            return $verifiedToken;
        }

        $user->markEmailAsVerified();
        $verifiedToken->delete();

        return 'success';
    }

    public function generateVerificationLink(string $email): string
    {
        $checkIfTokenExists = EmailVerificationToken::where('email', $email)->first();

        if ($checkIfTokenExists) {
            $checkIfTokenExists->delete();
        }

        $token = Str::uuid();
        $url = config('app.url') . "/api/success=" . $email . "&" . $token;
        $saveToken = EmailVerificationToken::create([
            "email" => $email,
            "token" => $token,
            "expired_at" => now()->addMinutes(60),
        ]);

        if ($saveToken) {
            return $url;
        }

        return '';
    }
}

    // public function sendVerificationlink(object $user): void
    // {
    //     Notification::send($user, new EmailVerification($this->generateVerificationLink($user->email)));
    // }

    // public function resendLink($email)
    // {
    //     $user = User::where('email', $email)->first();
    //     if ($user) {
    //         $this->sendVerificationlink($user);
    //     } else {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'User not found',
    //         ], 404);
    //     }
    // }

    // public function checkIfEmailIsVerified($user)
    // {
    //     if ($user->email_verified_at) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'Email has already been verified',
    //         ], 400);
    //     }
    // }

    // public function verifyToken(string $email, string $token)
    // {
    //     $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
    //     if ($token) {
    //         if ($token->expired_at >= now()) {
    //             return $token;
    //         } else {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Token expired'
    //             ], 400);
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'Invalid token'
    //         ], 400);
    //     }
    // }

    // public function verifyEmail(string $email, string $token)
    // {
    //     $user = User::where('email', $email)->first();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'User not found',
    //         ], 404);
    //     }
    //     $this->checkIfEmailIsVerified($user);
    //     $verifiedToken = $this->verifyToken($email, $token);
    //     if ($user->markEmailAsVerified()) {
    //         $verifiedToken->delete();
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Email has been verified successfully',
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'Email verification failed, please try again later.',
    //         ], 500);
    //     }
    // }

    // public function generateVerificationLink(string $email): string
    // {
    //     $checkIfTokenExists = EmailVerificationToken::where('email', $email)->first();

    //     if ($checkIfTokenExists) {
    //         $checkIfTokenExists->delete();
    //     }

    //     $token = Str::uuid();
    //     $url = config('app.url') . "/api/success=" . $email . "&" . $token;
    //     $saveToken = EmailVerificationToken::create([
    //         "email" => $email,
    //         "token" => $token,
    //         "expired_at" => now()->addMinutes(1),
    //     ]);

    //     if ($saveToken) {
    //         return $url;
    //     }

    //     return '';
    // }
