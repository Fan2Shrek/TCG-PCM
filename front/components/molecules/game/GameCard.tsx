"use client";

import { CSSProperties, memo, useContext } from "react";
import { CardSize } from "@/constants/card";
import type { BasicCard } from "@/lib/cards/types/card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";
import { GameContext } from "@/contexts/GameContext";

type GameCardProps = {
  card: BasicCard;
  targetId: string;
  size?: CardSize;
  canSelectSource?: boolean;
  disableSelfTarget?: boolean;
  className?: string;
  style?: CSSProperties;
};

function GameCard({
  card,
  targetId,
  size = CardSize.MD,
  canSelectSource = false,
  disableSelfTarget = false,
  className,
  style,
}: GameCardProps) {
  const { targeting, targetingActions } = useContext(GameContext);
  const { isTargeting, hoveredTargetId, selectedAttackerId } = targeting;

  const isSelectedSource = selectedAttackerId === card.instanceId;
  const isHovered =
    hoveredTargetId === targetId && isTargeting && !isSelectedSource;

  return (
    <div
      onClick={(e) => {
        e.stopPropagation();

        if (isTargeting) {
          if (disableSelfTarget && selectedAttackerId === targetId) {
            return;
          }

          targetingActions.handleTargetClick(targetId);
          return;
        }

        if (canSelectSource) {
          targetingActions.selectAttacker(
            isSelectedSource ? null : card.instanceId,
          );
        }
      }}
      onMouseEnter={() => {
        if (isTargeting) {
          targetingActions.hoverTarget(targetId);
        }
      }}
      onMouseLeave={() => targetingActions.hoverTarget(null)}
      className={`card-selected ${canSelectSource || isTargeting ? "cursor-pointer" : ""} ${isHovered ? "blue-pulse" : ""} ${className ?? ""}`}
      style={style}
    >
      <CardWithZoom card={card} size={size} />
    </div>
  );
}

export default memo(GameCard);
