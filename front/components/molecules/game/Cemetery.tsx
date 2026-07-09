"use client";

import { useContext, useEffect, useState } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";
import PileTooltip from "@/components/atoms/PileTooltip";
import { GameContext } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import {
  GAMEBOARD_ANIMATION_DURATION,
  GAMEBOARD_ANIMATION_TIMING,
} from "@/constants/gameArea";

type CemeteryProps = {
  cardIds: string[];
  className?: string;
  mirrored?: boolean;
  isCardDragged?: boolean;
};

const CARD_PLAY_ANIMATION_TIME = 300;

export default function Cemetery({
  cardIds,
  className = "",
  mirrored = false,
  isCardDragged = false,
}: CemeteryProps) {
  const { getCardById } = useContext(GameContext);
  const [showTooltip, setShowTooltip] = useState(false);
  const [playingCardIds, setPlayingCardIds] = useState<Set<string>>(new Set());

  const shadowOffsetX = mirrored
    ? -cardIds.length * 2 + 2
    : cardIds.length * 2 - 2;
  const shadow = `1px 0 rgba(0,0,0,0.66)`;

  useEffect(() => {
    const handleCardAnimated = (event: { card: { instanceId: string } }) => {
      setPlayingCardIds((prev) => new Set(prev).add(event.card.instanceId));

      setTimeout(() => {
        setPlayingCardIds((prev) => {
          const next = new Set(prev);
          next.delete(event.card.instanceId);
          return next;
        });
      }, CARD_PLAY_ANIMATION_TIME);
    };

    emitter.on("card:played", handleCardAnimated);
    emitter.on("card:discarded", handleCardAnimated);

    return () => {
      emitter.off("card:played", handleCardAnimated);
      emitter.off("card:discarded", handleCardAnimated);
    };
  }, []);

  return (
    <div
      className={`relative w-card-md aspect-card ${className}`}
      onMouseEnter={() => setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      style={{
        transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
        zIndex: cardIds.length,
      }}
    >
      {cardIds.map((cardId, i) => {
        const card = getCardById(cardId);
        if (!card) return null;
        const offsetX = mirrored ? -i : i;
        const offsetY = isCardDragged ? 0 : -i * 1.3;
        const isBottomCard = i === 0;
        const isTopCard = i === cardIds.length - 1;
        const isPlaying = playingCardIds.has(card.instanceId);
        const playOffset = mirrored ? "-200px" : "200px";

        return (
          <div
            key={cardId}
            className="absolute"
            style={{
              transform: isPlaying
                ? `scale(1.1) translateZ(80px) translateY(${playOffset}) translateX(${offsetX}px) translateY(${offsetY}px)`
                : `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${offsetY}px)`,
              zIndex: i,
              transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
              ...(isBottomCard && {
                boxShadow: `${shadowOffsetX}px 0 ${shadow}`,
              }),
            }}
          >
            {isTopCard ? (
              <CardWithZoom card={card} size={CardSize.MD} />
            ) : (
              <Card card={card} size={CardSize.MD} />
            )}
          </div>
        );
      })}
      <PileTooltip
        isVisible={showTooltip}
        count={cardIds.length}
        isMirrored={mirrored}
        label="cartes"
      />
    </div>
  );
}
