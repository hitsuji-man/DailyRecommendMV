<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserHistory;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class VideoService
{
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
