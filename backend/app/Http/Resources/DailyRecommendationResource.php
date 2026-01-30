<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DailyRecommendationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id
                                ?? $this->video->id ?? null,
            'videoId'         => $this->youtube_id
                                ?? $this->video->youtube_id ?? null,
            'title'           => $this->title
                                ?? $this->video->title ?? null,
            'description'     => $this->description
                                ?? $this->video->description ?? null,
            'channelId'       => $this->channel_id
                                ?? $this->video->channel_id ?? null,
            'channelTitle'    => $this->channel_title
                                ?? $this->video->channel_title ?? null,
            'thumbnail'       => $this->thumbnail
                                ?? $this->video->thumbnail ?? null,
            'publishedAt'     => $this->published_at
                                ?? $this->video->published_at ?? null,
            'viewCount'       => $this->view_count
                                ?? $this->video->view_count ?? null,
            'likeCount'       => $this->like_count
                                ?? $this->video->like_count ?? null,
            'sourceType'      => $this->source_type
                                ?? $this->video->source_type ?? null,
            'recommendDate'  => $this->recommend_date ?? null,
            'isFavorite'   => (bool) ($this->is_favorite ?? false),
            'can_favorite' => (bool) $request->user() ?? null,
        ];
    }
}
