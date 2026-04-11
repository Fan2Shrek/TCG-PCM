import { BasicCard } from '@/components/types/card';
import useMercure from '@/hook/useMercure';
import { GameEventType } from '@/lib/game/type/eventType';
import { GameEvent } from '@/lib/game/type/gameEvent';
import { GameState } from '@/lib/game/type/gameState';
import api from "@/lib/api/api";

import { createContext, ReactNode, useCallback, useState } from 'react';
import { PlayerActionType } from '@/lib/game/type/playerAction';

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

  useMercure(
	`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${gameId}`, // @todo change
	{
	  [GameEventType.TURN_ENDED]: (e: GameEvent) => {
		setGame((prevGame: GameState) => ({
		  ...prevGame,
		  currentPlayer: e.partialState.currentPlayer,
		}));
	  },
	  [GameEventType.CARD_DRAWN]: (e: GameEvent) => {
		if (!game) return;
		const playerId = e.data.playerId;
		const playerState = game.player1.player.id === playerId ? game.player1 : game.player2;
		const newPlayerState = {
		  ...playerState,
		  hand: e.partialState.hand,
		  drawPile: e.partialState.drawPile
		};

		setGame((prevGame: GameState) => ({
		  ...prevGame,
		  player1: game.player1.player.id === playerId ? newPlayerState : prevGame.player1,
		  player2: game.player2.player.id === playerId ? newPlayerState : prevGame.player2,
		  cards: e.partialState.cards
		}));
	  },
	  [GameEventType.CARD_PLACE_IN_MONSTER_AREA]: (e: GameEvent) => {
		if (!game) return;

		const playerId = e.data.playerId;
		const playerState = game.player1.player.id === playerId ? game.player1 : game.player2;
		const newPlayerState = {
		  ...playerState,
		  hand: e.partialState.hand,
		  playArea: e.partialState.playArea
		};


		setGame((prevGame: GameState) => ({
		  ...prevGame,
		  player1: game.player1.player.id === playerId ? newPlayerState : prevGame.player1,
		  player2: game.player2.player.id === playerId ? newPlayerState : prevGame.player2,
		  cards: e.partialState.cards
		}));
	  }
	}
  );

  return (
    <GameContext.Provider value={{ game, getCardById, actions: { playCard, attack, endTurn } }}>
	  {children}
    </GameContext.Provider>
  );
}
