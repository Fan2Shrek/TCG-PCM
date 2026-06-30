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
  playerId?: string;
};

const CARD_DRAW_ANIMATION_TIME = 200;

export default function DrawPile({ numCards, className = "", mirrored = false, isCardDragged = false, playerId }: DrawPileProps) {
  const [showTooltip, setShowTooltip] = useState(false);
  const [displayNumCards, setDisplayNumCards] = useState(numCards);
  const [animatingIndex, setAnimatingIndex] = useState<number | null>(null);
  const animationTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const shadowOffsetX = mirrored ? -displayNumCards * 2 + 6 : displayNumCards * 2 - 6;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

  useEffect(() => {
    const handleCardDrawn = (event: { playerId: string }) => {
      if (mirrored) return;
      if (playerId && event.playerId !== playerId) return;

      setAnimatingIndex(displayNumCards - 1);

      animationTimerRef.current = setTimeout(() => {
        emitter.emit("animation:card-draw-complete");
        setAnimatingIndex(null);
        setDisplayNumCards((prev) => prev - 1);
      }, CARD_DRAW_ANIMATION_TIME);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    return () => {
      const timerId = animationTimerRef.current;
      if (timerId) clearTimeout(timerId);
      emitter.off("game:card-drawn", handleCardDrawn);
    };
  }, [mirrored, playerId, displayNumCards]);

  return (
    <div
      className={`relative w-card-md aspect-card z-2 ${className}`}
      onMouseEnter={() => setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      style={{
        transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
      }}
    >
      {Array.from({ length: displayNumCards }).map((_, i) => {
        const offsetX = mirrored ? -i : i;
        const offsetY = isCardDragged ? 0 : -i * 1.3;
        const animatedCardOffsetY = offsetY + 750;
        const isAnimating = i === animatingIndex;
        const isBottomCard = i === 0;

        return (
          <div
            key={i}
            className='absolute'
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${isAnimating ? animatedCardOffsetY : offsetY}px)`,
              zIndex: i,
              transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
              pointerEvents: "auto",
              ...(isBottomCard && { boxShadow: `${shadowOffsetX}px 0 ${shadow}` }),
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
