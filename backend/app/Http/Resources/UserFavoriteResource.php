<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userId'          => $this->user_id ?? null,
            'videoId'         => $this->video->youtube_id ?? null,
            'title'           => $this->video->title ?? null,
            'description'     => $this->video->description ?? null,
            'channelId'       => $this->video->channel_id ?? null,
            'channelTitle'    => $this->video->channel_title ?? null,
            'thumbnail'       => $this->video->thumbnail ?? null,
            'publishedAt'     => $this->video->published_at ?? null,
            'viewCount'       => $this->video->view_count ?? null,
            'likeCount'       => $this->video->like_count ?? null,
            'sourceType'      => $this->video->source_type ?? null,
            'isFavorite'      => (bool) ($this->video->is_favorite ?? false),
        ];
    }
}
