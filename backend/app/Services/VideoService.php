<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserHistory;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class VideoService
{

    /**
     * ミックス動画リスト(トレンド+プレイリストの動画合計50件)を取得
     * ログイン中ならお気に入り状態を付与
     * @return Collection
     */
    public function getMixedDailyList(?User $user = null): Collection
    {
        /**
         * ログイン中のお気に入り判定:ローカルスコープを呼び出し
         */
        return Video::query()
            ->orderBy('id', 'desc')
            ->withIsFavorite($user)
            ->limit(50)
            ->get();
    }

    /**
     * 動画を取得し、ログイン中なら視聴履歴を保存する
     * @return Video
     */
    public function showVideoWithHistory(int $videoId, ?User $user = null): Video
    {
        /**
         * ログイン中のお気に入り判定:ローカルスコープを呼び出し
         */
        $video = Video::query()
            ->withIsFavorite($user)
            ->findOrFail($videoId);

        if ($user) {
            $this->storeUserHistory($user->id, $video->id);
        }

        return $video;

    }

    /**
     * 視聴履歴を保存
     */
    private function storeUserHistory(int $userId, int $videoId): void
    {
        UserHistory::create([
            'user_id'    => $userId,
            'video_id'   => $videoId,
            'viewed_at'  => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
