import type { NextConfig } from "next";

const isDev = process.env.NODE_ENV === "development";

// API 通信先（dev / prod 共通の変数名）
const apiOrigin = process.env.NEXT_PUBLIC_API_ORIGIN;

if (!apiOrigin) {
  throw new Error("NEXT_PUBLIC_API_ORIGIN is not defined");
}
// 許可先が複数ある場合の対処
const extraConnectSrc =
  process.env.NEXT_PUBLIC_EXTRA_CONNECT_SRC?.split(",") ?? [];

// connect-src を配列で管理
const connectSrc = ["'self'", apiOrigin, ...extraConnectSrc];

const csp = `
  default-src 'self';
  script-src 'self' 'unsafe-inline' ${isDev ? "'unsafe-eval'" : ""};
  style-src 'self' 'unsafe-inline';
  img-src 'self' https://i.ytimg.com data:;
  font-src 'self';
  connect-src ${connectSrc.join(" ")};
  frame-src 'self' https://www.youtube.com;
  object-src 'none';
  base-uri 'self';
  frame-ancestors 'none';
`;

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "i.ytimg.com",
        pathname: "/vi/**",
      },
    ],
  },

  async headers() {
    return [
      {
        source: "/(.*)",
        headers: [
          {
            key: "Content-Security-Policy",
            value: csp.replace(/\n/g, ""),
          },
        ],
      },
    ];
  },
};

export default nextConfig;
