'use client'

import { use, useEffect, useState } from 'react'
import api from "../../../../lib/api/api";
import { GameState } from '@/lib/game/type/gameState';
import GameBoard from '@/components/organisms/game/GameBoard';
import { GameContext, GameProvider } from '@/context/GameContext';

export default ({ params }: { params: Promise<{ id: string }> }) =>  {
  const { id } = use(params)

  const [game, setGame] = useState<GameState|null>(null)

  useEffect(() => {
	const fetchGame = async () => {
	  return await api.game.getGame(id)
	};

	fetchGame().then((data) => setGame(data)).catch(console.error)
  }, []);

  if (!game) {
	return <div>Loading</div>
  }

  return (
	<GameProvider gameId={id} game={ game }>
	  <GameBoard/>
	</GameProvider>
  )
}
