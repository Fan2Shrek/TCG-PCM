import { BasicCard } from '@/components/types/card';
import useMercure from '@/hook/useMercure';
import { GameEventType } from '@/lib/game/type/eventType';
import { GameEvent } from '@/lib/game/type/gameEvent';
import { GameState } from '@/lib/game/type/gameState';
import api from "@/lib/api/api";

import {
  createContext,
  ReactNode,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";
import { PlayerActionType } from '@/lib/game/type/playerAction';
import { getCurrentUser } from '@/lib/utils';

export type AnnouncementTone = "neutral" | "positive" | "negative";

export type GameAnnouncement = {
  id: number;
  text: string;
  tone: AnnouncementTone;
  presentation?: "normal" | "giant";
};

type AnnouncementPayload = Omit<GameAnnouncement, "id">;

type ActionObject = {
  playCard: (cardId: string) => void;
  attack: (cardId: string, targetId: string) => void;
  endTurn: () => void;
};

type GameContextType = {
  game: GameState | null;
  getCardById: (cardId: string) => BasicCard | undefined;
  announcements: GameAnnouncement[];
  actions: ActionObject;
};

type Props = {
  children: ReactNode;
  gameId: string;
  game?: GameState | null;
};

export const GameContext = createContext<GameContextType>();

export const GameProvider = ({ children, gameId, game: initialGame }: Props) => {
	const [game, setGame] = useState<GameState | null>(initialGame || null);
	const [announcements, setAnnouncements] = useState<GameAnnouncement[]>([]);
  const gameRef = useRef<GameState | null>(initialGame || null);
  const announcementIdRef = useRef(0);
  const timeoutRefs = useRef<ReturnType<typeof setTimeout>[]>([]);
	const currentUser = getCurrentUser();

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
        (currentTimeoutId: ReturnType<typeof setTimeout>) =>
          currentTimeoutId !== timeoutId,
      );
    }, 2200);

    timeoutRefs.current.push(timeoutId);
  }, []);

  useEffect(() => {
    return () => {
      timeoutRefs.current.forEach((timeoutId: ReturnType<typeof setTimeout>) =>
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

      return game.cards[cardId] as BasicCard | undefined;
    },
    [game],
  );

	const playCard = (cardId: string) => {
    api.game.play(gameId, PlayerActionType.PLAY_CARD, { cardId });
  };

	const attack = (cardId: string, targetId: string) => {
    api.game.play(gameId, PlayerActionType.END_TURN, { cardId, targetId });
  };

	const endTurn = () => {
    api.game.play(gameId, PlayerActionType.END_TURN);
  };

  const getPlayerKey = (
    state: GameState,
    playerId: string,
  ): "player1" | "player2" =>
    state.player1.player.id === playerId ? "player1" : "player2";

  const animate = (
    state: GameState,
    event: GameEvent,
  ): AnnouncementPayload | null => {
    if (event.type === GameEventType.DICE_ROLLED) {
	  if (!event.data.faces) return null;
      const rollValue = event.data.result

      return {
        text: rollValue === null ? "🎲 Lancer de dés" : `🎲 ${rollValue}`,
        tone: "neutral",
        presentation: "giant",
      };
    }

    if (!event.view) return null;

    const view = event.view;

    switch (event.type) {
      case GameEventType.TURN_STARTED: {
        const player = getPlayerKey(state, view.currentPlayer);
        return {
          text: `Tour de ${state[player].player.name}`,
          tone: "neutral",
        };
      }

      case GameEventType.COINS_GAINED:
      case GameEventType.COINS_LOST: {
        const playerKey = getPlayerKey(state, view.playerId);
        const previousCoins = state[playerKey].coins;
        const nextCoins = view.total;

        if (nextCoins !== previousCoins) {
          const delta = nextCoins - previousCoins;
          return {
            text: `${state[playerKey].player.name} ${delta > 0 ? "+" : ""}${delta} pièces`,
            tone: delta > 0 ? "positive" : "negative",
          };
        }

        return null;
      }

      case GameEventType.HEAL:
      case GameEventType.DAMAGE: {
        const playerKey = getPlayerKey(state, view.playerId);
        const previousHealth = state[playerKey].healthPoints;
        const nextHealth = view.total;

        if (nextHealth !== previousHealth) {
          const delta = nextHealth - previousHealth;
          return {
            text: `${state[playerKey].player.name} ${delta > 0 ? "+" : ""}${delta} PV`,
            tone: delta > 0 ? "positive" : "negative",
          };
        }

        return null;
      }

      default:
        return null;
    }
  };

	const applyView = (state: GameState, event: GameEvent): GameState => {
    if (!event.view) return state;
    console.log(event);

    let next = { ...state };
    const view = event.view;

    switch (event.type) {
      case GameEventType.CARD_DRAWN: {
        const playerKey = getPlayerKey(state, view.playerId);
        const player = state[playerKey];
        // skip
        if (!view.card && player.player.name === currentUser?.username) {
          return next;
        }

        const newHand = [...player.hand, view.cardId];

        const newDrawPile = player.drawPile.filter((id) => id !== view.cardId);

        next[playerKey] = {
          ...player,
          hand: newHand,
          drawPile: newDrawPile,
        };

        if (view.card) {
          next.cards = {
            ...next.cards,
            [view.card.instanceId]: view.card,
          };
        }

        return next;
      }

      case GameEventType.TURN_STARTED: {
        return {
          ...state,
          currentPlayer: view.currentPlayer,
        };
      }

      case GameEventType.CARD_DISCARDED:
      case GameEventType.CARD_PLACE_IN_PLAY_AREA:
      case GameEventType.CARD_PLACE_IN_MONSTER_AREA: {
        const cardId = view.cardId;

        const playerKey = getPlayerKey(state, view.playerId);
        const player = state[playerKey];

        const nextPlayer = {
          ...player,
          hand: player.hand.filter((id) => id !== cardId),
        };

        if (event.type === GameEventType.CARD_DISCARDED) {
          return {
            ...state,
            [playerKey]: {
              ...nextPlayer,
              discardPile: [...player.discardPile, cardId],
            },
          };
        }

        return {
          ...state,
          [playerKey]: {
            ...nextPlayer,
            playArea: {
              passiveCards:
                event.type === GameEventType.CARD_PLACE_IN_PLAY_AREA
                  ? [...player.playArea.passiveCards, cardId]
                  : player.playArea.passiveCards,
              monsterCards:
                event.type === GameEventType.CARD_PLACE_IN_MONSTER_AREA
                  ? [...player.playArea.monsterCards, cardId]
                  : player.playArea.monsterCards,
            },
          },
          cards: {
            ...state.cards,
            ...(view.card ? { [cardId]: view.card } : {}),
          },
        };
      }

      case GameEventType.COINS_GAINED:
      case GameEventType.COINS_LOST: {
        const nextCoins = view.total;

        const playerKey = getPlayerKey(state, view.playerId);
        return {
          ...state,
          [playerKey]: {
            ...state[playerKey],
            coins: nextCoins,
          },
        };
      }

      case GameEventType.HEAL:
      case GameEventType.DAMAGE: {
        const nextHealth = view.total;

        const playerKey = getPlayerKey(state, view.playerId);

        return {
          ...state,
          [playerKey]: {
            ...state[playerKey],
            healthPoints: nextHealth,
          },
        };
      }

      default:
        console.log(`Unhandled event type ${event.type}`);
        return state;
    }
  };

	useMercure(
    `${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${gameId}&topic=game/${gameId}-${currentUser?.username === game?.player1.player.name ? "1" : "2"}`, // @todo change
    {
      game_events: (e: { events: GameEvent[] }) => {
        const previousGame = gameRef.current;

        if (!previousGame) {
          return;
        }

        let next = { ...previousGame };

        for (const event of e.events) {
          const previous = next;
          const announcement = animate(previous, event);

          if (announcement) {
            pushAnnouncement(announcement);
          }

          next = applyView(next, event);
        }

        gameRef.current = next;
        setGame(next);
      },
    },
  );

	useEffect(() => {
    gameRef.current = game;
  }, [game]);

	return (
    <GameContext.Provider
      value={{
        game,
        getCardById,
        announcements,
        actions: { playCard, attack, endTurn },
      }}
    >
      {children}
    </GameContext.Provider>
  );
}
