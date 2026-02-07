import Link from "next/link";

export default function NotFound() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center text-center px-4">
      <h1 className="text-2xl font-bold mb-2">ページが見つかりません</h1>

      <p className="text-gray-600 mb-6">
        URLが間違っているか、ページが削除された可能性があります。
        <br />
        404 Not Found
      </p>

      <Link
        href="/"
        className="px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800"
      >
        トップへ戻る
      </Link>
    </div>
  );
}
