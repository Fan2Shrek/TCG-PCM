"use client";

import { BasicCard } from "@/lib/cards/types/card";
import useMercure from "@/hooks/useMercure";
import { GameEvent } from "@/lib/game/type/gameEvent";
import { GameState } from "@/lib/game/type/gameState";
import { playGameAction } from "@/lib/api/gameProxy";
import {
  AnnouncementPayload,
  AnnouncementTone,
  animateGameEvent,
  applyGameView,
} from "@/lib/game/gameEventReducer";

import {
  createContext,
  ReactNode,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { PlayerActionType } from "@/lib/game/type/playerAction";

export type { AnnouncementTone };

export type GameAnnouncement = {
  id: number;
} & AnnouncementPayload;

type ActionObject = {
  playCard: (cardId: string, data?: Record<string, unknown>) => void;
  attack: (cardId: string, targetId: string) => void;
  endTurn: () => void;
  pushAnnouncement: (announcement: AnnouncementPayload) => void;
};

export type TargetingState = {
  selectedAttackerId: string | null;
  hoveredTargetId: string | null;
  pendingPlayCardId: string | null;
  isTargeting: boolean;
};

export type TargetingActions = {
  selectAttacker: (cardId: string | null) => void;
  clearSelectedAttacker: () => void;
  hoverTarget: (targetId: string | null) => void;
  requestCardTarget: (cardId: string) => void;
  handleTargetClick: (targetId: string) => void;
  cancelPendingCardTarget: () => void;
  clearAllTargeting: () => void;
};

type GameContextType = {
  game: GameState | null;
  getCardById: (cardId: string) => BasicCard | undefined;
  announcements: GameAnnouncement[];
  actions: ActionObject;
  currentUsername?: string;
  isLoggedPlayerTurn: boolean;
  targeting: TargetingState;
  targetingActions: TargetingActions;
};

type Props = {
  children: ReactNode;
  gameId: string;
  game?: GameState | null;
  username?: string;
  mercureToken?: string;
};

export const GameContext = createContext<GameContextType>({
  game: null,
  getCardById: () => undefined,
  announcements: [],
  actions: {
    playCard: () => undefined,
    attack: () => undefined,
    endTurn: () => undefined,
    pushAnnouncement: () => undefined,
  },
  isLoggedPlayerTurn: false,
  targeting: {
    selectedAttackerId: null,
    hoveredTargetId: null,
    pendingPlayCardId: null,
    isTargeting: false,
  },
  targetingActions: {
    selectAttacker: () => undefined,
    clearSelectedAttacker: () => undefined,
    hoverTarget: () => undefined,
    requestCardTarget: () => undefined,
    handleTargetClick: () => undefined,
    cancelPendingCardTarget: () => undefined,
    clearAllTargeting: () => undefined,
  },
});

export const GameProvider = ({
  children,
  gameId,
  game: initialGame,
  username,
  mercureToken,
}: Props) => {
  useEffect(() => {
    if (!mercureToken) return;

    document.cookie = `mercureAuthorization=${mercureToken}; path=/; max-age=3600; secure; samesite=strict`;
  }, [mercureToken]);

  const normalizeGameState = useCallback(
    (state: GameState | null | undefined) => {
      if (!state) {
        return null;
      }

      const legacyCurrentPlayer = (
        state as GameState & { currentPlayer?: string | number }
      ).currentPlayer;

      const normalized = {
        ...state,
        currentPlayerId:
          state.currentPlayerId ||
          (legacyCurrentPlayer !== undefined
            ? String(legacyCurrentPlayer)
            : ""),
      } as GameState;

      return normalized;
    },
    [],
  );

  const [game, setGame] = useState<GameState | null>(
    normalizeGameState(initialGame),
  );
  const [announcements, setAnnouncements] = useState<GameAnnouncement[]>([]);
  const gameRef = useRef<GameState | null>(normalizeGameState(initialGame));
  const announcementIdRef = useRef(0);
  const timeoutRefs = useRef<number[]>([]);

  const pushAnnouncement = useCallback((announcement: AnnouncementPayload) => {
    const id = ++announcementIdRef.current;

    setAnnouncements((current: GameAnnouncement[]) => [
      ...current,
      { id, ...announcement },
    ]);

    const timeoutId = window.setTimeout(() => {
      setAnnouncements((current: GameAnnouncement[]) =>
        current.filter(
          (announcement: GameAnnouncement) => announcement.id !== id,
        ),
      );

      timeoutRefs.current = timeoutRefs.current.filter(
        (currentTimeoutId: number) => currentTimeoutId !== timeoutId,
      );
    }, 2200);

    timeoutRefs.current.push(timeoutId);
  }, []);

  useEffect(() => {
    return () => {
      timeoutRefs.current.forEach((timeoutId: number) =>
        window.clearTimeout(timeoutId),
      );
      timeoutRefs.current = [];
    };
  }, []);

  const getCardById = useCallback(
    (cardId: string): BasicCard | undefined => {
      if (!game) {
        return undefined;
      }

      return game.cards[cardId] as unknown as BasicCard | undefined;
    },
    [game],
  );

  const playCard = useCallback(
    async (cardId: string, data: Record<string, unknown> = {}) => {
      try {
        await playGameAction(gameId, PlayerActionType.PLAY_CARD, {
          cardId,
          data,
        });
      } catch (error) {
        const message =
          error instanceof Error ? error.message : "Une erreur est survenue";

        pushAnnouncement({
          text: message,
          tone: "negative",
        });
      }
    },
    [gameId, pushAnnouncement],
  );

  const attack = useCallback(
    (cardId: string, targetId: string) => {
      playGameAction(gameId, PlayerActionType.ATTACK, { cardId, targetId });
    },
    [gameId],
  );

  const endTurn = useCallback(() => {
    playGameAction(gameId, PlayerActionType.END_TURN);
  }, [gameId]);

  const isLoggedPlayerTurn = useMemo(() => {
    if (!game || !username) return false;

    const connectedPlayerId =
      game.player1.player.name === username
        ? game.player1.player.id
        : game.player2.player.id;

    return connectedPlayerId === game.currentPlayerId;
  }, [game, username]);

  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(
    null,
  );
  const [hoveredTargetId, setHoveredTargetId] = useState<string | null>(null);
  const [pendingPlayCardId, setPendingPlayCardId] = useState<string | null>(
    null,
  );

  const isTargeting = selectedAttackerId !== null || pendingPlayCardId !== null;

  const selectAttacker = useCallback(
    (cardId: string | null) => {
      if (!isLoggedPlayerTurn) return;

      setSelectedAttackerId((current) => (current === cardId ? null : cardId));
      setHoveredTargetId(null);
    },
    [isLoggedPlayerTurn],
  );

  const clearSelectedAttacker = useCallback(() => {
    setSelectedAttackerId(null);
  }, []);

  const hoverTarget = useCallback((targetId: string | null) => {
    setHoveredTargetId(targetId);
  }, []);

  const requestCardTarget = useCallback((cardId: string) => {
    setPendingPlayCardId(cardId);
    setSelectedAttackerId(null);
    setHoveredTargetId(null);
  }, []);

  const handleTargetClick = useCallback(
    (targetId: string) => {
      if (pendingPlayCardId) {
        playCard(pendingPlayCardId, { target: targetId });
        setPendingPlayCardId(null);
        setHoveredTargetId(null);
        setSelectedAttackerId(null);
        return;
      }

      if (!selectedAttackerId) return;

      attack(selectedAttackerId, targetId);
      setHoveredTargetId(null);
      setSelectedAttackerId(null);
    },
    [pendingPlayCardId, selectedAttackerId, playCard, attack],
  );

  const cancelPendingCardTarget = useCallback(() => {
    setPendingPlayCardId(null);
    setHoveredTargetId(null);
  }, []);

  const clearAllTargeting = useCallback(() => {
    setHoveredTargetId(null);
    setSelectedAttackerId(null);
    setPendingPlayCardId(null);
  }, []);

  const playerNumber = useMemo(() => {
    if (!initialGame || !username) return null;

    return username === initialGame.player1.player.name ? "1" : "2";
  }, [initialGame, username]);

  const mercureUrl = playerNumber
    ? `${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${gameId}&topic=game/${gameId}-${playerNumber}`
    : `${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${gameId}`;

  useMercure(mercureUrl, {
    game_events: (e: { events: GameEvent[] }) => {
      const previousGame = gameRef.current;

      if (!previousGame) {
        return;
      }

      let next = { ...previousGame };

      for (const event of e.events) {
        const previous = next;
        const announcement = animateGameEvent(previous, event);

        if (announcement) {
          pushAnnouncement(announcement);
        }

        next = applyGameView(next, event, username);
      }

      const normalizedNext = normalizeGameState(next);

      gameRef.current = normalizedNext;
      setGame(normalizedNext);
    },
  });

  useEffect(() => {
    gameRef.current = normalizeGameState(game);
  }, [game, normalizeGameState]);

  return (
    <GameContext.Provider
      value={{
        game,
        getCardById,
        announcements,
        actions: { playCard, attack, endTurn, pushAnnouncement },
        currentUsername: username,
        isLoggedPlayerTurn,
        targeting: {
          selectedAttackerId,
          hoveredTargetId,
          pendingPlayCardId,
          isTargeting,
        },
        targetingActions: {
          selectAttacker,
          clearSelectedAttacker,
          hoverTarget,
          requestCardTarget,
          handleTargetClick,
          cancelPendingCardTarget,
          clearAllTargeting,
        },
      }}
    >
      {children}
    </GameContext.Provider>
  );
};
