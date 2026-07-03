"use client";

import { useCallback, useState, useEffect } from "react";
import { CardModel, CardWithPosition } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";
import { getCardWidthPx } from "@/lib/cards/cardUtils";
import HandCard from "../molecules/HandCard";
import { useHandPositions } from "@/hooks/useHandPositions";
import { useDebouncedValue } from "@/hooks/useDebounceValue";
import { emitter } from "@/lib/eventBus";

export type CardsHandProps = {
  cards: CardModel[];
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  className?: string;
  isDisabled?: boolean;
};

export default function CardsHand({ cards, className = "", onMouseEnter, onMouseLeave, isDisabled = false }: CardsHandProps) {
  const [isPendingHovered, setIsPendingHovered] = useState(false);
  const [animatingCardIndex, setAnimatingCardIndex] = useState<number | null>(null);
  const isHovered = useDebouncedValue(isPendingHovered, 50);

  useEffect(() => {
    if (isHovered) {
      onMouseEnter?.();
    } else {
      onMouseLeave?.();
    }
  }, [isHovered, onMouseEnter, onMouseLeave]);

  useEffect(() => {
    const handleCardDrawn = () => {
      setAnimatingCardIndex(cards.length);
    };

    const handleDrawComplete = () => {
      const animationTimer = setTimeout(() => {
        setAnimatingCardIndex(null);
      }, 200);

      return () => clearTimeout(animationTimer);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    emitter.on("animation:card-draw-complete", handleDrawComplete);
    return () => {
      emitter.off("game:card-drawn", handleCardDrawn);
      emitter.off("animation:card-draw-complete", handleDrawComplete);
    };
  }, [cards.length]);

  const cardSize = isHovered ? CardSize.LG : CardSize.MD;
  const cardWidthPx = getCardWidthPx(cardSize);

  const positionedCards = useHandPositions(cards, cardWidthPx, isHovered);

  const handleCardHover = useCallback(() => {
    setIsPendingHovered(true);
  }, []);

  const handleCardLeave = useCallback(() => {
    setIsPendingHovered(false);
  }, []);

  const handleCardDragEnd = useCallback((positionedCard: CardWithPosition, pointerPos: { x: number; y: number }) => {
    emitter.emit("card:played", {
      card: positionedCard.card,
    });
  }, []);

  return (
    <div className={`relative w-82 h-62 ${className}`}>
      {positionedCards.map((positionedCard, i) => (
        <HandCard
          key={positionedCard?.card?.instanceId ?? i}
          positionedCard={positionedCard}
          cardSize={cardSize}
          totalCards={cards.length}
          onHover={handleCardHover}
          onLeave={handleCardLeave}
          onDragEnd={handleCardDragEnd}
          isDisabled={isDisabled}
          isHandHovered={isHovered}
          isAnimatingDraw={i === animatingCardIndex}
        />
      ))}
    </div>
  );
}
