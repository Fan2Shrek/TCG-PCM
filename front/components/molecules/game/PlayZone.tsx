"use client";

import { useCallback, useRef } from "react";
import { useDropZone } from "@/hooks/useDropZone";
import { GAMEBOARD_TILT } from "@/constants/gameArea";
import { CardSize } from "@/constants/card";
import { BasicCard } from "@/lib/cards/types/card";
import CardRow from "./CardRow";

type PlayZoneProps = {
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
  selectedCardId?: string | null;
  onSelectCard?: (cardId: string | null) => void;
  hoveredTargetId?: string | null;
};

export default function PlayZone({ passiveCardIds, monsterCardIds, className = "", selectedCardId, onSelectCard, hoveredTargetId }: PlayZoneProps) {
  const zoneRef = useRef<HTMLDivElement>(null);

  const getDropResult = useCallback((_: BasicCard) => {
    if (!zoneRef.current) {
      throw new Error("PlayZone ref is not set.");
    }
    return {
      pos: {
        x: zoneRef.current.getBoundingClientRect().left,
        y: zoneRef.current.getBoundingClientRect().top,
        z: 50,
      },
      size: CardSize.MD,
      tilt: { x: GAMEBOARD_TILT, y: 0, z: 0 },
    };
  }, []);

  const { isDragging, isHovered } = useDropZone({
    id: "PLAYZONE",
    ref: zoneRef,
    getDropResult: getDropResult,
  });

  return (
    <div
      ref={zoneRef}
      className={`w-full min-h-110 transition-all duration-200 rounded-xl flex flex-col items-center justify-between p-2 ${className}
        ${isDragging ? "ring-4 ring-blue-400/60 shadow-lg shadow-blue-400/30" : ""}
        ${isHovered ? "ring-4 ring-yellow-300 ring-pulse" : ""}
      `}
    >
      <CardRow cardIds={monsterCardIds} isLoggedPlayerSide selectedCardId={selectedCardId} onSelectCard={onSelectCard} hoveredTargetId={hoveredTargetId} />
      <CardRow cardIds={passiveCardIds} isLoggedPlayerSide selectedCardId={selectedCardId} onSelectCard={onSelectCard} hoveredTargetId={hoveredTargetId} />
    </div>
  );
}
