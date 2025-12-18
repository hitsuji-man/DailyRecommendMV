<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserHistoryResource;
use App\Models\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserHistoryController extends Controller
{
    /**
     * 視聴履歴一覧を取得
     * @return AnonymousResourceCollection
     */
    public function getUserHistories(): AnonymousResourceCollection
    {
        $userHistory = UserHistory::orderBy('viewed_at', 'desc')->get();
        return UserHistoryResource::collection($userHistory);
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
