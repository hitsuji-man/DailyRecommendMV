"use client";

import { useState } from "react";

type Props = {
  description: string;
};

export default function VideoDescription({ description }: Props) {
  const [expanded, setExpanded] = useState(false);

  return (
    <div className="mt-4">
      <p
        className={`text-sm text-gray-700 whitespace-pre-line transition-all ${
          expanded ? "" : "line-clamp-3"
        }`}
      >
        {description}
      </p>

      {/* 切り替えボタン */}
      {description.length > 0 && (
        <button
          onClick={() => setExpanded((prev) => !prev)}
          className="mt-1 text-sm font-medium text-gray-600 hover:text-gray-900"
        >
          {expanded ? "折りたたむ" : "もっと見る"}
        </button>
      )}
    </div>
  );
}
