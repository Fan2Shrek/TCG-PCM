"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { CardModel, CardWithPosition } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";
import { getCardWidthPx, getCardAspectRatio } from "@/lib/cards/cardUtils";
import HandCard from "../molecules/HandCard";
import { useHandPositions } from "@/hooks/useHandPositions";
import { useDebouncedValue } from "@/hooks/useDebounceValue";
import { emitter } from "@/lib/eventBus";

export type CardsHandProps = {
  cards: CardModel[];
  cardSize?: CardSize;
  hoverCardSize?: CardSize;
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  className?: string;
  isDisabled?: boolean;
};

export default function CardsHand({ cards, cardSize = CardSize.MD, hoverCardSize = CardSize.LG, className = "", onMouseEnter, onMouseLeave, isDisabled = false }: CardsHandProps) {
  const cardWidthPx = getCardWidthPx(cardSize);
  const hoverCardWidthPx = getCardWidthPx(hoverCardSize);
  const cardAspectRatio = getCardAspectRatio();

  const hoverYOffset = useMemo(() => {
    const normalHeightPx = cardWidthPx * cardAspectRatio;
    const hoverHeightPx = hoverCardWidthPx * cardAspectRatio;
    return (hoverHeightPx - normalHeightPx) * 2;
  }, [cardWidthPx, hoverCardWidthPx, cardAspectRatio]);

  const [pendingHoveredCard, setPendingHoveredCard] = useState<CardWithPosition | null>(null);
  const [hoveredCard, setHoveredCard] = useState<CardWithPosition | null>(null);
  const debouncedHoveredCard = useDebouncedValue(pendingHoveredCard, 50);

  const positionedCards = useHandPositions(cards, cardWidthPx, hoveredCard);

  useEffect(() => {
    setHoveredCard(debouncedHoveredCard);
  }, [debouncedHoveredCard]);

  const handleCardHover = useCallback(
    (card: CardWithPosition) => {
      setPendingHoveredCard(card);
      onMouseEnter?.();
    },
    [],
  );

  const handleCardLeave = useCallback(() => {
    setPendingHoveredCard(null);
    onMouseLeave?.();
  }, []);

  const handleCardDragEnd = useCallback((positionedCard: CardWithPosition, pointerPos: { x: number; y: number }) => {
    emitter.emit("card:played", {
      id: positionedCard.card.instanceId,
      x: pointerPos.x,
      y: pointerPos.y,
    });
  }, []);

  return (
    <div className={`relative w-82 h-62 ${className}`}>
      {positionedCards.map((positionedCard) => (
        <HandCard key={positionedCard?.card?.instanceId ?? Math.random()} positionedCard={positionedCard} hoverYOffset={hoverYOffset} cardSize={cardSize} hoverCardSize={hoverCardSize} totalCards={cards.length} onHover={handleCardHover} onLeave={handleCardLeave} onDragEnd={handleCardDragEnd} isDisabled={isDisabled} />
      ))}
    </div>
  );
}
