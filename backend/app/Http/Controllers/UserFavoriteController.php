<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFavoriteResource;
use App\Services\UserFavoriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
