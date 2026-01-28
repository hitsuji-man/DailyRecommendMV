import { formatRelativeDate } from "@/lib/formatRelativeDate";
import { Video } from "@/types/video";

type Props = {
  video: Video;
};

export default function VideoCard({ video }: Props) {
  return (
    <div className="flex flex-col">
      {/* サムネイル */}
      <div className="aspect-video mb-2">
        <iframe
          className="h-full w-full rounded-md"
          src={`https://www.youtube.com/embed/${video.videoId}`}
          title={video.title}
          allowFullScreen
        />
      </div>

      {/* タイトル */}
      <p className="text-sm font-medium leading-snug line-clamp-2 mb-1">
        {video.title}
      </p>

      {/* チャンネル名 */}
      <p className="text-xs text-gray-600">投稿者: {video.channelTitle}</p>

      {/* 視聴回数 + 投稿日 */}
      <p className="text-xs text-gray-500">
        {video.viewCount.toLocaleString()} 回視聴 ・{" "}
        {formatRelativeDate(video.publishedAt)}
      </p>
    </div>
  );
}
