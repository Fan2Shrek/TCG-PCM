"use client";

import {
  CSSProperties,
  memo,
  useContext,
} from "react";
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
  isPlaying?: boolean;
  isOpponentSideForPlayAnimation?: boolean;
  rowCardCount?: number;
};

function getAnimatedCardStyle(
  isPlaying: boolean,
  isSelected: boolean,
  isActive: boolean,
  isOpponentSide: boolean,
  rowCardCount: number,
): CSSProperties {
  if (isPlaying) {
    const playOffset = isOpponentSide ? "-200px" : "200px";
    return {
      transform: `scale(1.1) translateZ(80px) translateY(${playOffset})`,
      position: "relative",
      zIndex: 50,
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
      transition: "transform 300ms ease-in",
    };
  }

  if (isSelected) {
    return {
      transform: "scale(1.1) translateZ(80px) translateY(-40px)",
      zIndex: rowCardCount + 1,
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
    };
  }

  return {
    transform: `scale(1) translateZ(0) translateY(0)${!isActive ? " rotateZ(90deg)" : ""}`,
  };
}

function GameCard({
  card,
  targetId,
  size = CardSize.MD,
  canSelectSource = false,
  disableSelfTarget = false,
  className,
  style,
  isPlaying = false,
  isOpponentSideForPlayAnimation = false,
  rowCardCount = 0,
}: GameCardProps) {
  const { targeting, targetingActions } = useContext(GameContext);
  const { isTargeting, hoveredTargetId, selectedAttackerId } = targeting;

  const isSelectedSource = selectedAttackerId === card.instanceId;
  const isHovered =
    hoveredTargetId === targetId && isTargeting && !isSelectedSource;
  const animatedStyle = getAnimatedCardStyle(
    isPlaying,
    isSelectedSource,
    card.isActive ?? true,
    isOpponentSideForPlayAnimation,
    rowCardCount,
  );

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
      style={{ ...(animatedStyle ?? {}), ...(style ?? {}) }}
    >
      <CardWithZoom card={card} size={size} />
    </div>
  );
}

export default memo(GameCard);
