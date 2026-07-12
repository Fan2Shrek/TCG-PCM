"use client";

import { GameContext } from "@/contexts/GameContext";
import type { GameAnnouncement } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import { useCallback, useContext, useEffect, useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { toast } from "sonner";
import GameMainArea from "./GameMainArea";
import GameAnnouncements from "./GameAnnouncements";
import CardsHand from "../CardsHand";
import type { BasicCard } from "@/lib/cards/types/card";
import { useRoom } from "@/contexts/RoomContext";
import { RoomStatus } from "@/types/roomStatus";
import api from "@/lib/api/api";
import WinScreen from "./WinScreen";
import MobileGameDisclaimer from "@/components/molecules/game/MobileGameDisclaimer";
import Tooltip from "@/components/molecules/game/tooltip";
import GameActionButtons from "@/components/molecules/game/GameActionButtons";

export default function GameBoard() {
  const router = useRouter();
  const { id } = useParams();
  const { game, getCardById, announcements, actions, currentUsername } =
    useContext(GameContext);
  const { userRoom, clearRoom, lastEvent } = useRoom();
  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(
    null,
  );

  const [isHandHovered, setIsHandHovered] = useState(false);
  const [draggedCard, setDraggedCard] = useState<BasicCard | null>(null);
  const [hoveredTargetId, setHoveredTargetId] = useState<string | null>(null);
  const [pendingPlayCardId, setPendingPlayCardId] = useState<string | null>(
    null,
  );
  const [isMobileDevice, setIsMobileDevice] = useState(false);
  const [winnerId, setWinnerId] = useState<string | null>(null);

  const connectedPlayer =
    game?.player1.player.name === currentUsername
      ? game?.player1.player
      : (game?.player2.player ?? null);
  const isLoggedPlayerTurn = Boolean(
    connectedPlayer && game && connectedPlayer.id === game.currentPlayerId,
  );
  const currentState =
    game?.player1.player.name === currentUsername
      ? game?.player1
      : (game?.player2 ?? null);
  const opponentState =
    game?.player1.player.name === currentUsername
      ? (game?.player2 ?? null)
      : (game?.player1 ?? null);
  const currentCoins = currentState?.coins ?? 0;

  useEffect(() => {
    const mediaQuery = window.matchMedia(
      "(max-width: 1024px), (pointer: coarse)",
    );

    const updateDeviceType = () => {
      setIsMobileDevice(mediaQuery.matches);
    };

    updateDeviceType();
    mediaQuery.addEventListener("change", updateDeviceType);

    return () => {
      mediaQuery.removeEventListener("change", updateDeviceType);
    };
  }, []);

  useEffect(() => {
    if (userRoom && currentUsername && userRoom.id !== id) {
      router.push("/rooms");
      toast.error("Vous n'avez pas accès à cette partie");
    }
  }, [userRoom, id, currentUsername, router]);

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
      if (pendingPlayCardId) {
        actions.playCard(pendingPlayCardId, { target: targetId });
        setPendingPlayCardId(null);
        setHoveredTargetId(null);
        setSelectedAttackerId(null);
        return;
      }

      if (!selectedAttackerId) {
        return;
      }

      actions.attack(selectedAttackerId, targetId);
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
    },
    [actions, pendingPlayCardId, selectedAttackerId],
  );

  const cancelPendingPlayTarget = useCallback(() => {
    setPendingPlayCardId(null);
    setHoveredTargetId(null);
  }, []);

  // Gère la sélection de cibles
  useEffect(() => {
    const handleTargetHover = (targetId: string) => {
      setHoveredTargetId(targetId);
    };

    const handleTargetLeave = () => {
      setHoveredTargetId(null);
    };

    const handleTargetClick = (targetId: string) => {
      if (selectedAttackerId || pendingPlayCardId) {
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
  }, [selectedAttackerId, pendingPlayCardId, handleAttackTarget]);

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

      const cost = card.cost ?? 0;

      if (currentCoins < cost) {
        actions.pushAnnouncement({
          text: "Vous n'avez pas assez de pièces pour jouer cette carte.",
          tone: "negative",
        });
        return;
      }

      if (card.requiresTarget) {
        setPendingPlayCardId(cardId);
        setSelectedAttackerId(null);
        setHoveredTargetId(null);
        actions.pushAnnouncement({
          text: "Choisissez une cible pour cette carte.",
          tone: "neutral",
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
    if (selectedAttackerId || pendingPlayCardId) {
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
      setPendingPlayCardId(null);
    }
  };

  const isTargeting = selectedAttackerId !== null || pendingPlayCardId !== null;

  useEffect(() => {
    emitter.emit("game:targeting-changed", isTargeting);

    return () => {
      emitter.emit("game:targeting-changed", false);
    };
  }, [isTargeting]);

  const handCards = useMemo(() => {
    if (!currentState) {
      return [] as BasicCard[];
    }

    return currentState.hand
      .map((cardId: string) => getCardById(cardId))
      .filter((card): card is BasicCard => Boolean(card));
  }, [currentState, getCardById]);

  const winner = useMemo(() => {
    if (!winnerId || !currentState || !opponentState) {
      return null;
    }

    if (winnerId === currentState.player.id) {
      return currentState.player.name;
    }

    if (winnerId === opponentState.player.id) {
      return opponentState.player.name;
    }

    return "Joueur";
  }, [winnerId, currentState, opponentState]);

  const isGameFinished = winner !== null;

  const fetchWinnerFromRoom = useCallback(() => {
    if (!id || !currentState || !opponentState) {
      return;
    }

    api.room
      .getById(id as string)
      .then((room) => {
        if (room.status === RoomStatus.FINISHED) {
          setWinnerId(room.winnerId ?? null);
        }
      })
      .catch((error) => {
        console.error("Failed to fetch room by id:", error);
      });
  }, [id, currentState, opponentState]);

  // Fetch once on first render to handle page refresh after game end.
  useEffect(() => {
    if (!id || !currentState || !opponentState) {
      return;
    }

    fetchWinnerFromRoom();
  }, [fetchWinnerFromRoom]);

  useEffect(() => {
    if (lastEvent !== "game_finished") {
      return;
    }

    clearRoom();
    fetchWinnerFromRoom();
  }, [lastEvent, fetchWinnerFromRoom]);

  const handleForfeit = useCallback(async () => {
    if (!userRoom?.id) {
      router.push("/");
      return;
    }

    await api.room.leave(userRoom.id);
  }, [userRoom?.id, clearRoom, router]);

  const handleBackHome = useCallback(async () => {
    clearRoom();
    router.push("/");
  }, [userRoom?.id, clearRoom, router]);

  if (!game || !connectedPlayer || !currentState || !opponentState) {
    return <div>Loading...</div>;
  }

  return (
    <div
      className="relative flex flex-col h-screen bg-orange-800 text-white overflow-hidden"
      onClick={handleBackgroundClick}
    >
      {isGameFinished && (
        <WinScreen
          winnerName={winner}
          userName={connectedPlayer.name}
          onBackHome={handleBackHome}
        />
      )}

      <MobileGameDisclaimer isVisible={isMobileDevice} />
      <div className="top-5 right-5 absolute z-20">
        <Tooltip
          text="Pour gagner, vous devez réduire les points de vie de la carte personnage adverse à 0. À chaque tour, vous piochez une carte et gagnez de l'or. 
        L'or sert à jouer vos cartes. Certaines cartes peuvent infliger des status: Hacké change les valeurs d'une carte, une carte Tordu n'activera parfois pas sons effet, et Boost de puissance augmente ses dégâts. 
        Pour cibler une carte avec une des vôtres, cliquez d'abord sur votre carte puis sur la cible. Vous pouvez aussi double-cliquer sur une carte pour l'afficher en grand. Cliquez en dehors de la carte pour dézoomer."
        />
      </div>

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
      {!winner && (
        <div className="absolute bottom-10 right-10 z-20">
          <GameActionButtons
            isLoggedPlayerTurn={isLoggedPlayerTurn}
            showCancel={pendingPlayCardId !== null}
            onCancel={cancelPendingPlayTarget}
            onEndTurn={actions.endTurn}
            onForfeit={handleForfeit}
          />
        </div>
      )}
    </div>
  );
}
