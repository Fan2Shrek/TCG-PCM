"use client";

import { useContext } from "react";
import { GameContext } from "@/contexts/GameContext";
import { PlayerState } from "@/lib/game/type/gameState";
import Card from "../Card";
import { CardSize } from "@/constants/card";

type PlayerCharacterDisplayProps = {
  player: PlayerState;
  className?: string;
};

export default function PlayerCharacterDisplay({ player, className = "" }: PlayerCharacterDisplayProps) {
  const { getCardById } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);

  if (!playerCard) {
    return null;
  }

  return (
    <div className={`flex flex-col items-center gap-2 ${className}`}>
      <Card card={playerCard} size={CardSize.LG} />
    </div>
  );
}
