import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import Card from "../Card";

type BoardRowProps = {
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
}: BoardRowProps) => {
  const { getCardById } = useContext(GameContext);

  return (
    <div className={`flex flex-col items-center gap-2 ${className}`}>
      <div className="text-sm opacity-70">{title}</div>

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
              <Card card={card} />
            </button>
          );
        })}
      </div>
    </div>
  );
};
