"use client";

import { useState } from "react";
import { API_BASE_URL } from "@/lib/api";

type Props = {
  videoId: number;
  initialLiked: boolean;
};

export default function LikeButton({ videoId, initialLiked }: Props) {
  const [liked, setLiked] = useState(initialLiked);
  const [loading, setLoading] = useState(false);

  const handleClick = async () => {
    if (loading) return;

    setLoading(true);

    try {
      const res = await fetch(`${API_BASE_URL}/favorites/${videoId}`, {
        method: "POST",
        credentials: "include", // ← sanctumなら必須
      });

      if (!res.ok) {
        throw new Error("Failed to like");
      }

      // 成功したら即UI反映
      setLiked(true);
    } catch (e) {
      console.error(e);
      alert("いいねに失敗しました");
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleClick}
      className="flex items-center gap-1 text-sm"
      disabled={liked}
    >
      <span className={liked ? "text-red-500" : "text-gray-400"}>
        {liked ? "❤️" : "🤍"}
      </span>
      <span>{liked ? "いいね済み" : "いいね"}</span>
    </button>
  );
}
