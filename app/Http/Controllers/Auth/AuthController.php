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
                'status' => 'success',
                'message' => 'Register successfully, please verify your email',
                'data' => $user
            ]);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user->email_verified_at) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email not verified'
            ]);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid email or password'
            ]);
        }

        return $this->tokenService->respondWithToken($token);
    }

    public function me()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => auth()->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to get data'
            ]);
        }
    }

    public function logout(Request $request)
    {
        $storedToken = RefreshToken::where('token', hex2bin(base64_decode($request['refresh_token'])))->first();

        try {
            $storedToken->delete();
            auth()->logout(true);
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            $storedToken->delete();

            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to revoke tokens'
            ]);
        }
    }

    public function refresh(Request $request)
    {
        $storedToken = RefreshToken::where('token', hex2bin(base64_decode($request['refresh_token'])))->first();
        if (!$storedToken) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Refresh token is missing'
            ]);
        }
        try {
            $newAccessToken = auth()->refresh(true);

            return $this->tokenService->respondWithToken($newAccessToken, $storedToken);
        } catch (\Exception $e) {
            $storedToken->delete();

            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to refresh tokens'
            ]);
        }
    }

    public function revokeAllTokens()
    {
        try {
            RefreshToken::where('user_id', auth()->id())->delete();
            auth()->invalidate(true);

            return response()->json([
                'status' => 'success',
                'message' => 'All tokens revoked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to revoke tokens'
            ]);
        }
    }
}

    // public function verifyUserEmail(VerifyEmailRequest $request)
    // {
    //     return $this->emailVerifiedService->verifyEmail($request->email, $request->token);
    // }