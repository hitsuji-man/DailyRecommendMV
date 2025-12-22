<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserHistoryResource;
use App\Models\UserHistory;
use App\Services\UserHistoryService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

class UserHistoryController extends Controller
{

    private UserHistoryService $userHistoryService;

    public function __construct(UserHistoryService $userHistoryService)
    {
        $this->userHistoryService = $userHistoryService;
    }

    /**
     * 視聴履歴一覧を取得
     * @return AnonymousResourceCollection
     */
    public function getUserHistories(): AnonymousResourceCollection
    {
        $userHistory = $this->userHistoryService->getUserHistories();
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
     * 指定した視聴履歴を1件削除
     * @return JsonResponse
     */
    public function deleteUserHistory(int $id): JsonResponse
    {
        try {
            $this->userHistoryService->deleteUserHistory($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'delete user history',
            ]);
        } catch (DomainException $error) {
            return response()->json([
                'status'  => 'error',
                'message' => $error->getMessage(),
            ], 404);
        }
    }

    /**
     * 視聴履歴を全件削除
     * @return JsonResponse
     */
    public function deleteAllUsersHistories(): JsonResponse
    {
        try {
            $this->userHistoryService->deleteAllUsersHistories();
            return response()->json([
                'status'  => 'success',
                'message' => 'delete all users histories',
            ]);
        } catch (RuntimeException $error) {
            return response()->json([
                'status'  => 'error',
                'message' => $error->getMessage(),
            ], 500);
        }
    }
}
