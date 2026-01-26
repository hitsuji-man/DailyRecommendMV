<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            throw ValidationException::withMessages([
                'email' => ['認証に失敗しました'],
            ]);
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
     * ユーザー登録(昇格 or 新規)
     */
    public function register(Request $request): JsonResponse
    {
        // バリデーション(トランザクション外)
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'device_id' => ['required', 'uuid'],
        ]);

        // トランザクション開始
        $result = DB::transaction(function () use ($request) {
            // 現在のユーザー(匿名 or null)
            $user = $request->user();

            if ($user) {
                // 匿名ユーザー → 正規ユーザーに昇格
                if ($user->email !== null) {
                    throw ValidationException::withMessages([
                        'user'  => ['既に登録済みです'],
                    ]);
                }

                $user->update([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // 匿名トークンは全削除
                $user->tokens()->delete();
            } else {
                // 完全新規ユーザー作成
                $user = User::create([
                    'uuid'     => (string) Str::uuid(),
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            }

            // 同一 device_id の token を上書き
            $user->tokens()
                ->where('name', $request->device_id)
                ->delete();

            // 正規ユーザー用 token 発行
            $token = $user->createToken(
                $request->device_id,
                ['user']
            )->plainTextToken;

            // レスポンス用データを返す
            return [
                'user'  => $user,
                'token' => $token,
            ];
        });

        // commit後にレスポンス
        return response()->json([
            'token'  => $result['token'],
            'user'   => $result['user'],
        ], 201);
    }

    /**
     * ログイン中ユーザー情報を取得
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'is_guest' => $user->email === null,
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
