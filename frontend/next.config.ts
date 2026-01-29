import type { NextConfig } from "next";

const isDev = process.env.NODE_ENV === "development";

const csp = `
  default-src 'self';
  script-src 'self' 'unsafe-inline' ${isDev ? "'unsafe-eval'" : ""};
  style-src 'self' 'unsafe-inline';
  img-src 'self' https://i.ytimg.com data:;
  font-src 'self';
  connect-src 'self' http://localhost:8000;
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
