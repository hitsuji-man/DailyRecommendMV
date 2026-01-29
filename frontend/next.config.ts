import type { NextConfig } from "next";

const isDev = process.env.NODE_ENV === "development";

// API 通信先（dev / prod 共通の変数名）
const apiOrigin = process.env.NEXT_PUBLIC_API_ORIGIN;

if (!apiOrigin) {
  throw new Error("NEXT_PUBLIC_API_ORIGIN is not defined");
}

// connect-src を配列で管理
const connectSrc = ["'self'", apiOrigin];

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
