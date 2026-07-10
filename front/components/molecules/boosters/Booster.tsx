"use client";

import type { CSSProperties } from "react";
import Image from "@/components/atoms/Image";
import { BoosterType } from "@/constants/booster";

type BoosterProps = {
  boosterType: BoosterType;
  style?: CSSProperties;
  className?: string;
  showGlare?: boolean;
  brightness?: number;
};

const getBoosterImageSources = (boosterType: BoosterType) => {
  const basePath = `/booster/${boosterType}`;

  return {
    back: `${basePath}_back.png`,
    bottom: `${basePath}_bottom.png`,
    top: `${basePath}_top.png`,
  };
};

export default function Booster({
  boosterType,
  style,
  className,
  showGlare = false,
  brightness = 100,
}: BoosterProps) {
  const srcs = getBoosterImageSources(boosterType);

  return (
    <div
      className={`relative aspect-4/7 w-booster-lg transform-3d transform-gpu will-change-transform user-select-none transition-[width] duration-700 ease-in-out ${className ?? ""}`}
      style={style}
    >
      <div className="inset-0 backface-hidden">
        <Image
          src={srcs.bottom}
          alt={`${boosterType} booster bottom`}
          fill
          className="object-cover pb-px"
          style={{ filter: `brightness(${brightness}%)` }}
        />
        <Image
          src={srcs.top}
          alt={`${boosterType} booster top`}
          fill
          className="object-cover"
          style={{ filter: `brightness(${brightness}%)` }}
        />

        <div className="pointer-events-none absolute inset-0 overflow-hidden">
          <div
            className={`h-full w-full bg-glare-effect mix-blend-screen transition-opacity duration-300  ${showGlare ? "opacity-100" : "opacity-0"}`}
            style={{ transform: "translate(0%, 0%)" }}
          />
        </div>
      </div>
    </div>
  );
}
