<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        // // Cookie認証:Laravelセッションログイン
        // Auth::login($user);

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    /**
     * ログイン
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required'],
            'device_id' => ['required', 'uuid'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                ['message' => '認証に失敗しました'],
                401
            );
        }

        /**
         * 端末単位トークン管理
         * (同じ device_id なら再ログインで上書き)
         */
        $user->tokens()
            ->where('name', $request->device_id)
            ->delete();

        $token = $user->createToken(
            $request->device_id,    // token nameにdevice_id
            ['user']           // abilities
        )->plainTextToken;

        return response()->json([
            'token'  => $token,
            'user'   => $user,
        ]);
    }

    /**
     * ログアウト
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました',
        ]);
    }

    /**
     * ユーザー登録
     *  - 匿名ログイン済み: 正規ユーザーへ昇格
     *  - 未ログイン: 新規ユーザー作成
     */
    public function register(Request $request): JsonResponse
    {
        DB::listen(function ($query) {
            Log::info('register sql', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
            ]);
        });

        Log::info('register: request start', [
            'email' => $request->email,
            'device_id' => $request->device_id,
            'has_auth_user' => (bool) $request->user(),
            'auth_user_id' => $request->user()?->id,
        ]);

        // バリデーション(トランザクション外)
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'device_id' => ['required', 'uuid'],
        ]);

        try {
            $user = $request->user();

            if ($user) {
                if ($user->email !== null) {
                    throw ValidationException::withMessages([
                        'user' => ['既に登録済みです'],
                    ]);
                }

                try {
                    Log::info('step: user update start', ['user_id' => $user->id]);

                    $user->update([
                        'name'     => $request->name,
                        'email'    => $request->email,
                        'password' => $request->password,
                    ]);

                    Log::info('step: user update ok', ['user_id' => $user->id]);
                } catch (\Throwable $e) {
                    Log::error('step: user update failed', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'user_id' => $user->id,
                    ]);
                    throw $e;
                }

                try {
                    Log::info('step: delete anonymous tokens start', ['user_id' => $user->id]);

                    $user->tokens()->delete();

                    Log::info('step: delete anonymous tokens ok', ['user_id' => $user->id]);
                } catch (\Throwable $e) {
                    Log::error('step: delete anonymous tokens failed', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'user_id' => $user->id,
                    ]);
                    throw $e;
                }
            } else {
                try {
                    Log::info('step: user create start', ['email' => $request->email]);

                    $user = User::create([
                        'uuid'     => (string) Str::uuid(),
                        'name'     => $request->name,
                        'email'    => $request->email,
                        'password' => $request->password,
                    ]);

                    Log::info('step: user create ok', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('step: user create failed', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'email' => $request->email,
                    ]);
                    throw $e;
                }
            }

            try {
                Log::info('step: delete device token start', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                $user->tokens()
                    ->where('name', $request->device_id)
                    ->delete();

                Log::info('step: delete device token ok', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('step: delete device token failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);
                throw $e;
            }

            try {
                Log::info('step: createToken start', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                $token = $user->createToken(
                    $request->device_id,
                    ['user']
                )->plainTextToken;

                Log::info('step: createToken ok', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('step: createToken failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);
                throw $e;
            }

            return response()->json([
                'token' => $token,
                'user'  => $user,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('register: failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->email,
                'device_id' => $request->device_id,
                'has_auth_user' => (bool) $request->user(),
                'auth_user_id' => $request->user()?->id,
            ]);

            throw $e;
        }
    }

    /**
     * ログイン中ユーザー情報を取得
     */
    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * ユーザー情報更新
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // 匿名ユーザーは更新不可
        if ($user->email === null) {
            abort(403, '匿名ユーザーはユーザー情報を更新できません');
        }

        // バリデーション
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                // 自分自身を除外して unique チェック
                'unique:users,email,' . $user->id,
            ],
        ]);

        // 更新
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'ユーザー情報を更新しました',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * パスワード変更(正規ユーザーのみ)
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        // 匿名ユーザーは不可
        if ($user->email === null) {
            abort(403, '匿名ユーザーはパスワードを変更できません');
        }

        // バリデーション
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 現在のパスワード確認
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが正しくありません'],
            ]);
        }

        // 更新
        $user->update([
            'password'  => Hash::make($request->new_password),
        ]);

        /**
         * セキュリティ上の推奨:
         * - 現在の端末以外のトークンを失効
         * - 現在のトークンは維持(UX優先)
         */
        $user->tokens()
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        return response()->json([
            'message'  => 'パスワードを変更しました',
        ]);
    }
}
