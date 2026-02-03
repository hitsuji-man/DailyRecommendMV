<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserHistory;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class UserHistoryService
{
    /**
     * 視聴履歴を全件取得する
     * @return Collection
     */
    public function getUserHistories(User $user): Collection
    {
        return UserHistory::orderBy('viewed_at', 'desc')
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * 視聴履歴を1件削除する
     * @return void
     */
    public function deleteUserHistory(int $id): void
    {
        $userId = Auth::id();

        if (!$userId) {
            throw new DomainException('Unauthenticated');
        }

        $history = UserHistory::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$history) {
            throw new DomainException('user history not found');
        }
        $history->delete();
    }

    /**
     * 視聴履歴を全件削除する
     * @return void
     */
    public function deleteAllUsersHistories(): void
    {
        try {
            UserHistory::query()->delete();
        } catch (QueryException $error) {
            // DBレベルの例外をドメイン例外に変換
            throw new RuntimeException('failed to delete all users histories', 0, $error);
        }
    }
}
