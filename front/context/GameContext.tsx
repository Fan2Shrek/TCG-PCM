import { BasicCard } from '@/components/types/card';
import useMercure from '@/hook/useMercure';
import { GameEventType } from '@/lib/game/type/eventType';
import { GameEvent } from '@/lib/game/type/gameEvent';
import { GameState } from '@/lib/game/type/gameState';
import api from "@/lib/api/api";

import { createContext, ReactNode, useCallback, useState } from 'react';
import { PlayerActionType } from '@/lib/game/type/playerAction';
import { getCurrentUser } from '@/lib/utils';

type ActionObject = {
  playCard: (cardId: string) => void;
  attack: (cardId: string, targetId: string) => void;
  endTurn: () => void;
}

type GameContextType = {
  game: GameState | null;
  getCardById: (cardId: string) => BasicCard | undefined;
  actions: ActionObject;
}

type Props = {
  children: ReactNode;
  gameId: string;
  game?: GameState | null;
};

export const GameContext = createContext<GameContextType>();

export const GameProvider = ({ children, gameId, game: initialGame }: Props) => {
  const [game, setGame] = useState<GameState | null>(initialGame || null);

  const getCardById = useCallback(
    (cardId: string): BasicCard | undefined => {
      if (!game) {
		return undefined;
	  }

      return game.cards[cardId] as BasicCard | undefined;
    },
    [game]
  );

  const playCard = (cardId: string) => {
	api.game.play(gameId, PlayerActionType.PLAY_CARD, { cardId });
  }

  const attack = (cardId: string, targetId: string) => {
	api.game.play(gameId, PlayerActionType.END_TURN, { cardId, targetId });
  }

  const endTurn = () => {
	api.game.play(gameId, PlayerActionType.END_TURN);
  }

  const getPlayerKey = (state: GameState, playerId: string): 'player1' | 'player2' =>
	  state.player1.player.id === playerId ? 'player1' : 'player2';

  const applyView = (state: GameState, event: GameEvent): GameState => {
	if (!event.view) return state;

	let next = { ...state };
	const view = event.view;

	switch (event.type) {
	  case GameEventType.CARD_DRAWN: {
		const playerKey = getPlayerKey(state, view.playerId);
		const player = state[playerKey];
		// skip
		if (!view.card && player.player.name === getCurrentUser().username) {
		  return next;
		}

		const newHand = [...player.hand, view.cardId];

		const newDrawPile = player.drawPile.filter(
		  (id) => id !== view.cardId
		);

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

		console.log(cardId, player, view.playerId)
		player.hand = player.hand.filter((id) => id !== cardId);

		if (event.type === GameEventType.CARD_DISCARDED) {
		  player.discardPile = [...player.discardPile, cardId];
		} else {
		  player.playArea = {
			passiveCards: event.type === GameEventType.CARD_PLACE_IN_PLAY_AREA ? [...player.playArea.passiveCards, cardId] : player.playArea.passiveCards,
			monsterCards: event.type === GameEventType.CARD_PLACE_IN_MONSTER_AREA ? [...player.playArea.monsterCards, cardId] : player.playArea.monsterCards,
		  }
		}

		state[playerKey] = player;

		return state;
	  }

	  case GameEventType.COINS_GAINED:
	  case GameEventType.COINS_LOST: {
		const playerKey = getPlayerKey(state, event.playerId);

		return {
		  ...state,
		  [playerKey]: {
			...state[playerKey],
			coins: view.total,
		  },
		};
	  }

	  default:
		console.log(`Unhandled event type ${event.type}`);
		return state;
	}
  }

  useMercure(
	`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${gameId}&topic=game/${gameId}-${getCurrentUser().username === game.player1.player.name ? '1' : '2'}`, // @todo change
	{
		game_events: (e: { events: GameEvent[] }) => {
		  setGame((prev: GameState) => {
			let next = { ...prev };

			for (const event of e.events) {
			  next = applyView(next, event);
			}

			return next;
		  });
		},
	},
  );

  return (
    <GameContext.Provider value={{ game, getCardById, actions: { playCard, attack, endTurn } }}>
	  {children}
    </GameContext.Provider>
  );
}
