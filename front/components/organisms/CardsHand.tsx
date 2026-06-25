"use client";

import { useCallback, useState } from "react";
import { CardModel, CardWithPosition } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";
import { getCardWidthPx } from "@/lib/cards/cardUtils";
import HandCard from "../molecules/HandCard";
import { useHandPositions } from "@/hooks/useHandPositions";
import { emitter } from "@/lib/eventBus";

export type CardsHandProps = {
  cards: CardModel[];
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  className?: string;
  isDisabled?: boolean;
};

export default function CardsHand({ cards, className = "", onMouseEnter, onMouseLeave, isDisabled = false }: CardsHandProps) {
  const [isHovered, setIsHovered] = useState(false);

  const cardSize = isHovered ? CardSize.LG : CardSize.MD;
  const cardWidthPx = getCardWidthPx(cardSize);

  const positionedCards = useHandPositions(cards, cardWidthPx, isHovered);

  const handleCardHover = useCallback(() => {
    setIsHovered(true);
    onMouseEnter?.();
  }, [onMouseEnter]);

  const handleCardLeave = useCallback(() => {
    setIsHovered(false);
    onMouseLeave?.();
  }, [onMouseLeave]);

  const handleCardDragEnd = useCallback((positionedCard: CardWithPosition, pointerPos: { x: number; y: number }) => {
    emitter.emit("card:played", {
      id: positionedCard.card.instanceId,
      x: pointerPos.x,
      y: pointerPos.y,
    });
  }, []);

  return (
    <div className={`relative w-82 h-62 ${className}`}>
      {positionedCards.map((positionedCard, i) => (
        <HandCard key={positionedCard?.card?.instanceId ?? i} positionedCard={positionedCard} cardSize={cardSize} totalCards={cards.length} onHover={handleCardHover} onLeave={handleCardLeave} onDragEnd={handleCardDragEnd} isDisabled={isDisabled} />
      ))}
    </div>
  );
}
