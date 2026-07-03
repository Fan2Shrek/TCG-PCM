"use client";

import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import { CardSize } from "@/constants/card";
import { CardModel } from "@/lib/cards/types/card";
import { useHandPositions } from "@/hooks/useHandPositions";
import { getCardWidthPx } from "@/lib/cards/cardUtils";
import { useState, useEffect } from "react";
import { emitter } from "@/lib/eventBus";

type OpponentHandProps = {
  numCards: number;
  className?: string;
  currentPlayerId?: string;
};

const DRAWING_CARD_OFFSET = 1200;

export default function OpponentHand({ numCards, className = "", currentPlayerId }: OpponentHandProps) {
  const [displayNumCards, setDisplayNumCards] = useState(numCards);
  const [animatingCardIndex, setAnimatingCardIndex] = useState<number | null>(null);
  const cardWidthPx = getCardWidthPx(CardSize.MD);

  useEffect(() => {
    const handleCardDrawn = (event: { playerId: string; cardId: string }) => {
      if (currentPlayerId && event.playerId === currentPlayerId) return;
      setDisplayNumCards((prev) => prev + 1);
      setAnimatingCardIndex(displayNumCards);
    };

    const handleDrawComplete = () => {
      const timer = setTimeout(() => {
        setAnimatingCardIndex(null);
      }, 300);
      return () => clearTimeout(timer);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    emitter.on("animation:card-draw-complete", handleDrawComplete);
    return () => {
      emitter.off("game:card-drawn", handleCardDrawn);
      emitter.off("animation:card-draw-complete", handleDrawComplete);
    };
  }, [displayNumCards, currentPlayerId]);

  const dummyCards: CardModel[] = Array.from({ length: displayNumCards }, (_, i) => ({
    id: `opponent-card-${i}`,
    instanceId: `opponent-card-${i}`,
    name: "",
    description: "",
    cost: 0,
    image: "",
    rarity: "",
    set: "",
    effects: [],
    isActive: true,
  }));

  const positionedCards = useHandPositions(dummyCards, cardWidthPx, false);

  return (
    <div className={`relative w-82 h-82 ${className}`} style={{ transform: "scaleY(-1)", transformStyle: "preserve-3d" }}>
      {positionedCards.map((positionedCard, i) => {
        const displayY = i === animatingCardIndex ? positionedCard.y + DRAWING_CARD_OFFSET : positionedCard.y;
        const scale = i === animatingCardIndex ? 1.3 : 1;

        return (
          <div
            key={positionedCard.card.instanceId}
            className='absolute top-[50%] left-[50%]'
            style={{
              transform: `translate(calc(-50% + ${positionedCard.x}px), calc(50% + ${displayY}px)) rotateZ(${positionedCard.rotation}deg) scale(${scale})`,
              zIndex: positionedCard.rank,
              transition: `transform 300ms ease-in-out`,
            }}
          >
            <DummyFaceDownCard size={CardSize.MD} />
          </div>
        );
      })}
    </div>
  );
}
