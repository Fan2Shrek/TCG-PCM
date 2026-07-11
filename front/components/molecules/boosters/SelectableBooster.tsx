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
import { createPortal } from "react-dom";

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
  const [portalRect, setPortalRect] = useState<{
    top: number;
    left: number;
    width: number;
    height: number;
    baseWidth: number;
    baseHeight: number;
    scaleX: number;
    scaleY: number;
  } | null>(null);
  const boosterWrapperRef = useRef<HTMLDivElement | null>(null);

  const shouldUseBoosterPortal =
    isFrontBooster &&
    openingPhase === BoosterOpeningPhase.OPENING_SHOOT_BACK_CARDS;

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

  useEffect(() => {
    if (!shouldUseBoosterPortal) {
      setPortalRect(null);
      return;
    }

    let frameId = 0;

    const updateRect = () => {
      const wrapper = boosterWrapperRef.current;

      if (!wrapper) {
        frameId = window.requestAnimationFrame(updateRect);
        return;
      }

      const rect = wrapper.getBoundingClientRect();
      const baseWidth = wrapper.offsetWidth || rect.width;
      const baseHeight = wrapper.offsetHeight || rect.height;
      const scaleX = baseWidth > 0 ? rect.width / baseWidth : 1;
      const scaleY = baseHeight > 0 ? rect.height / baseHeight : 1;

      setPortalRect({
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height,
        baseWidth,
        baseHeight,
        scaleX,
        scaleY,
      });

      frameId = window.requestAnimationFrame(updateRect);
    };

    frameId = window.requestAnimationFrame(updateRect);

    return () => {
      window.cancelAnimationFrame(frameId);
    };
  }, [shouldUseBoosterPortal]);

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

  const boosterMotionType = shouldStayInPreviewPosition
    ? BoosterMotionType.SHAKE
    : isAnimatingPreview
      ? BoosterMotionType.NONE
      : BoosterMotionType.FLOAT;

  const boosterClassName = shouldStayInPreviewPosition ? "w-booster-xl" : "";
  const isPortalMounted = shouldUseBoosterPortal && portalRect !== null;

  return (
    <div className={isFrontRevealActive ? "invisible pointer-events-none" : ""}>
      <div
        ref={boosterWrapperRef}
        className="relative transition-transform duration-700 ease-in-out"
        style={{
          transform: shouldStayInPreviewPosition
            ? isSmallScreen
              ? "translateY(-110px) rotate(1080deg)"
              : "translateX(-160px) rotate(1080deg)"
            : "translate(0, 0) rotate(0deg)",
        }}
      >
        <div
          className={isPortalMounted ? "invisible pointer-events-none" : ""}
          aria-hidden={isPortalMounted}
        >
          <InteractiveBooster
            boosterType={booster.boosterType}
            onClick={handleBoosterClick}
            brightness={brightness}
            openingPhase={isFrontBooster ? openingPhase : undefined}
            shotCardCount={isFrontBooster ? shotCardCount : 0}
            isCursorAvailable={!isSmallScreen}
            disableShadow={shouldStayInPreviewPosition || isAnimatingPreview}
            motionType={boosterMotionType}
            className={boosterClassName}
          />
        </div>

        <p
          className={`absolute -bottom-14 left-1/2 -translate-x-1/2 text-white text-sm text-center whitespace-nowrap transition-opacity duration-200 ${isFrontPreviewOpen && !isAnimatingPreview ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        >
          Cliquer une seconde fois pour confirmer votre choix
        </p>
      </div>

      {isPortalMounted && portalRect
        ? createPortal(
            <div
              className="pointer-events-none fixed z-60"
              style={{
                top: portalRect.top,
                left: portalRect.left,
                width: portalRect.baseWidth,
                height: portalRect.baseHeight,
                transform: `scale(${portalRect.scaleX}, ${portalRect.scaleY})`,
                transformOrigin: "top left",
              }}
            >
              <div className="pointer-events-auto">
                <InteractiveBooster
                  boosterType={booster.boosterType}
                  onClick={handleBoosterClick}
                  brightness={brightness}
                  openingPhase={isFrontBooster ? openingPhase : undefined}
                  shotCardCount={isFrontBooster ? shotCardCount : 0}
                  isCursorAvailable={!isSmallScreen}
                  disableShadow={
                    shouldStayInPreviewPosition || isAnimatingPreview
                  }
                  motionType={boosterMotionType}
                  className={boosterClassName}
                />
              </div>
            </div>,
            document.body,
          )
        : null}

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
