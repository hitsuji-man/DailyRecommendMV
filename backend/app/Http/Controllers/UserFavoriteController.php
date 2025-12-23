<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFavoriteResource;
use App\Services\UserFavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class UserFavoriteController extends Controller
{

    private UserFavoriteService $userFavoriteService;

    public function __construct(UserFavoriteService $userFavoriteService)
    {
        $this->userFavoriteService = $userFavoriteService;
    }

    /**
     * お気に入り一覧を取得する
     */
    public function getUserFavorites()
    {
        $userFavorites = $this->userFavoriteService->getUserFavorites(Auth::user());
        return UserFavoriteResource::collection($userFavorites);
    }

    /**
     * お気に入りを1件保存する
     * @return JsonResponse
     */
    public function saveUserFavorite(int $id): JsonResponse
    {
        try {
            $this->userFavoriteService->saveUserFavorite($id, Auth::user());

            return response()->json([
                'status'   => 'success',
                'message'  => 'save user favorite',
            ], 200);

        } catch (RuntimeException $error) {
            return response()->json([
                'status'   => 'error',
                'message'  => $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
