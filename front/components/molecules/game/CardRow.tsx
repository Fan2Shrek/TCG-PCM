"use client";

import { useContext } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";

type CardRowProps = {
  cardIds: string[];
  className?: string;
};

export default function CardRow({ cardIds, className }: CardRowProps) {
  const { getCardById } = useContext(GameContext);

  return (
    <div className={`flex flex-wrap justify-center gap-2 ${className}`}>
      {cardIds.map((cardId) => {
        const card = getCardById(cardId);
        return card && <Card key={card.instanceId} card={card} size={CardSize.MD} />;
      })}
    </div>
  );
}
