import { GameContext } from "@/contexts/GameContext";
import { useContext, useEffect, useRef } from "react";
import Card from "../Card";
import {
  registerDropZone,
  unregisterDropZone,
} from "@/lib/dropZones/dropzoneRegistry";
import { DropZone } from "@/lib/dropZones/types/dropZone";
import { useDropZoneHighlight } from "@/lib/dropZones/hooks/useDropZoneHighlight";
import { BasicCard } from "@/lib/cards/types/card";
import { GAMEBOARD_TILT } from "@/constants/gameArea";

type MonsterZoneProps = {
  title: string;
  cardsIds: string[];
  isUsersZone?: boolean;
  onCardClick?: (cardId: string) => void;
  selectedCardId?: string | null;
  clickable?: boolean;
  isCardDisabled?: (cardId: string) => boolean;
  className?: string;
};

export default function MonsterZone({
  title,
  cardsIds,
  onCardClick,
  selectedCardId,
  clickable = false,
  isCardDisabled,
  className,
  isUsersZone = false,
}: MonsterZoneProps) {
  const { getCardById } = useContext(GameContext);

  const { isDragging, isHovered } = useDropZoneHighlight(() =>
    zoneRef.current!.getBoundingClientRect(),
  );
  const zoneRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!zoneRef.current || !isUsersZone) return;

    const monsterDropZone: DropZone = {
      id: "",
      getRect: () => zoneRef.current!.getBoundingClientRect(),
      getDropResult: (card: BasicCard) => {
        return {
          pos: {
            x: zoneRef.current!.getBoundingClientRect().left,
            y: zoneRef.current!.getBoundingClientRect().top,
          },
          size: "md",
          tilt: { x: GAMEBOARD_TILT, y: 0, z: 0 },
        };
      },
    };

    registerDropZone(monsterDropZone);

    return () => {
      unregisterDropZone(monsterDropZone.id);
    };
  });

  return (
    <div
      ref={zoneRef}
      className={`transition-all duration-200 rounded-xl flex flex-row justify-center items-center gap-2 min-h-72 ${className}
        ${isDragging ? "ring-4 ring-blue-400/60 shadow-lg shadow-blue-400/30" : ""}
        ${isHovered ? "ring-4 ring-yellow-300 animate-pulse" : ""}
      `}
    >
      <h3 className="text-lg font-semibold mb-2">{title}</h3>

      <div className="flex gap-2">
        {cardsIds.map((cardId) => {
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
}
