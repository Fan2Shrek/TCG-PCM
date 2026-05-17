import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import Card from "../Card";

type MonsterZoneProps = {
  title: string;
  cards: string[];
  onCardClick?: (cardId: string) => void;
  selectedCardId?: string | null;
  clickable?: boolean;
  isCardDisabled?: (cardId: string) => boolean;
  className?: string;
};

export default ({
  title,
  cards,
  onCardClick,
  selectedCardId,
  clickable = false,
  isCardDisabled,
  className,
}: MonsterZoneProps) => {
  const { getCardById } = useContext(GameContext);

  return (
    <div className={`flex flex-col items-center gap-2 ${className}`}>
      <h3 className="text-lg font-semibold mb-2">{title}</h3>

      <div className="flex gap-2">
        {cards.map((cardId) => {
          const card = getCardById(cardId);
          const cardDisabled =
            !clickable || isCardDisabled?.(cardId) || card?.isActive === false;

          return (
            <button
              key={cardId}
              type="button"
              onClick={() => onCardClick?.(cardId)}
              disabled={cardDisabled}
              className={`rounded-xl transition-transform ${
                cardDisabled
                  ? "cursor-not-allowed"
                  : "cursor-pointer hover:-translate-y-1"
              } ${selectedCardId === cardId ? "ring-4 ring-yellow-300 ring-offset-2 ring-offset-green-900" : ""}`}
            >
              {card && <Card card={card} />}
            </button>
          );
        })}
      </div>
    </div>
  );
};
