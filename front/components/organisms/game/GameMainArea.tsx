import { GameState, PlayerState } from "@/lib/game/type/gameState";
import PlayZone from "@/components/molecules/game/PlayZone";
import EnemyPlayZone from "@/components/molecules/game/EnemyPlayZone";
import DrawPile from "@/components/molecules/game/DrawPile";
import Cemetery from "@/components/molecules/game/Cemetery";
import PlayerCharacterDisplay from "@/components/molecules/game/PlayerCharacterDisplay";
import { GAMEBOARD_TILT } from "@/constants/gameArea";
import { BasicCard } from "@/lib/cards/types/card";

type GameMainAreaProps = {
  game: GameState | null;
  selectedAttackerId: string | null;
  onSelectAttacker: (cardId: string) => void;
  onSelectTarget: (cardId: string) => void;
  getCardById: (id: string) => BasicCard | undefined;
  selectedAttackerCard: BasicCard | undefined;
  opponentState: PlayerState;
  currentState: PlayerState;
  className?: string;
  isCardDragged: boolean;
};

export default function GameMainArea({
  game,
  className,
  selectedAttackerId,
  onSelectAttacker,
  onSelectTarget,
  selectedAttackerCard,
  opponentState,
  currentState,
  isCardDragged,
}: GameMainAreaProps) {
  const loggedPlayer =
    game?.player1.player.id === currentState.player.id
      ? game?.player1
      : game?.player2;
  const oppositePlayer =
    loggedPlayer === game?.player1 ? game?.player2 : game?.player1;

  return (
    <div
      className={`relative flex-1 flex flex-col items-center justify-center transform-gpu w-1250 h-1250  ${className || ""}`}
    >
      {/* parent div to apply transform 3d to the game area */}
      <div
        className="absolute -inset-[20%] flex items-center justify-center bg-orange-800 transition-transform duration-300"
        className="absolute -inset-[20%] flex items-center justify-center bg-orange-800 transition-transform duration-300"
        style={{
          transform: isCardDragged
            ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)"
            : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg)`,
          transform: isCardDragged
            ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)"
            : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg)`,
        }}
      >
        {/* this one above is to apply the rotation on the whole board while taking +10% than the max screen size. This is to make sure it takes up the entire screen, even if the component is tilted.*/}
        <div className="h-[95vh] min-h-300 w-[90vw] min-w-450 bg-orange-800 flex flex-col pb-25 relative">
          {/* finally, this div contains the actual play area where everything happens. */}
          {oppositePlayer && (
            <div className="w-full h-full relative grid grid-cols-5 items-center gap-5 bg-red-600 p-3">
              <DrawPile
                numCards={oppositePlayer.drawPile.length}
                className="col-span-1"
              />
              <div className="flex flex-col col-span-3 items-center">
                <EnemyPlayZone
                  title="Player 2 Cards"
                  passiveCardIds={oppositePlayer.playArea.passiveCards}
                  monsterCardIds={oppositePlayer.playArea.monsterCards}
                />
                <PlayerCharacterDisplay
                  player={opponentState}
                  className="absolute top-0"
                />
              </div>
              <Cemetery
                cardIds={oppositePlayer.discardPile}
                className="col-span-1"
              />
            </div>
          )}

          {loggedPlayer && (
            <div className="w-full h-full relative grid grid-cols-5 items-center gap-5 bg-blue-600 p-3">
              <Cemetery
                cardIds={loggedPlayer.discardPile}
                className="col-span-1"
              />
              <div className="flex flex-col col-span-3 items-center h-full">
                <PlayZone
                  title="Player 1 Cards"
                  passiveCardIds={loggedPlayer.playArea.passiveCards}
                  monsterCardIds={loggedPlayer.playArea.monsterCards}
                />
                <PlayerCharacterDisplay
                  player={currentState}
                  className="absolute bottom-0"
                />
              </div>
              <DrawPile
                numCards={loggedPlayer.drawPile.length}
                className="col-span-1"
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
