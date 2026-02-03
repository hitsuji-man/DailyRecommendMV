import Image from "next/image";
import LikeButton from "@/components/LikeButton";
import { FavoriteVideo } from "@/types/FavoriteVideo";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import Link from "next/link";

type Props = {
  video: FavoriteVideo;
  onUnfavorite: (userId: number, videoDbId: number) => void;
};

export default function FavoriteVideoItem({ video, onUnfavorite }: Props) {
  return (
    <li className="flex gap-4 items-start py-1">
      {/* 左カラム：サムネイル + いいね */}
      <div className="flex flex-col items-start gap-2 shrink-0">
        <Link href={`/videos/${video.videoDbId}`}>
          <Image
            src={video.thumbnail.url}
            alt={video.title}
            width={160}
            height={90}
            className="rounded-md"
          />
        </Link>

        {/* サムネ直下のいいね */}
        <LikeButton
          videoId={video.videoDbId}
          initialLiked={video.isFavorite}
          onUnfavorite={() => onUnfavorite(video.userId, video.videoDbId)}
        />
      </div>

      {/* 右カラム：動画情報 */}
      <Link
        href={`/videos/${video.videoDbId}`}
        className="flex-1 min-w-0 space-y-1"
      >
        <p className="font-medium line-clamp-3 leading-snug">{video.title}</p>
        <p className="text-sm text-gray-600">{video.channelTitle}</p>
        <p className="text-sm text-gray-500">
          {video.viewCount.toLocaleString()} 回視聴 ・{" "}
          {formatRelativeDate(video.publishedAt)}
        </p>
      </Link>
    </li>
  );
}
