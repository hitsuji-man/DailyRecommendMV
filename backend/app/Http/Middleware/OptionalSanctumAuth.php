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
        // ① すでに user が解決されていれば何もしない
        if (Auth::check()) {
            return $next($request);
        }

        /**
         * ② Sanctum ガードに user 解決を委ねる
         *
         * - Cookie 認証 → 解決される
         * - Bearer Token → 解決される
         * - どちらも無い → null
         */
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            Auth::setUser($user);
        }

        return $next($request);
    }
}
