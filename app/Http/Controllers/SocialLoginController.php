<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\SocialLogin;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }
    public function toProvider($driver)
    {
        return Socialite::driver($driver)->stateless()->redirect();
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
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => bcrypt(rand(1000, 9999))
                ]);
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
