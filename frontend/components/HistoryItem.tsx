import Image from "next/image";
import { History } from "@/types/History";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import Link from "next/link";

type Props = {
  history: History;
  onDelete: (id: number) => void;
};

export default function HistoryItem({ history, onDelete }: Props) {
  return (
    <div className="relative flex items-start gap-4 p-2">
      {/* 左側：クリック可能領域 */}
      <Link
        href={`/videos/${history.videoDbId}`}
        className="flex gap-4 flex-1 min-w-0"
      >
        {/* サムネイル */}
        <Image
          src={history.thumbnail.url}
          alt={history.title}
          width={160}
          height={90}
          className="rounded-md flex-shrink-0"
        />

        {/* 動画情報（サムネ右側に縦並び） */}
        <div className="flex flex-col gap-1 overflow-hidden">
          <p className="font-medium line-clamp-3">{history.title}</p>
          <p className="text-sm text-gray-600">{history.channelTitle}</p>
          <p className="text-sm text-gray-500">
            {history.viewCount.toLocaleString()} 回視聴
          </p>
          <p className="text-xs text-gray-400">
            視聴日：{formatRelativeDate(history.viewedAt)}
          </p>
        </div>
      </Link>

      {/* 右上：削除ボタン（クリック領域外） */}
      <button
        onClick={() => onDelete(history.id)}
        className="absolute top-2 right-2 text-3xl text-gray-400 hover:text-gray-700"
        aria-label="視聴履歴を削除"
      >
        ×
      </button>
    </div>
  );
}
