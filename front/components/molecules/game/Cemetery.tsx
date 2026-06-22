"use client";

import { useContext } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";

type CemeteryProps = {
  cardIds: string[];
  className?: string;
};

export default function Cemetery({ cardIds, className = "" }: CemeteryProps) {
  const { getCardById } = useContext(GameContext);

  const lastCard = cardIds.length > 0 ? getCardById(cardIds[cardIds.length - 1]) : null;

  return (
    <div className={`rounded-xl flex flex-col items-center justify-center p-2 ${className}`}>
      <div className='flex flex-col items-center gap-2'>
        {lastCard ? <Card card={lastCard} size={CardSize.MD} /> : <div className='w-card-md aspect-card rounded-lg border-2 border-dashed border-gray-400 flex items-center justify-center text-gray-400'>Empty</div>}
        {cardIds.length > 0 && <p className='text-sm text-gray-300'>{cardIds.length} cards</p>}
      </div>
    </div>
  );
}
