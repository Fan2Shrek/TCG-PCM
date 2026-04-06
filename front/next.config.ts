import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  images: {
    unoptimized: true, // maybe change
  },
  typescript: {
    ignoreBuildErrors: true,
  }
};

export default nextConfig;
