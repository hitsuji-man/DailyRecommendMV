<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * DB:Videosテーブルから取得したresourceをarrayに変換
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'videoId'      => $this->youtube_id ?? null,
            'title'        => $this->title ?? null,
            'description'  => $this->description ?? null,
            'channelId'    => $this->channel_id ?? null,
            'channelTitle' => $this->channel_title ?? null,
            'thumbnail'    => $this->thumbnail ?? null,
            'publishedAt'  => $this->published_at ?? null,
            'viewCount'    => $this->view_count ?? null,
            'likeCount'    => $this->like_count ?? null,
            'sourceType'   => $this->source_type ?? null,
        ];
    }
}
