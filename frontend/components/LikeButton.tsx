"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { HeartIcon as HeartSolid } from "@heroicons/react/24/solid";
import { HeartIcon as HeartOutline } from "@heroicons/react/24/outline";

type Props = {
  videoId: number;
  initialLiked: boolean;
  onUnfavorite?: () => void;
};

export default function LikeButton({
  videoId,
  initialLiked,
  onUnfavorite,
}: Props) {
  const [liked, setLiked] = useState(initialLiked);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLiked(initialLiked);
  }, [initialLiked]);

  const handleClick = async () => {
    if (loading) return;

    setLoading(true);

    try {
      if (liked) {
        await api.delete(`/favorites/${videoId}`);
        setLiked(false);

        // 解除成功後に親に通知
        onUnfavorite?.();
      } else {
        await api.post(`/favorites/${videoId}`);
        setLiked(true);
      }
    } catch (e) {
      console.error(e);
      alert("いいねの更新に失敗しました");
      throw new Error("Failed to like");
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleClick}
      className="flex items-center gap-1 text-sm cursor-pointer group"
      disabled={loading}
    >
      {liked ? (
        <HeartSolid className="w-5 h-5 text-red-500 transition-transform group-hover:scale-110" />
      ) : (
        <HeartOutline className="w-5 h-5 text-gray-800 transition-transform group-hover:scale-110" />
      )}
      <span>{liked ? "いいね済み" : "いいね"}</span>
    </button>
  );
}
