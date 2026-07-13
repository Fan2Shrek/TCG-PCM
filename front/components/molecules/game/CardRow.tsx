"use client";

import { memo, useContext, useEffect, useRef, useState } from "react";
import { GameContext } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import GameCard from "./GameCard";

type CardRowProps = {
  cardIds: string[];
  className?: string;
  isLoggedPlayerSide?: boolean;
};

const CARD_PLAY_ANIMATION_TIME = 200;

function CardRow({
  cardIds,
  className,
  isLoggedPlayerSide = false,
}: CardRowProps) {
  const { getCardById, targeting } = useContext(GameContext);
  const { isTargeting } = targeting;
  const [playingCardIds, setPlayingCardIds] = useState<Set<string>>(new Set());
  const timersRef = useRef<Map<string, ReturnType<typeof setTimeout>>>(new Map());

  useEffect(() => {
    const handleCardPlayed = (event: { card: { instanceId: string } }) => {
      const playedId = event.card.instanceId;

      setPlayingCardIds((prev) => new Set(prev).add(playedId));

      const existingTimer = timersRef.current.get(playedId);
      if (existingTimer) {
        clearTimeout(existingTimer);
      }

      const timeoutId = setTimeout(() => {
        setPlayingCardIds((prev) => {
          const next = new Set(prev);
          next.delete(playedId);
          return next;
        });
        timersRef.current.delete(playedId);
      }, CARD_PLAY_ANIMATION_TIME);

      timersRef.current.set(playedId, timeoutId);
    };

    emitter.on("card:played", handleCardPlayed);

    return () => {
      emitter.off("card:played", handleCardPlayed);
      for (const timeoutId of timersRef.current.values()) {
        clearTimeout(timeoutId);
      }
      timersRef.current.clear();
    };
  }, []);

  return (
    <div className={`flex flex-wrap justify-center gap-2 ${className}`}>
      {cardIds.map((cardId) => {
        const card = getCardById(cardId);
        const canSelect = isLoggedPlayerSide && card?.isActive && !isTargeting;
        const isPlaying = playingCardIds.has(card?.instanceId ?? "");

        return (
          card && (
            <GameCard
              key={card.instanceId}
              card={card}
              targetId={card.instanceId}
              canSelectSource={canSelect}
              disableSelfTarget
              isPlaying={isPlaying}
              isOpponentSideForPlayAnimation={!isLoggedPlayerSide}
              rowCardCount={cardIds.length}
            />
          )
        );
      })}
    </div>
  );
}

export default memo(CardRow);
