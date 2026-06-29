"use client";

import { useState, useEffect, useRef } from "react";
import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import PileTooltip from "@/components/atoms/PileTooltip";
import { CardSize } from "@/constants/card";
import { GAMEBOARD_ANIMATION_DURATION, GAMEBOARD_ANIMATION_TIMING } from "@/constants/gameArea";
import { emitter } from "@/lib/eventBus";

type DrawPileProps = {
  numCards: number;
  className?: string;
  mirrored?: boolean;
  isCardDragged?: boolean;
};

export default function DrawPile({ numCards, className = "", mirrored = false, isCardDragged = false }: DrawPileProps) {
  const [showTooltip, setShowTooltip] = useState(false);
  const [animatingCardIndex, setAnimatingCardIndex] = useState<number | null>(null);
  const animationTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const shadowOffsetX = mirrored ? -numCards * 2 + 6 : numCards * 2 - 6;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

  useEffect(() => {
    const handleCardDrawn = () => {
      if (mirrored) return;

      setAnimatingCardIndex(0);

      animationTimerRef.current = setTimeout(() => {
        emitter.emit("animation:card-draw-complete");
        setAnimatingCardIndex(null);
      }, 500);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    return () => {
      if (animationTimerRef.current) clearTimeout(animationTimerRef.current);
      emitter.off("game:card-drawn", handleCardDrawn);
    };
  }, [mirrored]);

  return (
    <div
      className={`relative w-card-md aspect-card z-2 ${className}`}
      onMouseEnter={() => setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      style={{
        transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
      }}
    >
      {Array.from({ length: numCards }).map((_, i) => {
        const offsetX = mirrored ? -i : i;
        const offsetY = isCardDragged ? 0 : -i * 1.3;
        const isAnimating = i === animatingCardIndex;
        const animatingOffsetY = offsetY + 400;

        return (
          <div
            key={i}
            className='absolute'
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${isAnimating ? animatingOffsetY : offsetY}px)`,
              zIndex: i,
              transition: isAnimating
                ? "transform 500ms ease-in"
                : `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}, box-shadow ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
              opacity: isAnimating ? 0 : 1,
              ...(i === 0 && { boxShadow: `${shadowOffsetX}px 0 ${shadow}` }),
            }}
          >
            <DummyFaceDownCard size={CardSize.MD} />
          </div>
        );
      })}
      <PileTooltip isVisible={showTooltip} count={numCards} mirrored={mirrored} label='cards left' />
    </div>
  );
}
