"use client";

import { GameContext } from "@/contexts/GameContext";
import type { GameAnnouncement } from "@/contexts/GameContext";
import { emitter } from "@/lib/eventBus";
import {
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
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
import Tooltip from "@/components/molecules/game/tooltip";
import GameActionButtons from "@/components/molecules/game/GameActionButtons";
import GameChat from "./GameChat";
import { useBoosterTokensContext } from "@/contexts/BoosterTokensContext";

export default function GameBoard() {
  const router = useRouter();
  const { id } = useParams();
  const {
    game,
    getCardById,
    announcements,
    actions,
    currentUsername,
    isLoggedPlayerTurn,
    targeting,
    targetingActions,
  } = useContext(GameContext);
  const { userRoom, clearRoom, lastEvent } = useRoom();

  const [isHandHovered, setIsHandHovered] = useState(false);
  const [draggedCard, setDraggedCard] = useState<BasicCard | null>(null);
  const [winnerId, setWinnerId] = useState<string | null>(null);
  const rewardedWinnerIdRef = useRef<string | null>(null);
  const { refresh: refreshBoosterTokens } = useBoosterTokensContext();

  const connectedPlayer =
    game?.player1.player.name === currentUsername
      ? game?.player1.player
      : (game?.player2.player ?? null);
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
      targetingActions.clearSelectedAttacker();
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
  }, [isLoggedPlayerTurn, targetingActions]);

  const resolvePlayCard = useCallback(
    (cardId: string) => {
      const card = getCardById(cardId);

      if (!card) {
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
        targetingActions.requestCardTarget(cardId);
        actions.pushAnnouncement({
          text: "Choisissez une cible pour cette carte.",
          tone: "neutral",
        });
        return;
      }

      actions.playCard(cardId);
    },
    [getCardById, currentCoins, actions, targetingActions],
  );

  // Gère quand carte laché dans zone de jeu
  useEffect(() => {
    const handleCardDropped = (data: {
      card: { instanceId: string };
      zoneId?: string;
    }) => {
      if (!data.zoneId) {
        return;
      }

      resolvePlayCard(data.card.instanceId);
    };

    emitter.on("card:dropped", handleCardDropped);

    return () => emitter.off("card:dropped", handleCardDropped);
  }, [resolvePlayCard]);

  const handlePlayZoneClick = useCallback(() => {
    if (!targeting.selectedHandCardId) {
      return;
    }

    const cardId = targeting.selectedHandCardId;
    targetingActions.selectHandCard(null);
    resolvePlayCard(cardId);
  }, [targeting.selectedHandCardId, targetingActions, resolvePlayCard]);

  const desktopCardHandPositionClass = isHandHovered
    ? "sm:bottom-0"
    : "sm:-bottom-30";

  const handleBackgroundClick = () => {
    if (
      targeting.selectedAttackerId ||
      targeting.pendingPlayCardId ||
      targeting.selectedHandCardId
    ) {
      targetingActions.clearAllTargeting();
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

  useEffect(() => {
    if (
      !winnerId ||
      rewardedWinnerIdRef.current === winnerId ||
      !connectedPlayer
    ) {
      return;
    }

    if (winnerId !== connectedPlayer.id) {
      return;
    }

    rewardedWinnerIdRef.current = winnerId;
    refreshBoosterTokens().catch(() => {});
  }, [winnerId, connectedPlayer, refreshBoosterTokens]);

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
  }, [userRoom, router]);

  const handleBackHome = useCallback(async () => {
    clearRoom();
    router.push("/");
  }, [clearRoom, router]);

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

      <div className="absolute top-4 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
        <div
          className={`rounded-full border px-5 py-1.5 text-sm font-semibold shadow-lg backdrop-blur-sm ${
            isLoggedPlayerTurn
              ? "border-emerald-300/60 bg-emerald-500/20 text-emerald-100"
              : "border-white/20 bg-black/30 text-white"
          }`}
        >
          {isLoggedPlayerTurn
            ? "À ton tour"
            : `Tour de ${opponentState.player.name}`}
        </div>
      </div>

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
      />
      <div className="h-full flex flex-row justify-center items-center pointer-events-auto">
        <GameMainArea
          opponentState={opponentState}
          currentState={currentState}
          isCardDragged={!!draggedCard}
          onPlayZoneClick={handlePlayZoneClick}
          isPlayZoneSelectable={targeting.selectedHandCardId !== null}
        />
      </div>
      <div
        className={`absolute -bottom-15 ${desktopCardHandPositionClass} left-1/2 -translate-x-1/2 p-4 z-10 transition-all ease-in-out duration-100`}
      >
        <CardsHand
          cards={handCards}
          onMouseEnter={() => setIsHandHovered(true)}
          onMouseLeave={() => setIsHandHovered(false)}
          isDisabled={!isLoggedPlayerTurn}
          selectedCardId={targeting.selectedHandCardId}
          onCardClick={(card) =>
            targetingActions.selectHandCard(card.instanceId)
          }
        />
      </div>
      {!winner && (
        <div className="absolute z-20 top-4 left-4 lg:top-auto lg:left-auto lg:bottom-10 lg:right-10">
          <GameActionButtons
            isLoggedPlayerTurn={isLoggedPlayerTurn}
            showCancel={
              targeting.pendingPlayCardId !== null ||
              targeting.selectedHandCardId !== null
            }
            onCancel={targetingActions.clearAllTargeting}
            onEndTurn={actions.endTurn}
            onForfeit={handleForfeit}
          />
        </div>
      )}
      <GameChat />
    </div>
  );
}
