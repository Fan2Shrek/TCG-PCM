"use client";

import { useContext, useState, useEffect } from "react";
import { CardSize } from "@/constants/card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";
import { GameContext } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import useTargetingMode from "@/hooks/useTargetingMode";

type CardRowProps = {
  cardIds: string[];
  className?: string;
  isLoggedPlayerSide?: boolean;
  selectedCardId?: string | null;
  onSelectCard?: (cardId: string | null) => void;
  onSelectTarget?: (cardId: string) => void;
  hoveredTargetId?: string | null;
};

const CARD_PLAY_ANIMATION_TIME = 300;

function useCardPlayAnimation() {
  const [playingCardIds, setPlayingCardIds] = useState<Set<string>>(new Set());

  useEffect(() => {
    const handleCardPlayed = (event: { card: { instanceId: string } }) => {
      setPlayingCardIds((prev) => new Set(prev).add(event.card.instanceId));

      setTimeout(() => {
        setPlayingCardIds((prev) => {
          const next = new Set(prev);
          next.delete(event.card.instanceId);
          return next;
        });
      }, CARD_PLAY_ANIMATION_TIME);
    };

    emitter.on("card:played", handleCardPlayed);
    return () => emitter.off("card:played", handleCardPlayed);
  }, []);

  return playingCardIds;
}

function getCardStyle(
  isPlaying: boolean,
  isSelected: boolean,
  isActive: boolean,
  isOpponentSide: boolean,
) {
  if (isPlaying) {
    const playOffset = isOpponentSide ? "-200px" : "200px";
    return {
      transform: `scale(1.1) translateZ(80px) translateY(${playOffset})`,
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
      transition: "transform 300ms ease-in",
    } as React.CSSProperties;
  }

  if (isSelected) {
    return {
      transform: "scale(1.1) translateZ(80px) translateY(-40px)",
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
    } as React.CSSProperties;
  }

  return {
    transform: `scale(1) translateZ(0) translateY(0)${!isActive ? " rotateZ(90deg)" : ""}`,
  } as React.CSSProperties;
}

export default function CardRow({
  cardIds,
  className,
  isLoggedPlayerSide = false,
  selectedCardId,
  onSelectCard,
  onSelectTarget,
  hoveredTargetId,
}: CardRowProps) {
  const isTargeting = useTargetingMode();
  const { getCardById } = useContext(GameContext);
  const playingCardIds = useCardPlayAnimation();
  const isControlled =
    selectedCardId !== undefined && onSelectCard !== undefined;

  return (
    <div
      className={`flex flex-wrap justify-center gap-2 ${className}`}
      style={{ perspective: "1000px" }}
    >
      {cardIds.map((cardId) => {
        const card = getCardById(cardId);
        const isSelected = selectedCardId === card?.instanceId;
        const isHovered =
          hoveredTargetId === card?.instanceId && isTargeting && !isSelected;
        const canSelect =
          isLoggedPlayerSide && isControlled && card?.isActive && !isTargeting;
        const isPlaying = playingCardIds.has(card?.instanceId || "");

        return (
          card && (
            <div
              key={card.instanceId}
              onClick={(e) => {
                e.stopPropagation();
                if (isTargeting) {
                  onSelectTarget?.(card.instanceId);
                } else if (canSelect) {
                  onSelectCard?.(isSelected ? null : card.instanceId);
                }
              }}
              onMouseEnter={() =>
                isTargeting && emitter.emit("target:hover", card.instanceId)
              }
              onMouseLeave={() => emitter.emit("target:leave")}
              className={`card-selected ${canSelect || isTargeting ? "cursor-pointer" : ""} ${isHovered ? "blue-pulse" : ""}`}
              style={getCardStyle(
                isPlaying,
                isSelected,
                card?.isActive ?? true,
                !isLoggedPlayerSide,
              )}
            >
              <CardWithZoom card={card} size={CardSize.MD} />
            </div>
          )
        );
      })}
    </div>
  );
}
