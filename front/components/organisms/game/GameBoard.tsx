import { GameContext } from "@/contexts/GameContext";
import type { GameAnnouncement } from "@/contexts/GameContext";
import { getCurrentUser } from "@/lib/utils";
import { emitter } from "@/lib/eventBus";
import { useContext, useEffect, useMemo, useState } from "react";
import GameMainArea from "./GameMainArea";
import GameAnnouncements from "./GameAnnouncements";
import CardsHand from "../CardsHand";
import { CardWithPosition } from "@/lib/cards/types/card";

export default function GameBoard() {
  const { game, getCardById, announcements, actions } = useContext(GameContext);
  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(null);
  const [isHandHovered, setIsHandHovered] = useState(false);
  const [draggedCard, setDraggedCard] = useState<CardWithPosition | null>(null);
  const [hoveredTargetId, setHoveredTargetId] = useState<string | null>(null);

  if (!game) {
    return <div>Loading...</div>;
  }

  const connectedPlayer = game.player1.player.name === getCurrentUser()?.username ? game.player1.player : game.player2.player;
  const isLoggedPlayerTurn = connectedPlayer.id === game.currentPlayer;
  const currentState = game.player1.player.name === getCurrentUser()?.username ? game.player1 : game.player2;
  const opponentState = game.player1.player.name === getCurrentUser()?.username ? game.player2 : game.player1;

  const giantAnnouncements = announcements.filter((announcement: GameAnnouncement) => announcement.presentation === "giant");
  const giantAnnouncement = giantAnnouncements[giantAnnouncements.length - 1] ?? null;
  const regularAnnouncements = announcements.filter((announcement: GameAnnouncement) => announcement.presentation !== "giant");

  // Gère le drag et drop des cartes
  useEffect(() => {
    const handleDragStart = ({ card }: { card: CardWithPosition }) => {
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

  // Gère la sélection de cibles
  useEffect(() => {
    if (!selectedAttackerId) {
      setHoveredTargetId(null);
      return;
    }

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
  }, [selectedAttackerId]);

  // Gère quand carte laché dans zone de jeu
  useEffect(() => {
    const handleCardDropped = (data: { card: { instanceId: string }; zoneId?: string }) => {
      const cardId = data.card.instanceId;
      const card = getCardById(cardId);

      if (!card || !data.zoneId) {
        return;
      }

      const cost = card.cost || 0;

      if (currentState.coins < cost) {
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
  }, [getCardById, actions, currentState.coins]);

  const selectedAttackerCard = useMemo(() => {
    if (!selectedAttackerId) return undefined;

    const card = getCardById(selectedAttackerId);

    if (card?.isActive === false) {
      return undefined;
    }

    return card;
  }, [getCardById, selectedAttackerId]);

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

  const cardHandPositionClass = isHandHovered ? "bottom-0" : "-bottom-30";

  const handleBackgroundClick = () => {
    if (selectedAttackerId) {
      setSelectedAttackerId(null);
    }
  };

  const handleSelectAttackerWithTurnCheck = (cardId: string) => {
    if (!isLoggedPlayerTurn) return;
    handleSelectAttacker(cardId);
  };

  return (
    <div className='relative flex flex-col h-screen bg-green-900 text-white overflow-hidden' onClick={handleBackgroundClick}>
      <GameAnnouncements regularAnnouncements={regularAnnouncements} giantAnnouncement={giantAnnouncement} selectedAttackerId={selectedAttackerId} />

      <div className='h-full flex flex-row justify-center items-center pointer-events-auto'>
        <GameMainArea selectedAttackerId={selectedAttackerId} onSelectAttacker={handleSelectAttackerWithTurnCheck} onSelectTarget={handleAttackTarget} selectedAttackerCard={selectedAttackerCard} getCardById={getCardById} game={game} opponentState={opponentState} currentState={currentState} isCardDragged={!!draggedCard} hoveredTargetId={hoveredTargetId} />
      </div>
      <div className={`absolute ${cardHandPositionClass} left-1/2 -translate-x-1/2 p-4 z-10 transition-all ease-in-out duration-100`}>
        <CardsHand cards={currentState.hand.map((cardId: string) => getCardById(cardId))} onMouseEnter={() => setIsHandHovered(true)} onMouseLeave={() => setIsHandHovered(false)} isDisabled={!isLoggedPlayerTurn} />
      </div>

      {connectedPlayer.id == game.currentPlayer && (
        <button className='absolute bottom-10 right-10 bg-red-500 text-white px-8 py-2 rounded text-xl cursor-pointer' onClick={actions.endTurn}>
          End turn
        </button>
      )}
    </div>
  );
}
