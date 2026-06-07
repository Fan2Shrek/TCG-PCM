"use client";

import { useContext } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";

type EnemyPassiveZoneProps = {
  title: string;
  cards: string[];
  className?: string;
};

export default function EnemyPassiveZone({
  title,
  cards,
  className = "",
}: EnemyPassiveZoneProps) {
  const { getCardById } = useContext(GameContext);

  return (
    <div
      className={`transition-all duration-200 rounded-xl flex flex-col items-center justify-center p-2 min-h-72 ${className}`}
    >
      <h3 className="text-lg font-semibold mb-2">{title}</h3>
      <div className="flex flex-wrap justify-center gap-2">
        {cards.map((cardId) => {
          const card = getCardById(cardId);
          return (
            card && (
              <Card key={card.instanceId} card={card} size={CardSize.SM} />
            )
          );
        })}
      </div>
    </div>
  );
}
