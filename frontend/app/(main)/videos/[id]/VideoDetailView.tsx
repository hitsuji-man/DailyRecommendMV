"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import { api } from "@/lib/api";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import VideoDescription from "@/components/VideoDescription";
import LikeButton from "@/components/LikeButton";
import { useAuthContext } from "@/context/AuthContext";

type Video = {
  id: number;
  videoId: string;
  title: string;
  description: string;
  channelTitle: string;
  thumbnail: {
    url: string;
    width: number;
    height: number;
  };
  publishedAt: string;
  viewCount: number;
  likeCount: number;
  sourceType: "trend" | "playlist";
  isFavorite: boolean;
  can_favorite: boolean;
};

type VideoResponse = {
  data: Video;
};

export default function VideoDetailView({ videoId }: { videoId: string }) {
  const [video, setVideo] = useState<Video | null>(null);
  const [loading, setLoading] = useState(true);

  const { loading: authLoading, authVersion } = useAuthContext();

  useEffect(() => {
    if (authLoading) return;

    const fetchVideo = async () => {
      try {
        const res = await api.get<VideoResponse>(`/videos/${videoId}`);
        setVideo(res.data.data);
      } catch (e) {
        console.error("Failed to fetch video", e);
      } finally {
        setLoading(false);
      }
    };

    fetchVideo();
  }, [videoId, authVersion, authLoading]);

  if (loading || !video) {
    return <div className="p-6 text-center">読み込み中...</div>;
  }

  return (
    <div className="p-6 max-w-4xl mx-auto">
      {/* 動画 */}
      <div className="aspect-video mb-4">
        <iframe
          className="w-full h-full rounded-lg"
          src={`https://www.youtube.com/embed/${video.videoId}`}
          title={video.title}
          allowFullScreen
        />
      </div>

      {/* タイトル */}
      <h1 className="text-xl font-semibold mb-2">{video.title}</h1>

      {/* 投稿者 + 視聴回数 + 投稿日 + いいねボタン */}
      <div className="flex items-center mb-3">
        {/* 左側: チャンネル情報 */}
        <div className="flex items-center gap-3">
          {/* チャンネルサムネイル(擬似) */}
          <Image
            src={video.thumbnail.url}
            alt={video.channelTitle}
            width={40}
            height={40}
            className="rounded-full"
          />
          {/* チャンネル名 + 視聴回数 + 投稿日時 */}
          <div className="flex flex-col">
            <p className="text-sm text-gray-700">
              投稿者: {video.channelTitle}
            </p>
            {/* 視聴回数 + 投稿日時 */}
            <p className="text-sm text-gray-500">
              {video.viewCount.toLocaleString()} 回視聴 ・{" "}
              {formatRelativeDate(video.publishedAt)}
            </p>
          </div>
        </div>

        {/* 右側：いいねボタン(ログイン時のみ) */}
        {video.can_favorite && (
          <div className="ml-4">
            <LikeButton videoId={video.id} initialLiked={video.isFavorite} />
          </div>
        )}
      </div>

      {/* 説明文 */}
      <VideoDescription description={video.description} />
    </div>
  );
}
