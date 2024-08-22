<?php

namespace App\Http\Controllers\Services;

use App\Models\RefreshToken;
use Illuminate\Support\Str;

class TokenService
{
    public function respondWithToken($token, $storedToken = null)
    {
        $refreshToken = Str::random(20) . '.' . Str::random(108) . '.' . Str::random(20);

        if ($storedToken) {
            $storedToken->update([
                'token' => $refreshToken,
                'expires_at' => now()->addMinutes(11520),
            ]);
        } else {
            RefreshToken::create([
                'user_id' => auth()->id(),
                'token' => $refreshToken,
                'expires_at' => now()->addMinutes(11520), // Set the expiration time for the refresh token
            ]);
        }

        return response()->json([
            'type' => 1,
            'access_token' => $token,
            'refresh_token' => base64_encode(bin2hex($refreshToken)),
            'token_type' => 'bearer',
        ]);
    }
}
