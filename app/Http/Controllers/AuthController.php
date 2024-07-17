<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\TokenService;
use App\Models\RefreshToken;
use Exception;

class AuthController extends Controller
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }
    public function register(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = new User;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = bcrypt($validated['password']);
            $user->save();

            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal membuat pengguna', 'message' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email atau kata sandi tidak valid.'], 401);
        }

        return $this->tokenService->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Berhasil logged out']);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        $storedToken = RefreshToken::where('token', unserialize(hex2bin(base64_decode($refreshToken))))->first();

        if (!$storedToken || $storedToken->revoked || $storedToken->expires_at->isPast()) {
            if ($storedToken) {
                $storedToken->delete();
                return response()->json(['error' => 'Token refresh tidak valid atau telah kedaluwarsa'], 401);
            } else {
                return response()->json(['error' => 'Token refresh kosong'], 401);
            }
        }

        try {
            $newAccessToken = auth()->refresh();

            return $this->tokenService->respondWithToken($newAccessToken, $storedToken);
        } catch (Exception $e) {
            return response()->json(['error' => 'Access token tidak valid'], 401);
        }
    }
}
