import { formatRelativeDate } from "@/lib/formatRelativeDate";
import { Video } from "@/types/video";
import Image from "next/image";
import Link from "next/link";

type Props = {
  video: Video;
};

export default function VideoCard({ video }: Props) {
  return (
    <Link href={`/videos/${video.id}`}>
      <div className="flex flex-col cursor-pointer hover:opacity-90">
        {/* サムネイル */}
        <Image
          src={video.thumbnail.url}
          alt={video.title}
          width={320}
          height={180}
          className="rounded-md mb-2"
        />

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
    </Link>
  );
}
