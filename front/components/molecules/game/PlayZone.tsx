"use client";

import { useCallback, useRef } from "react";
import { useDropZone } from "@/hooks/useDropZone";
import CardRow from "./CardRow";

type PlayZoneProps = {
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
  /** true when this zone represents the opponent's board rather than the logged-in player's own board */
  isOpponent?: boolean;
};

export default function PlayZone({
  passiveCardIds = [],
  monsterCardIds = [],
  className = "",
  isOpponent = false,
}: PlayZoneProps) {
  const zoneRef = useRef<HTMLDivElement>(null);

  const getDropResult = useCallback(() => {
    if (isOpponent) {
      return null;
    }

    if (!zoneRef.current) {
      throw new Error("PlayZone ref is not set.");
    }
    return "MAIN_DROPZONE";
  }, [isOpponent]);

  const { isDragging } = useDropZone({
    id: isOpponent ? "OPPONENT_PLAYZONE" : "PLAYZONE",
    ref: zoneRef,
    getDropResult,
  });

  const monsterRow = (
    <CardRow cardIds={monsterCardIds} isLoggedPlayerSide={!isOpponent} />
  );
  const passiveRow = (
    <CardRow cardIds={passiveCardIds} isLoggedPlayerSide={!isOpponent} />
  );

  return (
    <div
      ref={zoneRef}
      className={`w-full min-h-110 transition-all duration-200 rounded-xl flex flex-col items-center justify-between p-2 ${className}
        ${!isOpponent && isDragging ? "ring-4 ring-yellow-300 ring-pulse shadow-lg shadow-yellow-300/30" : ""}
      `}
    >
      {isOpponent ? passiveRow : monsterRow}
      {isOpponent ? monsterRow : passiveRow}
    </div>
  );
}
