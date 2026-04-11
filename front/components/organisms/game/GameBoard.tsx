import PlayerPanel from "@/components/atoms/game/PlayerPanel";
import BoardRow from "@/components/molecules/game/BoardRow";
import { GameContext } from "@/context/GameContext";
import { useContext, useEffect, useRef } from "react";
import CardsHand from "../CardsHand";
import PlayerHealthBar from "@/components/molecules/game/PlayerHealthBar";
import { getCurrentUser } from "@/lib/utils";
import { emitter } from "@/lib/eventBus";

export default () => {
  const { game, getCardById, actions } = useContext(GameContext);
  const playBoxRef = useRef<HTMLDivElement>(null);

  if (!game) {
  	return <div>Loading...</div>;
  }

  const connectedPlayer = game.player1.player.name === getCurrentUser()?.username ? game.player1.player.id : game.player2.player.id;

  const currentState = game.player1.player.name === getCurrentUser()?.username ? game.player1 : game.player2;
  const opponentState = game.player1.player.name === getCurrentUser()?.username ? game.player2 : game.player1;

  useEffect(() => {
	const handler = (data) => {
	  const rect = playBoxRef.current.getBoundingClientRect();

	  const x = data.x;
	  const y = data.y;

	  // TODO Check this shit
		//  const isInside =
		// x >= rect.left &&
		// x <= rect.right &&
		// y >= rect.top &&
		// y <= rect.bottom;

	  const isInside = true;

	  isInside && actions.playCard(data.id);
	};
	emitter.on('card:played', handler);

	return () => emitter.off('card:played', handler);
  }, []);

  return (
    <div className="flex flex-col h-screen bg-green-900 text-white">
	  <PlayerHealthBar health={opponentState.healthPoints} maxHealth={opponentState.maxHealthPoints} />
      <div className="flex justify-center p-4 border-b border-green-700">
        <PlayerPanel player={opponentState} />
      </div>

      <div ref={playBoxRef} className="flex flex-1 flex-col items-center justify-center gap-6">
        <BoardRow title="Player 2 Monsters" cards={opponentState.playArea.monsterCards} />
        <BoardRow title="Player 2 Passive" cards={opponentState.playArea.passiveCards} />

        <BoardRow title="Player 1 Monsters" cards={currentState.playArea.monsterCards} />
        <BoardRow title="Player 1 Passive" cards={currentState.playArea.passiveCards} />
      </div>

      <div className="border-t border-green-700 p-4">
        <PlayerPanel player={currentState} />

        <div className="flex gap-2 mt-4 justify-center">
		  <CardsHand cards={currentState.hand.map((cardId) => getCardById(cardId))} />
        </div>
		<PlayerHealthBar health={currentState.healthPoints} maxHealth={currentState.maxHealthPoints} />
      </div>
	  {connectedPlayer == game.currentPlayer && <button className="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded" onClick={actions.endTurn}>end</button>}
    </div>
  );
}
