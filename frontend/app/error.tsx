"use client";

import Link from "next/link";
import { useEffect } from "react";

type Props = {
  error: Error & { digest?: string };
  reset: () => void;
};

export default function ErrorPage({ error, reset }: Props) {
  useEffect(() => {
    // 本番ではここでログ送信（Sentry等）
    console.error(error);
  }, [error]);

  return (
    <div className="min-h-screen flex flex-col items-center justify-center px-4 text-center">
      <h1 className="text-2xl font-bold mb-2">エラーが発生しました</h1>

      <p className="text-gray-600 mb-6">
        一時的な問題が発生した可能性があります。
        <br />
        時間をおいて再度お試しください。
      </p>

      <div className="flex gap-3">
        <button
          onClick={reset}
          className="px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800"
        >
          もう一度試す
        </button>

        <Link
          href="/"
          className="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100"
        >
          トップへ戻る
        </Link>
      </div>
    </div>
  );
}
