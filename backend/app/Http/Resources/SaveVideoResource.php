<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaveVideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'youtube_id'      => $this->videoId ?? $this['videoId']
                                ?? $this->resource['id']
                                ?? $this->resource['videoId'] ?? null,
            'title'        => $this->title
                                ?? $this['title']
                                ?? $this->resource['snippet']['title'] ?? null,
            'description'  => $this->description
                                ?? $this['description']
                                ?? $this->resource['snippet']['description'] ?? null,
            'channel_id'    => $this->channelId
                                ?? $this['channelId']
                                ?? $this->resource['snippet']['channelId'] ?? null,
            'channel_title' => $this->channelTitle
                                ?? $this['channelTitle']
                                ?? $this->resource['snippet']['channelTitle'] ?? null,

            'thumbnail_url'    => $this->thumbnail
                                ?? $this['thumbnail']['url']
                                ?? $this->resource['snippet']['thumbnails']['high']['url'] ?? null,

            'published_at'  => $this->publishedAt
                                ?? $this['publishedAt']
                                ?? $this->resource['snippet']['publishedAt'] ?? null,

            'view_count'    => $this->viewCount
                                ?? $this['viewCount']
                                ?? $this->resource['statistics']['viewCount'] ?? null,

            'like_count'    => $this->likeCount
                                ?? $this['likeCount']
                                ?? $this->resource['statistics']['likeCount'] ?? null,

            // 'trend' or 'playlist'
            'source_type'   => $this->sourceType
                                ?? $this['sourceType']
                                ?? ($this->resource['sourceType'] ?? null),
        ];
    }
}
