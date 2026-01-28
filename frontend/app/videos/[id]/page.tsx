import { API_BASE_URL } from "@/lib/api";
import Image from "next/image";
import { formatRelativeDate } from "@/lib/formatRelativeDate";

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
};

type VideoResponse = {
  data: Video;
};

async function getVideo(id: string): Promise<Video> {
  const res = await fetch(`${API_BASE_URL}/api/v1/videos/${id}`, {
    cache: "no-store",
  });

  if (!res.ok) {
    console.error("Fetch failed:", res.status, res.statusText);
    throw new Error(`Failed to fetch video: ${res.status}`);
  }

  const json: VideoResponse = await res.json();
  return json.data;
}

export default async function VideoDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const video = await getVideo(id);

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

      {/* チャンネル情報 */}
      <div className="flex items-center gap-3 mb-2">
        <Image
          src={video.thumbnail.url}
          alt={video.channelTitle}
          width={40}
          height={40}
          className="rounded-full"
        />
        <p className="text-sm text-gray-700">投稿者: {video.channelTitle}</p>
      </div>

      {/* 視聴回数・投稿日 */}
      <p className="text-sm text-gray-500 mb-4">
        {video.viewCount.toLocaleString()} 回視聴 ・{" "}
        {formatRelativeDate(video.publishedAt)}
      </p>

      {/* 説明文 */}
      <p className="text-sm whitespace-pre-line text-gray-700">
        {video.description}
      </p>
    </div>
  );
}
