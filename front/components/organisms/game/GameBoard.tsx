import { GameContext } from "@/contexts/GameContext";
import type { GameAnnouncement } from "@/contexts/GameContext";
import { getCurrentUser } from "@/lib/utils";
import { emitter } from "@/lib/eventBus";
import { useCallback, useContext, useEffect, useMemo, useState } from "react";
import GameMainArea from "./GameMainArea";
import GameAnnouncements from "./GameAnnouncements";
import CardsHand from "../CardsHand";
import type { BasicCard } from "@/lib/cards/types/card";

export default function GameBoard() {
  const { game, getCardById, announcements, actions } = useContext(GameContext);
  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(
    null,
  );
  const [isHandHovered, setIsHandHovered] = useState(false);
  const [draggedCard, setDraggedCard] = useState<BasicCard | null>(null);
  const [hoveredTargetId, setHoveredTargetId] = useState<string | null>(null);

  const connectedPlayer =
    game?.player1.player.name === getCurrentUser()?.username
      ? game?.player1.player
      : (game?.player2.player ?? null);
  const isLoggedPlayerTurn = Boolean(
    connectedPlayer && game && connectedPlayer.id === game.currentPlayerId,
  );
  const currentState =
    game?.player1.player.name === getCurrentUser()?.username
      ? game?.player1
      : (game?.player2 ?? null);
  const opponentState =
    game?.player1.player.name === getCurrentUser()?.username
      ? (game?.player2 ?? null)
      : (game?.player1 ?? null);
  const currentCoins = currentState?.coins ?? 0;

  const giantAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation === "giant",
  );
  const giantAnnouncement =
    giantAnnouncements[giantAnnouncements.length - 1] ?? null;
  const regularAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation !== "giant",
  );

  // Gère le drag et drop des cartes
  useEffect(() => {
    const handleDragStart = ({ card }: { card: BasicCard }) => {
      if (!isLoggedPlayerTurn) return;
      setDraggedCard(card);
      setSelectedAttackerId(null);
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
  }, [isLoggedPlayerTurn]);

  const handleAttackTarget = useCallback(
    (targetId: string) => {
      if (!selectedAttackerId) {
        return;
      }

      actions.attack(selectedAttackerId, targetId);
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
    },
    [actions, selectedAttackerId],
  );

  // Gère la sélection de cibles
  useEffect(() => {
    const handleTargetHover = (targetId: string) => {
      setHoveredTargetId(targetId);
    };

    const handleTargetLeave = () => {
      setHoveredTargetId(null);
    };

    const handleTargetClick = (targetId: string) => {
      if (selectedAttackerId) {
        handleAttackTarget(targetId);
      }
    };

    emitter.on("target:hover", handleTargetHover);
    emitter.on("target:leave", handleTargetLeave);
    emitter.on("target:click", handleTargetClick);

    return () => {
      emitter.off("target:hover", handleTargetHover);
      emitter.off("target:leave", handleTargetLeave);
      emitter.off("target:click", handleTargetClick);
    };
  }, [selectedAttackerId, handleAttackTarget]);

  // Gère quand carte laché dans zone de jeu
  useEffect(() => {
    const handleCardDropped = (data: {
      card: { instanceId: string };
      zoneId?: string;
    }) => {
      const cardId = data.card.instanceId;
      const card = getCardById(cardId);

      if (!card || !data.zoneId) {
        return;
      }

      const cost = (card as BasicCard & { cost?: number }).cost ?? 0;

      if (currentCoins < cost) {
        actions.pushAnnouncement({
          text: "Not enough coins",
          tone: "negative",
        });
        return;
      }

      actions.playCard(cardId);
    };

    emitter.on("card:dropped", handleCardDropped);

    return () => emitter.off("card:dropped", handleCardDropped);
  }, [getCardById, actions, currentCoins]);

  const selectedAttackerCard = useMemo(() => {
    if (!selectedAttackerId) return undefined;

    const card = getCardById(selectedAttackerId);

    if (card?.isActive === false) {
      return undefined;
    }

    return card;
  }, [getCardById, selectedAttackerId]);

  const handleSelectAttacker = (cardId: string | null) => {
    if (!isLoggedPlayerTurn) return;

    if (selectedAttackerId === cardId) {
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
      return;
    }

    setHoveredTargetId(null);
    setSelectedAttackerId(cardId);
  };

  const cardHandPositionClass = isHandHovered ? "bottom-0" : "-bottom-30";

  const handleBackgroundClick = () => {
    if (selectedAttackerId) {
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
    }
  };

  const handCards = useMemo(() => {
    if (!currentState) {
      return [] as BasicCard[];
    }

    return currentState.hand
      .map((cardId: string) => getCardById(cardId))
      .filter((card): card is BasicCard => Boolean(card));
  }, [currentState, getCardById]);

  if (!game || !connectedPlayer || !currentState || !opponentState) {
    return <div>Loading...</div>;
  }

  return (
    <div
      className="relative flex flex-col h-screen bg-orange-800 text-white overflow-hidden"
      onClick={handleBackgroundClick}
    >
      <GameAnnouncements
        regularAnnouncements={regularAnnouncements}
        giantAnnouncement={giantAnnouncement}
        selectedAttackerId={selectedAttackerId}
      />
      <div className="h-full flex flex-row justify-center items-center pointer-events-auto">
        <GameMainArea
          selectedAttackerId={selectedAttackerId}
          onSelectAttacker={handleSelectAttacker}
          onSelectTarget={handleAttackTarget}
          selectedAttackerCard={selectedAttackerCard}
          getCardById={getCardById}
          game={game}
          opponentState={opponentState}
          currentState={currentState}
          isCardDragged={!!draggedCard}
          hoveredTargetId={hoveredTargetId}
        />
      </div>
      <div
        className={`absolute ${cardHandPositionClass} left-1/2 -translate-x-1/2 p-4 z-10 transition-all ease-in-out duration-100`}
      >
        <CardsHand
          cards={handCards}
          onMouseEnter={() => setIsHandHovered(true)}
          onMouseLeave={() => setIsHandHovered(false)}
          isDisabled={!isLoggedPlayerTurn}
        />
      </div>
      {isLoggedPlayerTurn && (
        <button
          className="absolute bottom-10 right-10 bg-red-500 text-white px-8 py-2 rounded text-xl cursor-pointer"
          onClick={actions.endTurn}
        >
          End turn
        </button>
      )}
    </div>
  );
}
