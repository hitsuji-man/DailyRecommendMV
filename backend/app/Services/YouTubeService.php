<?php

namespace App\Services;

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

            // JSON->PHPの連想配列のitemsキーを取り出し、それがnullまたは未定義なら空配列を返す
            return $response->json()['items'] ?? [];

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
     * PlaylistItemを取得(例: ミックスリスト)
     * @param int $maxResults
     * @return array
     */
    public function fetchPlaylistItems(int $maxResults = 50): array {
                try {
            $response = Http::timeout(5)->get($this->baseUrl . 'playlistItems', [
                'part'       => 'snippet,statistics',
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

            // JSON->PHPの連想配列のitemsキーを取り出し、それがnullまたは未定義なら空配列を返す
            return $response->json()['items'] ?? [];

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
     * トレンド35件 + PlaylistItem15件 = 合計50件の動画のリスト
     * @return array
     */
    public function buildMixedDailyList(): array {
        try {
            $trending = $this->fetchTrendingMusic(50);
            $playlistItems = $this->fetchPlaylistItems(50);

            $trendingLimited=array_slice($trending, 0, 35);
            $playlistItemsLimited=array_slice($playlistItems, 0, 15);

            return array_merge($trendingLimited, $playlistItemsLimited);
        } catch (\Exception $error) {
            Log::error('YouTube API Merge Exception', [
                'message' => $error->getMessage(),
                'trace'   => $error->getTraceAsString(),
            ]);

            return [];
        }
    }
}
