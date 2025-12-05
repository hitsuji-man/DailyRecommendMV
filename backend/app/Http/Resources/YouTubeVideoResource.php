<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class YouTubeVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'videoId'      => $this->videoId ?? $this['videoId']
                                ?? $this->resource['id']
                                ?? $this->resource['videoId'] ?? null,
            'title'        => $this->title
                                ?? $this['title']
                                ?? $this->resource['snippet']['title'] ?? null,
            'description'  => $this->description
                                ?? $this['description']
                                ?? $this->resource['snippet']['description'] ?? null,
            'channelId'    => $this->channelId
                                ?? $this['channelId']
                                ?? $this->resource['snippet']['channelId'] ?? null,
            'channelTitle' => $this->channelTitle
                                ?? $this['channelTitle']
                                ?? $this->resource['snippet']['channelTitle'] ?? null,

            'thumbnail'    => $this->thumbnail
                                ?? $this['thumbnail']
                                ?? $this->resource['snippet']['thumbnails']['high'] ?? null,

            'publishedAt'  => $this->publishedAt
                                ?? $this['publishedAt']
                                ?? $this->resource['snippet']['publishedAt'] ?? null,

            'viewCount'    => $this->viewCount
                                ?? $this['viewCount']
                                ?? $this->resource['statistics']['viewCount'] ?? null,

            'likeCount'    => $this->likeCount
                                ?? $this['likeCount']
                                ?? $this->resource['statistics']['likeCount'] ?? null,

            // 'trend' or 'playlist'
            'sourceType'   => $this->sourceType
                                ?? $this['sourceType']
                                ?? ($this->resource['sourceType'] ?? null),
        ];
    }
}
