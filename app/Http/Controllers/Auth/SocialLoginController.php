<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\TokenService;
use App\Models\SocialLogin;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{

    public function __construct(protected TokenService $tokenService) {}

    public function toProvider($driver)
    {
        return response()->json([
            'url' => Socialite::driver($driver)->stateless()->redirect()->getTargetUrl()
        ]);
    }

    public function handleCallback($driver)
    {
        $user = Socialite::driver($driver)->stateless()->user();

        $userAccount = SocialLogin::where('provider', $driver)->where('provider_id', $user->getId())->first();

        if ($userAccount) {
            $dbUser = $userAccount->user;
        } else {
            $dbUser = User::where('email', $user->getEmail())->first();

            if (!$dbUser) {
                $dbUser = User::create([
                    'profilepict' => $user->getAvatar(),
                    'username' => $user->getNickname(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'email_verified_at' => now(),
                    'password' => null
                ]);
            } else {
                // Jika pengguna ditemukan tetapi belum diverifikasi, verifikasi emailnya
                if (is_null($dbUser->email_verified_at)) {
                    $dbUser->email_verified_at = now();
                    $dbUser->save();
                }
            }

            SocialLogin::create([
                'provider' => $driver,
                'provider_id' => $user->getId(),
                'user_id' => $dbUser->id,
            ]);
        }


        $token = auth()->login($dbUser);

        return $this->tokenService->respondWithToken($token);
    }
}
