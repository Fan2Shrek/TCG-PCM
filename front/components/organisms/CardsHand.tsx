"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { CardModel, CardSize, CardWithPosition } from "../types/card";
import {
  getCardWidthPx,
  getCardAspectRatio,
} from "../utils/cardUtils";
import HandCard from "../molecules/HandCard";
import { useHandPositions } from "../hooks/useHandPositions";
import { useDebouncedValue } from "../hooks/useDebounceValue";

export type CardsHandProps = {
  cards: CardModel[];
  cardSize?: CardSize;
  hoverCardSize?: CardSize;
  className?: string;
};

export default function CardsHand({
  cards,
  cardSize = "md",
  hoverCardSize = "lg",
  className = "",
}: CardsHandProps) {

  const cardWidthPx = getCardWidthPx(cardSize);
  const hoverCardWidthPx = getCardWidthPx(hoverCardSize);
  const cardAspectRatio = getCardAspectRatio();

  const hoverYOffset = useMemo(() => {
    const normalHeightPx = cardWidthPx * cardAspectRatio;
    const hoverHeightPx = hoverCardWidthPx * cardAspectRatio;
    return (hoverHeightPx - normalHeightPx) * 2;
  }, [cardWidthPx, hoverCardWidthPx, cardAspectRatio]);

  const [hoveredCard, setHoveredCard] = useState<CardWithPosition | null>(null);
  const [pendingHoveredCard, setPendingHoveredCard] = useState<CardWithPosition | null>(null);
  const debouncedHoveredCard = useDebouncedValue(pendingHoveredCard, 50);

  const positionedCards = useHandPositions(cards, cardWidthPx, hoveredCard);

  useEffect(() => {
    setHoveredCard(debouncedHoveredCard);
  }, [debouncedHoveredCard]);

  const handleCardDrag = useCallback((e: MouseEvent) => {
    //TODO implement card play through drag and drop
    console.log(e);
  }, []);

  const handleCardHover = useCallback((card: CardWithPosition) => {
    setPendingHoveredCard({ ...card, y: hoveredCard?.y ?? card.y });
  }, [hoveredCard]);

  const handleCardLeave = useCallback(() => setPendingHoveredCard(null), []);

  return (
    <div className={`relative w-82 h-82 ${className}`}>
      {positionedCards.map((positionedCard) => (
        <HandCard
          key={positionedCard.card.id}
          positionedCard={positionedCard}
          hoverYOffset={hoverYOffset}
          cardSize={cardSize}
          hoverCardSize={hoverCardSize}
          totalCards={cards.length}
          onHover={handleCardHover}
          onLeave={handleCardLeave}
          onDragCard={handleCardDrag}
        />
      ))}
    </div>
  );
}
