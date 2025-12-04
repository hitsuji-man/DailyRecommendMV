<?php

namespace App\Http\Controllers;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoController extends Controller
{
    private YouTubeService $youtube;

    public function __construct(YouTubeService $youtube)
    {
        $this->youtube = $youtube;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * トレンドとプレイリストがミックスされた合計50件の動画リストのAPIレスポンスを返す
     * @return AnonymousResourceCollection
     */
    public function getMixedDailyList(): AnonymousResourceCollection
    {
        $videos = $this->youtube->buildMixedDailyList();
        // ResourceでAPIレスポンス化
        return VideoResource::collection($videos);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
