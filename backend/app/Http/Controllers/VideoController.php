<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaveVideoResource;
use App\Http\Resources\VideoResource;
use App\Http\Resources\YouTubeVideoResource;
use App\Models\Artist;
use App\Models\DailyRecommendation;
use App\Models\Video;
use App\Services\DailyRecommendationService;
use App\Services\YouTubeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    private YouTubeService $youtube;
    private DailyRecommendationService $dailyRecommendationServide;

    public function __construct(YouTubeService $youtube, DailyRecommendationService $dailyRecommendationService)
    {
        $this->youtube = $youtube;
        $this->dailyRecommendationServide = $dailyRecommendationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
                'thumbnail' => (isset($v['thumbnail'])
                        ? json_encode($v['thumbnail']) : null),
                'published_at'  => isset($v['published_at'])
                    ? \Carbon\Carbon::parse($v['published_at'])->format('Y-m-d H:i:s') : null,
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
     * 今日のおすすめMVを取得(なければ保存)
     * @return VideoResource
     */
    public function getDailyRecommendVideo(DailyRecommendationService $dailyRecommendationService): VideoResource
    {
        return new VideoResource($dailyRecommendationService->pickDailyRecommendVideo());
    }

    /**
     * 今日のおすすめMVをDBに保存
     * @return JsonResponse
     */
    public function saveDailyRecommendVideo(DailyRecommendationService $dailyRecommendationService): JsonResponse
    {
        // GET API(getDailyRecommendVideo())を先に呼んでも、POST API(saveDailyRecommendVideo())を先に呼んでも常に結果は同じ(冪等)で壊れない
        $recommendVideo = $dailyRecommendationService->pickDailyRecommendVideo();

        return response()->json([
            'status'  => 'success',
            'message' => 'save recommendVideo'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        //
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
