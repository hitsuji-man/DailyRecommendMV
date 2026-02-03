"use client";

import Image from "next/image";
import { Recommendation } from "@/types/Recommendation";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import Link from "next/link";

type Props = {
  recommendation: Recommendation;
};

export default function RecommendationCard({ recommendation }: Props) {
  return (
    <div className="flex items-start gap-4 p-2">
      <Link href={`/videos/${recommendation.videoDbId}`}>
        <li key={recommendation.id} className="flex gap-3 items-start">
          {/* サムネイル */}
          <div className="flex-shrink-0">
            <Image
              src={recommendation.thumbnail.url}
              alt={recommendation.title}
              width={160}
              height={90}
              className="rounded-md mb-2"
            />
          </div>
          {/* タイトル + チャンネル名 + 視聴回数 + 投稿日時 */}
          <div className="flex-1 min-w-0">
            {/* タイトル */}
            <p className="font-large text-sm sm:text-base line-clamp-3">
              {recommendation.title}
            </p>
            {/* チャンネル名(投稿者) */}
            <p className="text-xs sm:text-sm text-gray-600 mt-1">
              投稿者: {recommendation.channelTitle}
            </p>
            {/* 視聴回数 + 投稿日時 */}
            <p className="text-xs sm:text-sm text-gray-500 mt-1">
              {recommendation.viewCount.toLocaleString()} 回視聴 ・{" "}
              {formatRelativeDate(recommendation.publishedAt)}
            </p>
          </div>
        </li>
      </Link>
    </div>
  );
}
