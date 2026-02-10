<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaveVideoResource;
use App\Http\Resources\VideoResource;
use App\Http\Resources\YouTubeVideoResource;
use App\Models\Artist;
use App\Models\Video;
use App\Services\VideoService;
use App\Services\YouTubeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class VideoController extends Controller
{
    private YouTubeService $youtube;
    private VideoService $videoService;

    public function __construct(YouTubeService $youtube, VideoService $videoService)
    {
        $this->youtube = $youtube;
        $this->videoService = $videoService;
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
        $videos = $this->videoService->getMixedDailyList(Auth::user());
        return VideoResource::collection($videos);
    }


    /**
     * ミックス動画リストを重複防止で保存する
     * @return JsonResponse
     */
    public function saveMixedDailyList(): JsonResponse
    {
        $this->videoService->saveMixedDailyList();

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
        $video = $this->videoService->showVideoWithHistory($id, Auth::user());

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
