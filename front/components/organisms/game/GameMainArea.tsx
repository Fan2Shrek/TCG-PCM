import { BasicCard } from "@/lib/cards/types/card";
import { GameState, PlayerState } from "@/lib/game/type/gameState";
import PlayZone from "@/components/molecules/game/PlayZone";
import EnemyPlayZone from "@/components/molecules/game/EnemyPlayZone";
import DrawPile from "@/components/molecules/game/DrawPile";
import Cemetery from "@/components/molecules/game/Cemetery";
import PlayerCharacterDisplay from "@/components/molecules/game/PlayerCharacterDisplay";
import { GAMEBOARD_TILT } from "@/constants/gameArea";

type GameMainAreaProps = {
  game: GameState | null;
  selectedAttackerId: string | null;
  onSelectAttacker: (cardId: string) => void;
  onSelectTarget: (cardId: string) => void;
  getCardById: (id: string) => BasicCard | undefined;
  opponentState: PlayerState;
  currentState: PlayerState;
  selectedAttackerCard: BasicCard | undefined;
  className?: string;
  isCardDragged: boolean;
};

export default function GameMainArea({
  game,
  className,
  selectedAttackerId,
  onSelectAttacker,
  onSelectTarget,
  getCardById,
  opponentState,
  currentState,
  selectedAttackerCard,
  isCardDragged,
}: GameMainAreaProps) {
  const p1 = game?.player1;
  const p2 = game?.player2;

  return (
    <div
      className={`relative flex-1 flex flex-col items-center justify-center transform-3d transform-gpu w-1250 h-1250  ${className || ""}`}
    >
      {/* parent div to apply transform 3d to the game area */}
      <div
        className="absolute -inset-[20%] flex items-center justify-center bg-orange-800 transition-transform duration-300"
        style={{
          transform: isCardDragged
            ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)"
            : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg)`,
        }}
      >
        {/* this one above is to apply the rotation on the whole board while taking +10% than the max screen size. This is to make sure it takes up the entire screen, even if the component is tilted.*/}
        <div className="h-[90vh] w-[90vw] bg-orange-800 flex flex-col pb-25 relative">
          {/* finally, this div contains the actual play area where everything happens. */}
          {p2 && (
            <div className="flex-1 w-full flex flex-col items-center justify-center bg-red-600 gap-5 px-5 relative">
              <PlayerCharacterDisplay
                player={opponentState}
                className="absolute top-0"
              />
              <div className="w-full grid grid-cols-5 items-center gap-5">
                <DrawPile
                  numCards={p2.drawPile.length}
                  className="col-span-1"
                />
                <EnemyPlayZone
                  title="Player 2 Cards"
                  passiveCardIds={p2.playArea.passiveCards}
                  monsterCardIds={p2.playArea.monsterCards}
                  className="col-span-3"
                />
                <Cemetery cardIds={p2.discardPile} className="col-span-1" />
              </div>
            </div>
          )}

          {p1 && (
            <div className="flex-1 w-full flex flex-col items-center justify-center bg-blue-600 gap-5 px-5 relative">
              <div className="w-full grid grid-cols-5 items-center gap-5">
                <DrawPile
                  numCards={p1.drawPile.length}
                  className="col-span-1"
                />
                <PlayZone
                  title="Player 1 Cards"
                  passiveCardIds={p1.playArea.passiveCards}
                  monsterCardIds={p1.playArea.monsterCards}
                  className="col-span-3"
                />
                <Cemetery cardIds={p1.discardPile} className="col-span-1" />
              </div>
              <PlayerCharacterDisplay
                player={currentState}
                className="absolute bottom-0"
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
