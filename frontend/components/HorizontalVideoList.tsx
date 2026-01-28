"use client";

import { useRef } from "react";
import VideoCard from "@/components/VideoCard";
import { Video } from "@/types/video";

type Props = {
  videos: Video[];
};

const CARD_WIDTH = 320; // VideoCard の幅
const GAP = 16; // gap-4 = 16px

export default function HorizontalVideoList({ videos }: Props) {
  const scrollRef = useRef<HTMLDivElement>(null);

  const scrollLeft = () => {
    scrollRef.current?.scrollBy({
      left: -(CARD_WIDTH + GAP),
      behavior: "smooth",
    });
  };

  const scrollRight = () => {
    scrollRef.current?.scrollBy({
      left: CARD_WIDTH + GAP,
      behavior: "smooth",
    });
  };

  return (
    <div className="relative">
      {/* 左矢印 */}
      <button
        onClick={scrollLeft}
        className="absolute left-0 top-1/2 -translate-y-1/2 z-10
                   bg-white/80 hover:bg-white shadow rounded-full
                   w-10 h-10 flex items-center justify-center"
        aria-label="Scroll left"
      >
        ◀
      </button>

      {/* 右矢印 */}
      <button
        onClick={scrollRight}
        className="absolute right-0 top-1/2 -translate-y-1/2 z-10
                   bg-white/80 hover:bg-white shadow rounded-full
                   w-10 h-10 flex items-center justify-center"
        aria-label="Scroll right"
      >
        ▶
      </button>

      {/* 横スクロール領域 */}
      <div ref={scrollRef} className="overflow-x-auto snap-x snap-mandatory">
        <ul className="flex gap-4 w-max px-12">
          {videos.map((video) => (
            <li key={video.id} className="w-[320px] flex-shrink-0 snap-start">
              <VideoCard video={video} />
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
