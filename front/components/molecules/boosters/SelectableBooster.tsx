"use client";

import type { Booster } from "@/app/types/booster";
import InteractiveBooster, {
  BoosterMotionType,
} from "@/components/molecules/boosters/InteractiveBooster";
import { useEffect, useRef, useState } from "react";

type SelectableBoosterProps = {
  booster: Booster;
  index: number;
  frontBoosterId: string;
  isPreviewOpen: boolean;
  isSmallScreen: boolean;
  onRotateTo: (index: number) => void;
  onPreviewChange: (open: boolean) => void;
};

const TRANSITION_DURATION = 700;
export default function SelectableBooster({
  booster,
  index,
  frontBoosterId,
  isPreviewOpen,
  isSmallScreen,
  onRotateTo,
  onPreviewChange,
}: SelectableBoosterProps) {
  const isFrontBooster = booster.id === frontBoosterId;
  const isFrontPreviewOpen = isPreviewOpen && isFrontBooster;
  const brightness = isFrontBooster ? 100 : isPreviewOpen ? 60 : 80;
  const wasFrontPreviewOpenRef = useRef(isFrontPreviewOpen);
  const [isAnimatingPreview, setIsAnimatingPreview] = useState(false);

  useEffect(() => {
    if (wasFrontPreviewOpenRef.current !== isFrontPreviewOpen) {
      setIsAnimatingPreview(true);

      const timer = window.setTimeout(() => {
        setIsAnimatingPreview(false);
      }, TRANSITION_DURATION);

      wasFrontPreviewOpenRef.current = isFrontPreviewOpen;
      return () => window.clearTimeout(timer);
    }

    wasFrontPreviewOpenRef.current = isFrontPreviewOpen;
    return undefined;
  }, [isFrontPreviewOpen]);

  const handleBoosterClick = () => {
    if (isPreviewOpen) {
      return;
    }

    if (!isFrontBooster) {
      onRotateTo(index);
      return;
    }

    onPreviewChange(true);
  };

  return (
    <div>
      <div
        className="relative transition-transform duration-700 ease-in-out"
        style={{
          transform: isFrontPreviewOpen
            ? isSmallScreen
              ? "translateY(-110px) rotate(1080deg)"
              : "translateX(-220px) rotate(1080deg)"
            : "translate(0, 0) rotate(0deg)",
        }}
      >
        <InteractiveBooster
          boosterType={booster.boosterType}
          onClick={handleBoosterClick}
          showGlare={isFrontBooster}
          brightness={brightness}
          isCursorAvailable={!isSmallScreen}
          disableShadow={isFrontPreviewOpen || isAnimatingPreview}
          motionType={
            isFrontPreviewOpen
              ? BoosterMotionType.SHAKE
              : isAnimatingPreview
                ? BoosterMotionType.NONE
                : BoosterMotionType.FLOAT
          }
          className={isFrontPreviewOpen ? "w-booster-xl" : ""}
        />

        <p
          className={`absolute -bottom-14 left-1/2 -translate-x-1/2 text-white text-sm text-center whitespace-nowrap transition-opacity duration-200 ${isFrontPreviewOpen && !isAnimatingPreview ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        >
          Cliquer une seconde fois pour confirmer votre choix
        </p>
      </div>

      <div
        className={`absolute transition-opacity duration-700 ease-in-out
          ${
            isSmallScreen
              ? "left-1/2 -translate-x-1/2 top-[calc(50%+190px)] w-[min(90vw,24rem)]"
              : "left-12.5 top-1/2 -translate-y-1/2 w-[min(40vw,26rem)]"
          } ${isFrontPreviewOpen ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        onClick={(event) => event.stopPropagation()}
      >
        <div
          className={`border-2 border-white bg-primary text-white rounded-xl p-4 transition-transform duration-700 ease-in-out ${
            isFrontPreviewOpen ? "translate-x-0" : "translate-x-[120vw]"
          }`}
        >
          Texte a venir
        </div>
      </div>
    </div>
  );
}
