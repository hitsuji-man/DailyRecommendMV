<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 既に user がいれば何もしない
        if (Auth::check()) {
            return $next($request);
        }

        // Bearer token がある場合のみ評価
        if ($token = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                // token の所有者(User)をセット
                Auth::setUser($accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
