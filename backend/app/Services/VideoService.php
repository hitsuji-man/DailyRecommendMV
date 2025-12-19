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
        return Video::query()
            ->orderBy('id', 'asc')
            ->when($user, function ($q) use ($user) {
                $q->withCount([
                    'favorites as is_favorite' => function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    }
                ]);
            })
            ->get();
    }

    /**
     * 動画を取得し、ログイン中なら視聴履歴を保存する
     * @return Video
     */
    public function showVideoWithHistory(int $videoId, ?User $user = null): Video
    {
        $video = Video::findOrFail($videoId);

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
