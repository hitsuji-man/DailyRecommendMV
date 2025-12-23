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
        // when句:ログイン中(withCountを追加)、未ログイン(何もしない)
        /**
         * クエリの中身:userFavoritesリレーションより、ログインユーザーでレコードが存在(お気に入り済 count=1)、存在しない(未お気に入り count=0)
         */
        return Video::query()
            ->orderBy('id', 'desc')
            ->when($user, function ($q) use ($user) {
                $q->withCount([
                    'userFavorites as is_favorite' => function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    }
                ]);
            })
            ->limit(50)
            ->get();
    }

    /**
     * 動画を取得し、ログイン中なら視聴履歴を保存する
     * @return Video
     */
    public function showVideoWithHistory(int $videoId, ?User $user = null): Video
    {
        // when句:ログイン中(withCountを追加)、未ログイン(何もしない)
        /**
         * クエリの中身:userFavoritesリレーションより、ログインユーザーでレコードが存在(お気に入り済 count=1)、存在しない(未お気に入り count=0)
         */
        $video = Video::query()
            ->when($user, function ($q) use ($user) {
                $q->withCount([
                    'userFavorites as is_favorite' => function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    }
                ]);
            })
            ->findOrFail($videoId);

        if ($user) {
            $this->storeUserHistory(Auth::id(), $video->id);
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
