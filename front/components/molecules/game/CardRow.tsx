"use client";

import { useContext } from "react";
import { CardSize } from "@/constants/card";
import Card from "../Card";
import { GameContext } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";

type CardRowProps = {
  cardIds: string[];
  className?: string;
  isLoggedPlayerSide?: boolean;
  selectedCardId?: string | null;
  onSelectCard?: (cardId: string | null) => void;
  hoveredTargetId?: string | null;
};

export default function CardRow({ cardIds, className, isLoggedPlayerSide = false, selectedCardId, onSelectCard, hoveredTargetId }: CardRowProps) {
  const { getCardById } = useContext(GameContext);
  const isControlled = selectedCardId !== undefined && onSelectCard !== undefined;
  const isTargeting = selectedCardId !== null;

  return (
    <div className={`flex flex-wrap justify-center gap-2 ${className}`} style={{ perspective: "1000px" }}>
      {cardIds.map((cardId) => {
        const card = getCardById(cardId);
        const isSelected = selectedCardId === card?.instanceId;
        const isHovered = hoveredTargetId === card?.instanceId && isTargeting && !isSelected;
        const canSelect = isLoggedPlayerSide && isControlled && card?.isActive;

        return (
          card && (
            <div
              key={card.instanceId}
              onClick={(e) => {
                e.stopPropagation();
                if (canSelect) {
                  onSelectCard?.(isSelected ? null : card.instanceId);
                } else if (isTargeting && card?.isActive) {
                  emitter.emit("target:click", card.instanceId);
                }
              }}
              onMouseEnter={() => isTargeting && emitter.emit("target:hover", card.instanceId)}
              onMouseLeave={() => emitter.emit("target:leave")}
              className={`card-selected ${canSelect || isTargeting ? "cursor-pointer" : ""} ${isHovered ? "blue-pulse" : ""}`}
              style={
                isSelected
                  ? {
                      transform: "scale(1.1) translateZ(80px) translateY(-40px)",
                      boxShadow: "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
                    }
                  : { transform: `scale(1) translateZ(0) translateY(0)${!card?.isActive ? " rotateZ(90deg)" : ""}` }
              }
            >
              <Card card={card} size={CardSize.MD} />
            </div>
          )
        );
      })}
    </div>
  );
}
