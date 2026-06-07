import { GameContext } from "@/contexts/GameContext";
import type { GameAnnouncement } from "@/contexts/GameContext";
import { getCurrentUser } from "@/lib/utils";
import { emitter } from "@/lib/eventBus";
import { useContext, useEffect, useMemo, useRef, useState } from "react";
import GameMainArea from "./GameMainArea";
import GameAnnouncements from "./GameAnnouncements";
import CardsHand from "../CardsHand";
import { CardWithPosition } from "@/lib/cards/types/card";
import PlayerStatsDisplay from "@/components/molecules/game/PlayerStatsDisplay";

export default function GameBoard() {
  const { game, getCardById, announcements, actions } = useContext(GameContext);
  const playBoxRef = useRef<HTMLDivElement>(null);
  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(
    null,
  );
  const [isHandHovered, setIsHandHovered] = useState(false);
  const [draggedCard, setDraggedCard] = useState<CardWithPosition | null>(null);

  const giantAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation === "giant",
  );
  const giantAnnouncement =
    giantAnnouncements[giantAnnouncements.length - 1] ?? null;
  const regularAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation !== "giant",
  );

  useEffect(() => {
    const handler = (data: { id: string }) => {
      const rect = playBoxRef.current?.getBoundingClientRect();

      if (!rect) {
        return;
      }

      const isInside = true;

      isInside && actions.playCard(data.id);
    };

    emitter.on("card:played", handler);

    return () => emitter.off("card:played", handler);
  }, [actions]);

  useEffect(() => {
    const handleDragStart = ({ card }: { card: CardWithPosition }) => {
      setDraggedCard(card);
    };
    const handleDragEnd = () => {
      setDraggedCard(null);
    };

    emitter.on("card:drag:start", handleDragStart);
    emitter.on("card:drag:end", handleDragEnd);

    return () => {
      emitter.off("card:drag:start", handleDragStart);
      emitter.off("card:drag:end", handleDragEnd);
    };
  }, []);

  useEffect(() => {
    const handleCardDropped = (data: {
      card: { instanceId: string };
      zoneId: string;
    }) => {
      const cardId = data.card.instanceId;
      const card = getCardById(cardId);

      if (!card) {
        return;
      }

      // if (currentState.coins >= (card.cost || 0)) {
      actions.playCard(cardId);
      // }
    };

    emitter.on("card:dropped", handleCardDropped);

    return () => emitter.off("card:dropped", handleCardDropped);
  }, [getCardById, actions]);

  if (!game) {
    return <div>Loading...</div>;
  }

  const connectedPlayer =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player1.player.id
      : game.player2.player.id;

  const currentState =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player1
      : game.player2;
  const opponentState =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player2
      : game.player1;
  const selectedAttackerCard = useMemo(
    () => (selectedAttackerId ? getCardById(selectedAttackerId) : undefined),
    [getCardById, selectedAttackerId],
  );

  useEffect(() => {
    if (!selectedAttackerId) {
      return;
    }

    if (selectedAttackerCard?.isActive === false) {
      setSelectedAttackerId(null);
    }
  }, [selectedAttackerCard, selectedAttackerId]);

  const handleSelectAttacker = (cardId: string) => {
    if (selectedAttackerId === cardId) {
      setSelectedAttackerId(null);
      return;
    }

    setSelectedAttackerId(cardId);
  };

  const handleAttackTarget = (targetId: string) => {
    if (!selectedAttackerId) {
      return;
    }

    actions.attack(selectedAttackerId, targetId);
    setSelectedAttackerId(null);
  };

  const cardHandSize = draggedCard ? "sm" : isHandHovered ? "md" : "sm";
  const cardHandPositionClass = isHandHovered ? "bottom-0" : "-bottom-20";

  return (
    <div className="relative flex flex-col h-screen bg-green-900 text-white overflow-hidden">
      <GameAnnouncements
        regularAnnouncements={regularAnnouncements}
        giantAnnouncement={giantAnnouncement}
        selectedAttackerId={selectedAttackerId}
      />

      <div className="h-full flex flex-row justify-center items-center pointer-events-auto">
        <GameMainArea
          game={game}
          selectedAttackerId={selectedAttackerId}
          onSelectAttacker={handleSelectAttacker}
          onSelectTarget={handleAttackTarget}
          getCardById={getCardById}
          opponentState={opponentState}
          currentState={currentState}
          selectedAttackerCard={selectedAttackerCard}
          isCardDragged={!!draggedCard}
        />
      </div>
      <div
        className={`absolute ${cardHandPositionClass} left-1/2 -translate-x-1/2 p-4 transition-all z-10`}
      >
        <CardsHand
          cards={currentState.hand.map((cardId: string) => getCardById(cardId))}
          cardSize={cardHandSize}
          onMouseEnter={() => setIsHandHovered(true)}
          onMouseLeave={() => setIsHandHovered(false)}
        />
      </div>

      <div className="absolute left-10 bottom-10">
        <PlayerStatsDisplay
          money={currentState.coins}
          health={currentState.healthPoints}
        />
      </div>

      {connectedPlayer == game.currentPlayer.id && (
        <button
          className="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded"
          onClick={actions.endTurn}
        >
          end
        </button>
      )}
    </div>
  );
}
