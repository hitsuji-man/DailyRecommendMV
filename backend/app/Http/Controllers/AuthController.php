<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * 匿名即時ログイン(冪等化)
     */
    public function anonymousLogin(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => ['required', 'uuid'],
        ]);

        // 冪等処理
        $user = User::where('device_id', $request->device_id)->first();

        if(!$user) {
            $user = User::create([
                'uuid'      => (string) Str::uuid(),
                'device_id' => $request->device_id,
                'name'      => 'ゲスト',
                'email'     => null,
                'password'  => null,
            ]);
        }

        // トークンは「毎回新規」か「既存再利用」どちらでもOK
        $token = $user->createToken('anonymous')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }
}
