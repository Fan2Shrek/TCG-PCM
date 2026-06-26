"use client";

import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import { CardSize } from "@/constants/card";
import { CardModel } from "@/lib/cards/types/card";
import { useHandPositions } from "@/hooks/useHandPositions";
import { getCardWidthPx } from "@/lib/cards/cardUtils";

type OpponentHandProps = {
  numCards: number;
  className?: string;
};

export default function OpponentHand({ numCards, className = "" }: OpponentHandProps) {
  const cardWidthPx = getCardWidthPx(CardSize.MD);

  const dummyCards: CardModel[] = Array.from({ length: numCards }, (_, i) => ({
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
      {positionedCards.map((positionedCard) => (
        <div
          key={positionedCard.card.instanceId}
          className='absolute top-[50%] left-[50%]'
          style={{
            transform: `translate(calc(-50% + ${positionedCard.x}px), calc(50% + ${positionedCard.y}px)) rotateZ(${positionedCard.rotation}deg)`,
            zIndex: positionedCard.rank,
          }}
        >
          <DummyFaceDownCard size={CardSize.MD} />
        </div>
      ))}
    </div>
  );
}
