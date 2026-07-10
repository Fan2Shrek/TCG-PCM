"use client";

import type { CSSProperties } from "react";
import Image from "@/components/atoms/Image";
import { BoosterType } from "@/constants/booster";

type BoosterProps = {
  boosterType: BoosterType;
  style?: CSSProperties;
  className?: string;
  showGlare?: boolean;
  dimmed?: boolean;
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
  dimmed = false,
}: BoosterProps) {
  const srcs = getBoosterImageSources(boosterType);

  return (
    <div
      className={`relative aspect-4/7 w-booster-lg transform-3d transform-gpu will-change-transform user-select-none ${className ?? ""}`}
      style={style}
    >
      <div className="inset-0 backface-hidden">
        <Image
          src={srcs.bottom}
          alt={`${boosterType} booster bottom`}
          fill
          className={`object-cover pb-px ${dimmed ? "brightness-80" : "brightness-100"}`}
        />
        <Image
          src={srcs.top}
          alt={`${boosterType} booster top`}
          fill
          className={`object-cover ${dimmed ? "brightness-80" : "brightness-100"}`}
        />

        <div className="pointer-events-none absolute inset-0 overflow-hidden">
          <div
            className={`h-full w-full bg-glare-effect mix-blend-screen transition-opacity duration-300  ${showGlare ? "opacity-100" : "opacity-0"}`}
            style={{ transform: "translate(0%, 0%)" }}
          />
        </div>
      </div>
      {/* <div className="absolute inset-0 backface-hidden rotate-y-180 pointer-events-none select-none overflow-hidden rounded-2xl">
        <Image
          src={srcs.back}
          alt={`${boosterType} booster back`}
          fill
          className="object-cover"
        />
      </div> */}
    </div>
  );
}
