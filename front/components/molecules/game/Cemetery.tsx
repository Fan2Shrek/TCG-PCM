"use client";

import { useContext, useState } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import PileTooltip from "@/components/atoms/PileTooltip";
import { GameContext } from "@/contexts/GameContext";
import { GAMEBOARD_ANIMATION_DURATION, GAMEBOARD_ANIMATION_TIMING } from "@/constants/gameArea";

type CemeteryProps = {
  cardIds: string[];
  className?: string;
  mirrored?: boolean;
  isCardDragged?: boolean;
};

export default function Cemetery({ cardIds, className = "", mirrored = false, isCardDragged = false }: CemeteryProps) {
  const { getCardById } = useContext(GameContext);
  const [showTooltip, setShowTooltip] = useState(false);
  const shadowOffsetX = mirrored ? -cardIds.length + 3 : cardIds.length - 3;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

  return (
    <div
      className={`relative w-card-md aspect-card z-1 ${className}`}
      onMouseEnter={() => setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      style={{
        transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
      }}
    >
      {cardIds.map((cardId, i) => {
        const card = getCardById(cardId);
        if (!card) return null;
        const offsetX = mirrored ? -i : i;
        const offsetY = isCardDragged ? 0 : -i * 1.5;
        return (
          <div
            key={cardId}
            className='absolute'
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${offsetY}px)`,
              zIndex: i,
              transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING},
                box-shadow ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
              ...(i === cardIds.length - 1 && { boxShadow: `${shadowOffsetX}px 0 ${shadow}` }),
            }}
          >
            <Card card={card} size={CardSize.MD} />
          </div>
        );
      })}
      <PileTooltip isVisible={showTooltip} count={cardIds.length} mirrored={mirrored} label='cards' />
    </div>
  );
}
