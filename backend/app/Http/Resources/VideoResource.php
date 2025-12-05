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
            'videoId'      => $this->youtube_id,
            'title'        => $this->title,
            'description'  => $this->description,
            'channelId'    => $this->channel_id,
            'channelTitle' => $this->channel_title,
            'thumbnail'    => $this->thumbnail,
            'publishedAt'  => $this->published_at,
            'viewCount'    => $this->view_count,
            'likeCount'    => $this->like_count,
            'sourceType'   => $this->source_type,
        ];

    }
}
