<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    protected string $apiKey;
    protected string $playlistId;
    protected string $baseUrl = 'https://www.googleapis.com/youtube/v3/';

    public function __construct()
    {
        // Laravel推奨のconfig()経由
        $this->apiKey = config('services.youtube.key');
        $this->playlistId = config('services.youtube.playlist_id');
    }

    /**
     * トレンド音楽動画を取得
     *
     * @param int $maxResults=30(APIが合計30までしか返さない)
     * @return array
     */
    public function fetchTrendingMusic(int $maxResults = 30): array {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . 'videos', [
                'part'       => 'snippet,statistics',
                'chart'      => 'mostPopular',
                'regionCode' => 'JP',
                'videoCategoryId' => 10,  // Music
                'maxResults' => $maxResults,
                'key'        => $this->apiKey,
            ]);

            if ($response->failed()) {
                Log::warning('YouTube API TrendingMusic failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            // JSON->PHPのitemsをキーとした連想配列を返す
            $trending = $response->json()['items'];

            // published_at フォーマットDATETIME型('Y-m-d H:i:s')
            // source_type "trend" を付与
            $trending = array_map(function($item){
                $item['snippet']['publishedAt'] = Carbon::parse($item['snippet']['publishedAt'])->timezone('Asia/Tokyo')->toIso8601String();
                $item['sourceType'] = 'trend';
                return $item;
            }, $trending);

            // nullまたは未定義なら空配列を返す
            return $trending ?? [];

        } catch (\Exception $error) {
            // グローバル名前空間 Exception を補足
            Log::error('YouTube API TrendingMusic exception', [
                'message' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * PlaylistItemを取得( プレイリスト「Catch Up Japan」 )
     * @param int $maxResults
     * @return array
     */
    public function fetchPlaylistItems(int $maxResults = 50): array {
        try {
            // 1. PlaylistItems API
            $response = Http::timeout(5)->get($this->baseUrl . 'playlistItems', [
                'part'       => 'snippet,contentDetails',
                'playlistId' => $this->playlistId,
                'maxResults' => $maxResults,
                'key'        => $this->apiKey,
            ]);

            if ($response->failed()) {
                Log::warning('YouTube API Playlist failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            $items = $response->json()['items'] ?? [];
            if (empty($items)) return [];

            // 2. videoId抽出
            $videoIds = collect($items)
                ->map(fn($i) => $i['contentDetails']['videoId'] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if(empty($videoIds)) return [];

            // 3. Video API (statisticsを取得)
            $resVideo = Http::timeout(5)->get($this->baseUrl . 'videos', [
                'part' => 'snippet,statistics',
                'id'   => implode(',', $videoIds),
                'key'  => $this->apiKey,
            ]);

            if($resVideo->failed()) {
                Log::warning('Youtube API Video failed (statistics)', [
                    'status' => $resVideo->status(),
                    'body'   => $resVideo->body(),
                ]);
                return [];
            }

            $videos = $resVideo->json()['items'] ?? [];

            // 4. videoId => videoData形式に並べ替え
            // keyBy('id')で各videoIdを辞書のキーとして保存
            $videoMap = collect($videos)->keyBy('id');

            // 5. 結合 (snippet + statistics + playlist snippet)
            $merged = [];

            foreach ($items as $item) {
                $videoId = $item['contentDetails']['videoId'];
                if (!isset($videoMap[$videoId])) continue;

                $v = $videoMap[$videoId];

                $merged[] = [
                    'videoId'      => $videoId,
                    'title'        => $v['snippet']['title'] ?? null,
                    'description'  => $v['snippet']['description'] ?? null,
                    'channelId'    => $v['snippet']['channelId'] ?? null,
                    'channelTitle' => $v['snippet']['channelTitle'] ?? null,
                    'thumbnail'    => $v['snippet']['thumbnails']['high'] ?? null,
                    'publishedAt'  => $v['snippet']['publishedAt'] ?? null,
                    'viewCount'    => $v['statistics']['viewCount'] ?? null,
                    'likeCount'    => $v['statistics']['likeCount'] ?? null,
                ];
            }

            return $merged;

        } catch (\Exception $error) {
            // グローバル名前空間 Exception を補足
            Log::error('YouTube API Playlists exception', [
                'message' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * トレンド30件 + PlaylistItem20件 = 合計50件の動画のリスト
     * @return array
     */
    public function buildMixedDailyList(): array {
        try {
            $trending = $this->fetchTrendingMusic(30);
            $playlistItems = $this->fetchPlaylistItems(50);

            // sourceTypeを付与: 'trend' or 'playlist'
            $trending = array_map(function ($item) {
                $item['sourceType'] = 'trend';
                return $item;
            }, $trending);

            $playlistItems = array_map(function($item) {
                $item['sourceType'] = 'playlist';
                return $item;
            }, $playlistItems);

            $playlistItemsLimited=array_slice($playlistItems, 0, 20);

            return array_merge($trending, $playlistItemsLimited);
        } catch (\Exception $error) {
            Log::error('YouTube API Merge Exception', [
                'message' => $error->getMessage(),
                'trace'   => $error->getTraceAsString(),
            ]);

            return [];
        }
    }
}
