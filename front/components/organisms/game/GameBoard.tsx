import PlayerPanel from "@/components/atoms/game/PlayerPanel";
import BoardRow from "@/components/molecules/game/BoardRow";
import { GameState } from "@/lib/game/type/gameState";

type Props = {
	game: GameState;
}

export default ({game}: Props) => {
  const p1 = game.player1;
  const p2 = game.player2;

  return (
    <div className="flex flex-col h-screen bg-green-900 text-white">
      <div className="flex justify-center p-4 border-b border-green-700">
        <PlayerPanel player={p2} />
      </div>

      <div className="flex flex-1 flex-col items-center justify-center gap-6">

        <BoardRow title="Player 2 Monsters" cards={p2.playArea.monsterCards} />
        <BoardRow title="Player 2 Passive" cards={p2.playArea.passiveCards} />

        <BoardRow title="Player 1 Monsters" cards={p1.playArea.monsterCards} />
        <BoardRow title="Player 1 Passive" cards={p1.playArea.passiveCards} />

        <div className="text-sm opacity-80">
          Current Player: {game.currentPlayer.name}
        </div>

      </div>

      <div className="border-t border-green-700 p-4">
        <PlayerPanel player={p1} />

        <div className="flex gap-2 mt-4 justify-center">
			<p>Card</p>
			<p>Card</p>
			<p>Card</p>
			<p>Card</p>
        </div>
      </div>
    </div>
  );
}
