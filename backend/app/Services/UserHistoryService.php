<?php

namespace App\Services;

use App\Models\UserHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use RuntimeException;

class UserHistoryService
{
    /**
     * 視聴履歴を全件取得する
     * @return Collection
     */
    public function getUserHistories(): Collection
    {
        return UserHistory::orderBy('viewed_at', 'desc')->get();
    }

    /**
     * 視聴履歴を1件削除する
     * @return void
     */
    public function deleteUserHistory(int $id): void
    {
        $history = UserHistory::find($id);
        if (!$history) {
            throw new \DomainException('user history not found');
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
