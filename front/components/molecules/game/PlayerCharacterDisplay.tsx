"use client";

import { useContext } from "react";
import { GameContext } from "@/contexts/GameContext";
import { PlayerState } from "@/lib/game/type/gameState";
import { CardSize } from "@/constants/card";
import GameCard from "./GameCard";

type PlayerCharacterDisplayProps = {
  player: PlayerState;
  className?: string;
};

export default function PlayerCharacterDisplay({
  player,
  className = "",
}: PlayerCharacterDisplayProps) {
  const { getCardById, targeting } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);
  const isHovered =
    targeting.hoveredTargetId === player.player.id && targeting.isTargeting;

  if (!playerCard) {
    return null;
  }

  return (
    <div className={`flex flex-col items-center gap-2 ${className}`}>
      <GameCard
        card={playerCard}
        size={CardSize.LG}
        targetId={player.player.id}
        className={isHovered ? "rounded-xl" : undefined}
      />
    </div>
  );
}
