"use client";

import { useCallback, useContext, useRef } from "react";
import { useDropZone } from "@/hooks/useDropZone";
import { GAMEBOARD_TILT } from "@/constants/gameArea";
import { CardSize } from "@/constants/card";
import { BasicCard } from "@/lib/cards/types/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";
import { CardZones } from "@/constants/zone";

type PlayZoneProps = {
  title: string;
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
};

export default function PlayZone({ title, passiveCardIds, monsterCardIds, className = "" }: PlayZoneProps) {
  const { getCardById } = useContext(GameContext);
  const zoneRef = useRef<HTMLDivElement>(null);

  const getDropResult = useCallback((_: BasicCard) => {
    if (!zoneRef.current) {
      throw new Error("PlayZone ref is not set.");
    }
    return {
      pos: {
        x: zoneRef.current.getBoundingClientRect().left,
        y: zoneRef.current.getBoundingClientRect().top,
      },
      size: CardSize.MD,
      tilt: { x: GAMEBOARD_TILT, y: 0, z: 0 },
    };
  }, []);

  const { isDragging, isHovered } = useDropZone({
    id: "MONSTER",
    ref: zoneRef,
    getDropResult: getDropResult,
  });

  return (
    <div
      ref={zoneRef}
      className={`transition-all duration-200 rounded-xl flex flex-col items-center justify-center p-2 min-h-72 ${className}
        ${isDragging ? "ring-4 ring-blue-400/60 shadow-lg shadow-blue-400/30" : ""}
        ${isHovered ? "ring-4 ring-yellow-300 animate-pulse" : ""}
      `}
    >
      <h3 className='text-lg font-semibold mb-2'>{title}</h3>
      <div className='w-full flex flex-col gap-4'>
        <div className='flex flex-wrap justify-center gap-2'>
          {monsterCardIds.map((cardId) => {
            const card = getCardById(cardId);
            return card && <Card key={card.instanceId} card={card} size={CardSize.MD} />;
          })}
        </div>
        <div className='flex flex-wrap justify-center gap-2'>
          {passiveCardIds.map((cardId) => {
            const card = getCardById(cardId);
            return card && <Card key={card.instanceId} card={card} size={CardSize.MD} />;
          })}
        </div>
      </div>
    </div>
  );
}
