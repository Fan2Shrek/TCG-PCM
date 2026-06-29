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
  const [displayNumCards, setDisplayNumCards] = useState(numCards);
  const [shouldAnimateDraw, setShouldAnimateDraw] = useState(false);
  const animationTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const shadowOffsetX = mirrored ? -displayNumCards * 2 + 6 : displayNumCards * 2 - 6;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

  useEffect(() => {
    const handleCardDrawn = () => {
      if (mirrored) return;

      setDisplayNumCards(numCards + 1);
      setShouldAnimateDraw(false);

      requestAnimationFrame(() => {
        setShouldAnimateDraw(true);
      });

      animationTimerRef.current = setTimeout(() => {
        console.log(displayNumCards);

        emitter.emit("animation:card-draw-complete");
        setDisplayNumCards(numCards);
        setShouldAnimateDraw(false);
      }, 500);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    return () => {
      if (animationTimerRef.current) clearTimeout(animationTimerRef.current);
      emitter.off("game:card-drawn", handleCardDrawn);
    };
  }, [displayNumCards, numCards, mirrored]);

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
        const isAnimating = shouldAnimateDraw && i === displayNumCards;
        const isBottomCard = i === 0;

        return (
          <div
            key={i}
            className='absolute'
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${isAnimating ? "500px" : offsetY}px)`,
              zIndex: i,
              transition: isAnimating ? "transform 500ms ease-in" : `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
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
