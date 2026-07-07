"use client";

import { useCallback, useRef } from "react";
import { useDropZone } from "@/hooks/useDropZone";
import CardRow from "./CardRow";

type PlayZoneProps = {
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
  selectedCardId?: string | null;
  onSelectCard?: (cardId: string | null) => void;
  hoveredTargetId?: string | null;
};

export default function PlayZone({
  passiveCardIds = [],
  monsterCardIds = [],
  className = "",
  selectedCardId,
  onSelectCard,
  hoveredTargetId,
}: PlayZoneProps) {
  const zoneRef = useRef<HTMLDivElement>(null);

  const getDropResult = useCallback(() => {
    if (!zoneRef.current) {
      throw new Error("PlayZone ref is not set.");
    }
    return "MAIN_DROPZONE";
  }, []);

  const { isDragging } = useDropZone({
    id: "PLAYZONE",
    ref: zoneRef,
    getDropResult: getDropResult,
  });

  return (
    <div
      ref={zoneRef}
      className={`w-full min-h-110 transition-all duration-200 rounded-xl flex flex-col items-center justify-between p-2 ${className}
        ${isDragging ? "ring-4 ring-yellow-300 ring-pulse shadow-lg shadow-yellow-300/30" : ""}
      `}
    >
      <CardRow
        cardIds={monsterCardIds}
        isLoggedPlayerSide
        selectedCardId={selectedCardId}
        onSelectCard={onSelectCard}
        hoveredTargetId={hoveredTargetId}
      />
      <CardRow
        cardIds={passiveCardIds}
        isLoggedPlayerSide
        selectedCardId={selectedCardId}
        onSelectCard={onSelectCard}
        hoveredTargetId={hoveredTargetId}
      />
    </div>
  );
}
