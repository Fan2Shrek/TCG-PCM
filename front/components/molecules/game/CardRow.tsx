"use client";

import { memo, useContext, useState, useEffect } from "react";
import { GameContext } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import GameCard from "./GameCard";

type CardRowProps = {
  cardIds: string[];
  className?: string;
  isLoggedPlayerSide?: boolean;
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

function CardRow({
  cardIds,
  className,
  isLoggedPlayerSide = false,
}: CardRowProps) {
  const { getCardById, targeting } = useContext(GameContext);
  const { selectedAttackerId, isTargeting } = targeting;
  const playingCardIds = useCardPlayAnimation();

  return (
    <div
      className={`flex flex-wrap justify-center gap-2 ${className}`}
      style={{ perspective: "1000px" }}
    >
      {cardIds.map((cardId) => {
        const card = getCardById(cardId);
        const isSelected = selectedAttackerId === card?.instanceId;
        const canSelect = isLoggedPlayerSide && card?.isActive && !isTargeting;
        const isPlaying = playingCardIds.has(card?.instanceId || "");

        return (
          card && (
            <GameCard
              key={card.instanceId}
              card={card}
              targetId={card.instanceId}
              canSelectSource={canSelect}
              disableSelfTarget
              style={getCardStyle(
                isPlaying,
                isSelected,
                card?.isActive ?? true,
                !isLoggedPlayerSide,
              )}
            />
          )
        );
      })}
    </div>
  );
}

export default memo(CardRow);
