"use client";

import { useCallback, useState, useEffect } from "react";
import { BasicCard, CardWithPosition } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";
import { getCardWidthPx } from "@/lib/cards/cardUtils";
import HandCard from "../molecules/HandCard";
import Card from "../molecules/Card";
import { useHandPositions } from "@/hooks/useHandPositions";
import { useDebouncedValue } from "@/hooks/useDebounceValue";
import { useIdlePrewarm } from "@/hooks/useIdlePrewarm";
import { emitter } from "@/lib/eventBus";

export type CardsHandProps = {
  cards: BasicCard[];
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  className?: string;
  isDisabled?: boolean;
};

export default function CardsHand({
  cards,
  className = "",
  onMouseEnter,
  onMouseLeave,
  isDisabled = false,
}: CardsHandProps) {
  const [isPendingHovered, setIsPendingHovered] = useState(false);
  const [animatingCardIndex, setAnimatingCardIndex] = useState<number | null>(
    null,
  );
  const [isMobileDevice, setIsMobileDevice] = useState(false);
  const [hasHoveredOnce, setHasHoveredOnce] = useState(false);
  const isHovered = useDebouncedValue(isPendingHovered, 50);
  const shouldPrewarmHover = useIdlePrewarm({
    disabled: hasHoveredOnce,
    timeout: 300,
  });
  const isHandExpanded = !isMobileDevice && isHovered;

  // Latches on the first debounced hover, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (isHandExpanded && !hasHoveredOnce) {
    setHasHoveredOnce(true);
  }

  useEffect(() => {
    const mediaQuery = window.matchMedia(
      "(max-width: 1024px), (pointer: coarse)",
    );

    const updateDeviceType = () => {
      setIsMobileDevice(mediaQuery.matches);
    };

    updateDeviceType();
    mediaQuery.addEventListener("change", updateDeviceType);

    return () => {
      mediaQuery.removeEventListener("change", updateDeviceType);
    };
  }, []);

  useEffect(() => {
    if (isHandExpanded) {
      onMouseEnter?.();
    } else {
      onMouseLeave?.();
    }
  }, [isHandExpanded, onMouseEnter, onMouseLeave]);

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

  const cardSize = isMobileDevice
    ? CardSize.SM
    : isHovered
      ? CardSize.LG
      : CardSize.MD;
  const cardWidthPx = getCardWidthPx(cardSize);

  const positionedCards = useHandPositions(cards, cardWidthPx, isHandExpanded);

  const handleCardHover = useCallback(() => {
    if (isMobileDevice) {
      return;
    }

    setIsPendingHovered(true);
  }, [isMobileDevice]);

  const handleCardLeave = useCallback(() => {
    if (isMobileDevice) {
      return;
    }

    setIsPendingHovered(false);
  }, [isMobileDevice]);

  const handleCardDragEnd = useCallback((positionedCard: CardWithPosition) => {
    emitter.emit("card:played", {
      card: positionedCard.card,
    });
  }, []);

  return (
    <div className={`relative sm:w-82 h-62 ${className}`}>
      {shouldPrewarmHover && !hasHoveredOnce && (
        <div
          className="absolute -z-10 opacity-0 pointer-events-none"
          aria-hidden
        >
          {cards.map((card) => (
            <Card
              key={`prewarm-${card.instanceId}`}
              card={card}
              size={CardSize.LG}
            />
          ))}
        </div>
      )}

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
