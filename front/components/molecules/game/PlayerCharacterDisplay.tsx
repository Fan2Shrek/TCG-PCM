"use client";

import { useContext } from "react";
import { GameContext } from "@/contexts/GameContext";
import { PlayerState } from "@/lib/game/type/gameState";
import { CardSize } from "@/constants/card";
import useTargetingMode from "@/hooks/useTargetingMode";
import GameCard from "./GameCard";

type PlayerCharacterDisplayProps = {
  player: PlayerState;
  className?: string;
  hoveredTargetId?: string | null;
  onSelectTarget?: (targetId: string) => void;
};

export default function PlayerCharacterDisplay({
  player,
  className = "",
  hoveredTargetId,
  onSelectTarget,
}: PlayerCharacterDisplayProps) {
  const isTargeting = useTargetingMode();
  const { getCardById } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);
  const isHovered = hoveredTargetId === player.player.id && isTargeting;

  if (!playerCard) {
    return null;
  }

  return (
    <div className={`flex flex-col items-center gap-2 ${className}`}>
      <GameCard
        card={playerCard}
        size={CardSize.LG}
        targetId={player.player.id}
        isTargeting={isTargeting}
        hoveredTargetId={hoveredTargetId}
        onSelectTarget={onSelectTarget}
        className={isHovered ? "rounded-xl" : undefined}
      />
    </div>
  );
}
