"use client";

import { useState } from "react";
import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import PileTooltip from "@/components/atoms/PileTooltip";
import { CardSize } from "@/constants/card";
import { GAMEBOARD_ANIMATION_DURATION, GAMEBOARD_ANIMATION_TIMING } from "@/constants/gameArea";

type DrawPileProps = {
  numCards: number;
  className?: string;
  mirrored?: boolean;
  isCardDragged?: boolean;
};

export default function DrawPile({ numCards, className = "", mirrored = false, isCardDragged = false }: DrawPileProps) {
  const [showTooltip, setShowTooltip] = useState(false);
  const shadowOffsetX = mirrored ? -numCards * 2 + 6 : numCards * 2 - 6;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

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
        return (
          <div
            key={i}
            className='absolute'
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${offsetY}px)`,
              zIndex: i,
              transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING},
                box-shadow ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
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
