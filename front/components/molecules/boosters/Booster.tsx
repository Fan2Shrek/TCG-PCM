"use client";

import type { CSSProperties } from "react";
import Image from "@/components/atoms/Image";
import { BoosterType } from "@/constants/booster";
import { BoosterOpeningPhase } from "@/lib/boosterOpening/phases";

type BoosterProps = {
  boosterType: BoosterType;
  style?: CSSProperties;
  className?: string;
  showGlare?: boolean;
  brightness?: number;
  openingPhase?: BoosterOpeningPhase;
  shotCardCount?: number;
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
  openingPhase,
  shotCardCount = 0,
}: BoosterProps) {
  const srcs = getBoosterImageSources(boosterType);
  const isDropTop = openingPhase === BoosterOpeningPhase.OPENING_DROP_TOP;
  const isShootBackCards =
    openingPhase === BoosterOpeningPhase.OPENING_SHOOT_BACK_CARDS;
  const isDropEmptyBooster =
    openingPhase === BoosterOpeningPhase.OPENING_DROP_EMPTY_BOOSTER;
  const hideTopLayerAfterDrop = isShootBackCards || isDropEmptyBooster;
  const cardsToShootCount = Math.max(0, shotCardCount);

  return (
    <div
      className={`relative aspect-4/7 w-booster-lg transform-3d transform-gpu will-change-transform user-select-none transition-[width] duration-700 ease-in-out ${className ?? ""}`}
      style={style}
    >
      {isShootBackCards ? (
        <div className="absolute inset-0 z-0">
          {Array.from({ length: cardsToShootCount }).map((_, index) => (
            <div
              key={`booster-shot-card-${index}`}
              className="absolute left-1/2 top-1/2 w-card-lg aspect-card -translate-x-1/2 -translate-y-1/2 animate-booster-back-card-shoot"
              style={{
                ["--card-index" as string]: String(index),
                ["--card-offset" as string]: String(
                  index - (cardsToShootCount - 1) / 2,
                ),
                ["--card-launch-x" as string]: "3.25rem",
              }}
            >
              <Image
                src="/card/card_back.png"
                alt="Back card"
                fill
                className="object-cover"
              />
            </div>
          ))}
        </div>
      ) : null}

      <div className="inset-0 backface-hidden">
        <Image
          src={srcs.bottom}
          alt={`${boosterType} booster bottom`}
          fill
          className={`object-cover pb-px ${
            isDropEmptyBooster ? "animate-booster-empty-drop" : ""
          } z-10`}
          style={{ filter: `brightness(${brightness}%)` }}
        />
        <Image
          src={srcs.top}
          alt={`${boosterType} booster top`}
          fill
          className={`object-cover ${
            isDropTop
              ? "animate-booster-cap-drop"
              : hideTopLayerAfterDrop
                ? "opacity-0"
                : ""
          } z-20`}
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
