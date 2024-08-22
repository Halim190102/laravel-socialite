<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\TokenService;
use App\Http\Controllers\Services\EmailVerificationService;
use App\Http\Controllers\Services\ImageService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\StoreUserRequest;
// use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use App\Models\RefreshToken;
use Exception;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{


    public function __construct(
        protected TokenService $tokenService,
        protected EmailVerificationService $emailVerifiedService,
        protected ImageService $imageService
    ) {}

    public function register(StoreUserRequest $request)
    {
        $picture = $this->imageService->postImage($request['profilepict']);

        $user = User::create([
            'username' => $request['username'],
            'profilepict' => $picture,
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
        ]);
        if ($user) {
            $this->emailVerifiedService->sendVerificationlink($user);
            return response()->json([
                'type' => 1,
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => $user
            ]);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'type' => 4,
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'type' => 3,
                'status' => 'error',
                'message' => 'Email not verified'
            ]);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'type' => 2,
                'status' => 'error',
                'message' => 'Invalid email or password'
            ]);
        }

        return $this->tokenService->respondWithToken($token);
    }

    public function me()
    {
        return response()->json([
            'type' => 1,
            'status' => 'success',
            'data' => auth()->user()
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'type' => 1,
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        $storedToken = RefreshToken::where('token', hex2bin(base64_decode($refreshToken)))->first();

        if (!$storedToken || $storedToken->revoked || $storedToken->expires_at->isPast()) {
            if ($storedToken) {
                $storedToken->delete();
                return response()->json([
                    'type' => 2,
                    'status' => 'error',
                    'message' => 'Refresh token is invalid or has expired'
                ]);
            } else {
                return response()->json([
                    'type' => 3,
                    'status' => 'error',
                    'message' => 'Refresh token is missing'
                ]);
            }
        }

        try {
            $newAccessToken = auth()->refresh();

            return $this->tokenService->respondWithToken($newAccessToken, $storedToken);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 0,
                'status' => 'error',
                'message' => 'Access token tidak valid'
            ]);
        }
    }
}

    // public function verifyUserEmail(VerifyEmailRequest $request)
    // {
    //     return $this->emailVerifiedService->verifyEmail($request->email, $request->token);
    // }