import { BasicCard } from '@/components/types/card';
import { GameState } from '@/lib/game/type/gameState';

import { createContext, ReactNode, useCallback, useContext, useState } from 'react';

type GameContextType = {
  game: GameState | null;
  getCardById: (cardId: string) => BasicCard | undefined;
}

type Props = {
  children: ReactNode;
  game?: GameState | null;
};

export const GameContext = createContext<GameContextType>();

export const GameProvider = ({ children, game: initialGame }: Props) => {
  const [game, setGame] = useState<GameState | null>(initialGame || null);

  const getCardById = useCallback(
    (cardId: string): BasicCard | undefined => {
      if (!game) return undefined;

	  console.log(cardId, game.cards)
      return game.cards[cardId] as BasicCard | undefined;
    },
    [game]
  );

  return (
    <GameContext.Provider value={{ game, getCardById }}>
	  {children}
    </GameContext.Provider>
  );
}
