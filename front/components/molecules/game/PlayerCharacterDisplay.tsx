"use client";

import { useContext } from "react";
import { GameContext } from "@/contexts/GameContext";
import { PlayerState } from "@/lib/game/type/gameState";
import Card from "../Card";
import { CardSize } from "@/constants/card";
import { emitter } from "@/lib/eventBus";

type PlayerCharacterDisplayProps = {
  player: PlayerState;
  className?: string;
  isTargeting?: boolean;
  hoveredTargetId?: string | null;
};

export default function PlayerCharacterDisplay({ player, className = "", isTargeting = false, hoveredTargetId }: PlayerCharacterDisplayProps) {
  const { getCardById } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);
  const isHovered = hoveredTargetId === player.player.id && isTargeting;

  if (!playerCard) {
    return null;
  }

  return (
    <div
      className={`flex flex-col items-center gap-2 ${className} ${isTargeting ? "cursor-pointer" : ""}`}
      onMouseEnter={() => isTargeting && emitter.emit("target:hover", player.player.id)}
      onMouseLeave={() => emitter.emit("target:leave")}
      onClick={(e) => {
        e.stopPropagation();
        isTargeting && emitter.emit("target:click", player.player.id);
      }}
    >
      <div className={isHovered ? "blue-pulse rounded-xl" : ""}>
        <Card card={playerCard} size={CardSize.LG} />
      </div>
    </div>
  );
}
