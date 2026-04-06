import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    unoptimized: true, // maybe change
  },
  typescript: {
    ignoreBuildErrors: true,
  }
};

export default nextConfig;
