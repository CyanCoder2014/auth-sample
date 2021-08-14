<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function __construct()
    {

    }


    protected function googleCallBack()
    {
        $user = Socialite::driver('google')->user();

        // OAuth 2.0 providers...
        $token = $user->token;
        $refreshToken = $user->refreshToken;
        $expiresIn = $user->expiresIn;

        // OAuth 1.0 providers...
        $token = $user->token;
        $tokenSecret = $user->tokenSecret;

        // All providers...
        $user->getId();
        $user->getNickname();
        $user->getName();
        $user->getEmail();
        $user->getAvatar();

        return response()->json(['user' => $user]);
    }




}
