"use client";

import { useState } from "react";
import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import { CardSize } from "@/constants/card";
import { GAMEBOARD_TILT, GAMEBOARD_ANIMATION_DURATION, GAMEBOARD_SHADOW_ANIMATION_DURATION, GAMEBOARD_ANIMATION_TIMING } from "@/constants/gameArea";

type DrawPileProps = {
  numCards: number;
  className?: string;
  mirrored?: boolean;
  isCardDragged?: boolean;
};

export default function DrawPile({ numCards, className = "", mirrored = false, isCardDragged = false }: DrawPileProps) {
  const [showTooltip, setShowTooltip] = useState(false);

  const pileTransform = isCardDragged ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)" : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg)`;

  const shadowOffsetY = isCardDragged ? 0 : -20;
  const shadowOffsetX = mirrored ? -(numCards * 2) : numCards * 2;
  const shadow = `1px 20px rgba(0,0,0,0.66)`;

  return (
    <div className={`relative z-1 ${className}`} onMouseEnter={() => setShowTooltip(true)} onMouseLeave={() => setShowTooltip(false)}>
      <div className='relative w-card-md aspect-card' style={{ transform: pileTransform, transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}` }}>
        {Array.from({ length: numCards }).map((_, i) => {
          const offsetX = mirrored ? -(i - numCards / 2) * 1 : (i - numCards / 2) * 1;
          const offsetY = isCardDragged ? 0 : -i * 1.5;
          return (
            <div
              key={i}
              className='absolute'
              style={{
                transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${offsetY}px)`,
                zIndex: i,
                transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}, box-shadow ${GAMEBOARD_SHADOW_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
                ...(i === 0 && { boxShadow: `${shadowOffsetX}px ${shadowOffsetY}px ${shadow}` }),
              }}
            >
              <DummyFaceDownCard size={CardSize.MD} id={`draw-pile-card-${i}`} />
            </div>
          );
        })}
      </div>
      <div
        className={`absolute bg-gray-900 text-white text-sm px-3 py-1 rounded whitespace-nowrap pointer-events-none transition-all duration-300 z-10 top-1/2 -translate-y-1/2 ${showTooltip ? "opacity-100" : "opacity-0 pointer-events-none"}
      ${mirrored ? `left-full ml-2 ${showTooltip ? "translate-x-0" : "-translate-x-2"}` : `right-full mr-2 ${showTooltip ? "translate-x-0" : "translate-x-2"}`}`}
      >
        {numCards} cards left
      </div>
    </div>
  );
}
