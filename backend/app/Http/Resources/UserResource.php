<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,

            // 派生情報（DBに持たない）
            'is_guest' => $this->email === null,

            // 表示専用の補助情報（必要なら）
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
