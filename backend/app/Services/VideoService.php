<?php

namespace App\Services;

use App\Http\Resources\SaveVideoResource;
use App\Models\Artist;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\Video;
use App\Services\YouTubeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class VideoService
{

    private YouTubeService $youtube;

    public function __construct(YouTubeService $youtube)
    {
        $this->youtube = $youtube;
    }

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
     * ミックス動画リストを重複防止で保存する
     * @return void
     */
    public function saveMixedDailyList(): void
    {
        $rawVideos = $this->youtube->buildMixedDailyList();

        $saveData = SaveVideoResource::collection($rawVideos)->toArray(request());

        $upsertData = [];
        $map = [];

        /**
         * artistの取得or作成:先にまとめて取得→一括作成→map化
         */
        // channel_title 一覧
        $channelTitleMap = collect($saveData)
            ->keyBy('channel_id')
            ->map(fn ($v) => $v['channel_title']);

            // channel_id 一覧
        $channelIds = collect($saveData)
            ->pluck('channel_id')
            ->unique()
            ->values();

        // 既存 artist 取得
        $artists = Artist::whereIn('channel_id', $channelIds)->get();

        $artistMap = $artists->keyBy('channel_id');

        // 存在しない artist 作成
        $missing = $channelIds->diff($artists->pluck('channel_id'));

        $newArtists = $missing->map(fn ($id) => [
            'channel_id' => $id,
            'channel_title' => $channelTitleMap[$id],
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        if ($newArtists->isNotEmpty()) {
            Artist::insert($newArtists->toArray());
        }

        // artist 再取得
        $artistMap = Artist::whereIn('channel_id', $channelIds)
            ->get()
            ->keyBy('channel_id');


        foreach ($saveData as $v) {
            $artistId = $artistMap[$v['channel_id']]->id;

            // trendとplaylist両方から同じ動画が来る場合を想定して、youtube_idで連想配列にする
            $map[$v['youtube_id']] = [
                'youtube_id' => $v['youtube_id'],
                'artist_id' => $artistId,
                'title' => $v['title'],
                'description' => $v['description'] ?? null,
                'channel_id' => $v['channel_id'],
                'channel_title' => $v['channel_title'],
                'thumbnail' => isset($v['thumbnail'])
                    ? json_encode($v['thumbnail'])
                    : null,
                'published_at' => isset($v['published_at'])
                    ? Carbon::parse($v['published_at'])->timezone('Asia/Tokyo')->format('Y-m-d H:i:s')
                    : null,
                'view_count' => isset($v['view_count']) ? (int)$v['view_count'] : null,
                'like_count' => isset($v['like_count']) ? (int)$v['like_count'] : null,
                'source_type' => $v['source_type'],
                'created_at'    => now()->format('Y-m-d H:i:s'),
                'updated_at'    => now()->format('Y-m-d H:i:s'),
            ];
        }

        $upsertData = array_values($map);

        // upsert 実行
        Video::upsert(
            $upsertData,
            ['youtube_id'], // unique key
            [
                'artist_id',
                'title',
                'description',
                'channel_id',
                'channel_title',
                'thumbnail',
                'published_at',
                'view_count',
                'like_count',
                'source_type',
                'updated_at',
            ]
        );
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
        // 冪等性:直近3秒以内に履歴があれば保存しない(3秒以内の二重保存を防止)
        $exists = UserHistory::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->where('viewed_at', '>=', now()->subSeconds(3))
            ->exists();

        if ($exists) {
            return;
        }

        UserHistory::create([
            'user_id'    => $userId,
            'video_id'   => $videoId,
            'viewed_at'  => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
