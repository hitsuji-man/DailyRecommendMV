<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * 未認証時のリダイレクト先
     */
    protected function redirectTo(Request $request): ?string
    {
        // APIリクエストはリダイレクトしない
        if ($request->expectsJson()) {
            return null;
        }

        // web 用（もし使うなら）
        return route('login');
    }
}
