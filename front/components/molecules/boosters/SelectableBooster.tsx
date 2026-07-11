"use client";

import type { Booster } from "@/app/types/booster";
import type { BasicCard } from "@/lib/cards/types/card";
import BoosterPreviewPanel from "./BoosterPreviewPanel";
import InteractiveBooster, {
  BoosterMotionType,
} from "@/components/molecules/boosters/InteractiveBooster";
import {
  BoosterOpeningPhase,
  isOpeningAnimationPhase,
  isRevealPhase,
} from "@/lib/boosterOpening/phases";
import { useEffect, useRef, useState } from "react";

type SelectableBoosterProps = {
  booster: Booster;
  index: number;
  frontBoosterId: string;
  isPreviewOpen: boolean;
  openingPhase: BoosterOpeningPhase;
  shotCardCount: number;
  isSmallScreen: boolean;
  previewCards: BasicCard[];
  previewTitle: string;
  ownedCards: number;
  totalCards: number;
  isPreviewLoading: boolean;
  onRotateTo: (index: number) => void;
  onPreviewChange: (open: boolean) => void;
  onConfirmOpen: () => void;
};

const TRANSITION_DURATION = 700;
export default function SelectableBooster({
  booster,
  index,
  frontBoosterId,
  isPreviewOpen,
  openingPhase,
  shotCardCount,
  isSmallScreen,
  previewCards,
  previewTitle,
  ownedCards,
  totalCards,
  isPreviewLoading,
  onRotateTo,
  onPreviewChange,
  onConfirmOpen,
}: SelectableBoosterProps) {
  const isFrontBooster = booster.id === frontBoosterId;
  const isFrontPreviewOpen = isPreviewOpen && isFrontBooster;
  const isFrontOpeningAnimating =
    isFrontBooster && isOpeningAnimationPhase(openingPhase);
  const isFrontRevealActive = isFrontBooster && isRevealPhase(openingPhase);
  const shouldStayInPreviewPosition =
    isFrontPreviewOpen || isFrontOpeningAnimating;
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
      if (isFrontBooster) {
        onConfirmOpen();
      }

      return;
    }

    if (!isFrontBooster) {
      onRotateTo(index);
      return;
    }

    onPreviewChange(true);
  };

  return (
    <div className={isFrontRevealActive ? "invisible pointer-events-none" : ""}>
      <div
        className="relative transition-transform duration-700 ease-in-out"
        style={{
          transform: shouldStayInPreviewPosition
            ? isSmallScreen
              ? "translateY(-110px) rotate(1080deg)"
              : "translateX(-160px) rotate(1080deg)"
            : "translate(0, 0) rotate(0deg)",
        }}
      >
        <InteractiveBooster
          boosterType={booster.boosterType}
          onClick={handleBoosterClick}
          showGlare={isFrontBooster}
          brightness={brightness}
          openingPhase={isFrontBooster ? openingPhase : undefined}
          shotCardCount={isFrontBooster ? shotCardCount : 0}
          isCursorAvailable={!isSmallScreen}
          disableShadow={shouldStayInPreviewPosition || isAnimatingPreview}
          motionType={
            shouldStayInPreviewPosition
              ? BoosterMotionType.SHAKE
              : isAnimatingPreview
                ? BoosterMotionType.NONE
                : BoosterMotionType.FLOAT
          }
          className={shouldStayInPreviewPosition ? "w-booster-xl" : ""}
        />

        <p
          className={`absolute -bottom-14 left-1/2 -translate-x-1/2 text-white text-sm text-center whitespace-nowrap transition-opacity duration-200 ${isFrontPreviewOpen && !isAnimatingPreview ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        >
          Cliquer une seconde fois pour confirmer votre choix
        </p>
      </div>

      <BoosterPreviewPanel
        isVisible={isFrontPreviewOpen}
        isSmallScreen={isSmallScreen}
        title={previewTitle}
        cards={previewCards}
        ownedCards={ownedCards}
        totalCards={totalCards}
        isLoading={isPreviewLoading}
        shouldRenderCards={!isAnimatingPreview}
        onBack={() => onPreviewChange(false)}
      />
    </div>
  );
}
