"use client";

import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";

type PassivesZoneProps = {
  title: string;
  cards: string[];
  className?: string;
};

export default function PassivesZone({
  title,
  cards,
  className = "",
}: PassivesZoneProps) {
  const { getCardById } = useContext(GameContext);

  return (
    <div
      className={`flex flex-col items-center justify-center p-2 ${className}`}
    >
      <h3 className="text-lg font-semibold mb-2">{title}</h3>
      <div className="flex flex-wrap justify-center gap-2">
        {cards.map((cardId) => {
          const card = getCardById(cardId);
          return card && <Card key={card.instanceId} card={card} size="sm" />;
        })}
      </div>
    </div>
  );
}
