<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'id'           => $this->id ?? null,
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
            'isFavorite'   => (bool) ($this->is_favorite ?? false),
            'can_favorite' => (bool) $request->user() ?? null,
        ];
    }
}
