<?php

namespace App\Http\Controllers\Auth;

class FacebookController extends SocialLoginController
{
    protected function provider(): string
    {
        return 'facebook';
    }

    protected function label(): string
    {
        return 'Facebook';
    }
}
