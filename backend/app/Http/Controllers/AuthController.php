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

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'device_id' => ['required', 'uuid'],
        ]);

        Log::info('register: validation passed', [
            'email' => $request->email,
            'device_id' => $request->device_id,
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $user = $request->user();

                Log::info('tx: start', [
                    'has_user' => (bool) $user,
                    'user_id' => $user?->id,
                    'user_email' => $user?->email,
                ]);

                DB::select('select 1');
                Log::info('tx: checkpoint 1 ok');

                if ($user) {
                    Log::info('tx: upgrade anonymous user start', [
                        'user_id' => $user->id,
                        'before_email' => $user->email,
                    ]);

                    if ($user->email !== null) {
                        Log::warning('tx: already registered user tried register', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                        ]);

                        throw ValidationException::withMessages([
                            'user' => ['既に登録済みです'],
                        ]);
                    }

                    Log::info('tx: before user update', [
                        'user_id' => $user->id,
                        'new_email' => $request->email,
                    ]);

                    $user->update([
                        'name'     => $request->name,
                        'email'    => $request->email,
                        'password' => $request->password,
                    ]);

                    Log::info('tx: after user update', [
                        'user_id' => $user->id,
                    ]);

                    DB::select('select 1');
                    Log::info('tx: checkpoint 2 ok');

                    Log::info('tx: before all anonymous tokens delete', [
                        'user_id' => $user->id,
                    ]);

                    $user->tokens()->delete();

                    Log::info('tx: after all anonymous tokens delete', [
                        'user_id' => $user->id,
                    ]);

                    DB::select('select 1');
                    Log::info('tx: checkpoint 3 ok');
                } else {
                    Log::info('tx: before user create', [
                        'email' => $request->email,
                    ]);

                    $user = User::create([
                        'uuid'     => (string) Str::uuid(),
                        'name'     => $request->name,
                        'email'    => $request->email,
                        'password' => $request->password,
                    ]);

                    Log::info('tx: after user create', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);

                    DB::select('select 1');
                    Log::info('tx: checkpoint 4 ok');
                }

                Log::info('tx: before device token delete', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                $user->tokens()
                    ->where('name', $request->device_id)
                    ->delete();

                Log::info('tx: after device token delete', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                DB::select('select 1');
                Log::info('tx: checkpoint 5 ok');

                Log::info('tx: before createToken', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                $token = $user->createToken(
                    $request->device_id,
                    ['user']
                )->plainTextToken;

                Log::info('tx: after createToken', [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);

                DB::select('select 1');
                Log::info('tx: checkpoint 6 ok');

                return [
                    'user'  => $user,
                    'token' => $token,
                ];
            });

            Log::info('register: transaction committed', [
                'user_id' => $result['user']->id,
                'email' => $result['user']->email,
            ]);

            return response()->json([
                'token' => $result['token'],
                'user'  => $result['user'],
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
