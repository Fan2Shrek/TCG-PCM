"use client";

import { CSSProperties } from "react";
import { CardSize } from "@/constants/card";
import type { BasicCard } from "@/lib/cards/types/card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";
import { emitter } from "@/lib/eventBus";

type GameCardProps = {
  card: BasicCard;
  targetId: string;
  size?: CardSize;
  isTargeting: boolean;
  hoveredTargetId?: string | null;
  selectedSourceId?: string | null;
  canSelectSource?: boolean;
  disableSelfTarget?: boolean;
  onSelectSource?: (cardId: string | null) => void;
  onSelectTarget?: (targetId: string) => void;
  className?: string;
  style?: CSSProperties;
};

export default function GameCard({
  card,
  targetId,
  size = CardSize.MD,
  isTargeting,
  hoveredTargetId,
  selectedSourceId,
  canSelectSource = false,
  disableSelfTarget = false,
  onSelectSource,
  onSelectTarget,
  className,
  style,
}: GameCardProps) {
  const isSelectedSource = selectedSourceId === card.instanceId;
  const isHovered =
    hoveredTargetId === targetId && isTargeting && !isSelectedSource;

  return (
    <div
      onClick={(e) => {
        e.stopPropagation();

        if (isTargeting) {
          if (disableSelfTarget && selectedSourceId === targetId) {
            return;
          }

          onSelectTarget?.(targetId);
          return;
        }

        if (canSelectSource && onSelectSource) {
          onSelectSource(isSelectedSource ? null : card.instanceId);
        }
      }}
      onMouseEnter={() => {
        if (isTargeting) {
          emitter.emit("target:hover", targetId);
        }
      }}
      onMouseLeave={() => emitter.emit("target:leave")}
      className={`card-selected ${canSelectSource || isTargeting ? "cursor-pointer" : ""} ${isHovered ? "blue-pulse" : ""} ${className ?? ""}`}
      style={style}
    >
      <CardWithZoom card={card} size={size} />
    </div>
  );
}
