<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaveVideoResource;
use App\Http\Resources\VideoResource;
use App\Http\Resources\YouTubeVideoResource;
use App\Models\Artist;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\Video;
use App\Services\YouTubeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    private YouTubeService $youtube;

    public function __construct(YouTubeService $youtube)
    {
        $this->youtube = $youtube;
    }

    /**
     * トレンド動画のAPIレスポンスを返す
     * @return AnonymousResourceCollection
     */
    public function getTrendingMusicVideos(): AnonymousResourceCollection
    {
        $videos = $this->youtube->fetchTrendingMusic(30);
        // ResourceでAPIレスポンス化
        return YouTubeVideoResource::collection($videos);
    }

    /**
     * ミックス動画リスト(トレンド+プレイリストの動画合計50件)のAPIレスポンスを返す
     * @return AnonymousResourceCollection
     */
    public function getMixedDailyList(): AnonymousResourceCollection
    {
        $videos = Video::orderBy('id', 'asc')->get();
        return VideoResource::collection($videos);
    }


    /**
     * ミックス動画リストを重複防止で保存する
     * @return JsonResponse
     */
    public function saveMixedDailyList(Request $request): JsonResponse
    {
        $rawVideos = $this->youtube->buildMixedDailyList();

        $saveData = SaveVideoResource::collection($rawVideos)->toArray(request());

        $upsertData = [];

        foreach ($saveData as $v) {
            // Artist 作成 or 取得
            $artist = Artist::firstOrCreate(
    ['channel_id' => $v['channel_id']],
        ['channel_title' => $v['channel_title']],
            );

            $upsertData[] = [
                ...$v,
                'artist_id'     => $artist->id,
                'thumbnail'     => (isset($v['thumbnail'])
                                ? json_encode($v['thumbnail']) : null),
                'published_at'  => (isset($v['published_at'])
                                ? Carbon::parse($v['published_at'])->timezone('Asia/Tokyo')->format('Y-m-d H:i:s') : null),
                'created_at'    => now()->format('Y-m-d H:i:s'),
                'updated_at'    => now()->format('Y-m-d H:i:s'),
            ];
        }

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

        return response()->json([
            'status'  => 'success',
            'message' => 'upsert video',
        ]);
    }

    /**
     * 特定の動画詳細を表示する
     * @return VideoResource
     */
    public function showVideo(int $id): VideoResource
    {
        $video = Video::findOrFail($id);

        // TODO
        // 仮にログイン中にする
        $user = User::find(1);
        // そのユーザーでログイン状態にする
        Auth::login($user);

        // ログイン中なら視聴履歴を保存
        if (Auth::check()) {
            UserHistory::create([
                'user_id'    => Auth::id(),
                'video_id'   => $video->id,
                'viewed_at'  => now()->format('Y-m-d H:i:s'),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return new VideoResource($video);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        //
    }
}
