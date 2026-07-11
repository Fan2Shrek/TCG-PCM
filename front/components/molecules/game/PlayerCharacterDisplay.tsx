"use client";

import { useContext } from "react";
import { GameContext } from "@/contexts/GameContext";
import { PlayerState } from "@/lib/game/type/gameState";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";
import { CardSize } from "@/constants/card";
import { emitter } from "@/lib/eventBus";
import useTargetingMode from "@/hooks/useTargetingMode";

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
    <div
      className={`flex flex-col items-center gap-2 ${className} ${isTargeting ? "cursor-pointer" : ""}`}
      onMouseEnter={() =>
        isTargeting && emitter.emit("target:hover", player.player.id)
      }
      onMouseLeave={() => emitter.emit("target:leave")}
      onClick={(e) => {
        e.stopPropagation();
        if (isTargeting) {
          onSelectTarget?.(player.player.id);
        }
      }}
    >
      <div className={isHovered ? "blue-pulse rounded-xl" : ""}>
        <CardWithZoom card={playerCard} size={CardSize.LG} />
      </div>
    </div>
  );
}
