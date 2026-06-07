"use client";

import { useContext } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";

type EnemyPlayZoneProps = {
  title: string;
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
};

export default function EnemyPlayZone({
  title,
  passiveCardIds,
  monsterCardIds,
  className = "",
}: EnemyPlayZoneProps) {
  const { getCardById } = useContext(GameContext);

  return (
    <div
      className={`transition-all duration-200 rounded-xl flex flex-col items-center justify-center p-2 min-h-72 ${className}`}
    >
      <h3 className="text-lg font-semibold mb-2">{title}</h3>
      <div className="w-full flex flex-col gap-4">
        <div className="flex flex-wrap justify-center gap-2">
          {passiveCardIds.map((cardId) => {
            const card = getCardById(cardId);
            return card && <Card key={card.instanceId} card={card} size={CardSize.MD} />;
          })}
        </div>
        <div className="flex flex-wrap justify-center gap-2">
          {monsterCardIds.map((cardId) => {
            const card = getCardById(cardId);
            return card && <Card key={card.instanceId} card={card} size={CardSize.MD} />;
          })}
        </div>
      </div>
    </div>
  );
}
