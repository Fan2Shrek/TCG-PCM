import { BasicCard } from "@/lib/cards/types/card";
import { GameState, PlayerState } from "@/lib/game/type/gameState";
import AlliedCharacterPanel from "./AlliedCharacterPanel";
import PassiveZone from "@/components/molecules/game/PassiveZone";
import MonsterZone from "@/components/molecules/game/MonsterZone";
import EnemyPassiveZone from "@/components/molecules/game/EnemyPassiveZone";
import EnemyMonsterZone from "@/components/molecules/game/EnemyMonsterZone";
import { GAMEBOARD_TILT } from "@/constants/gameArea";
import EnemyCharacterPanel from "./EnemyCharacterPanel";

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
        <div className="h-[90vh] w-[90vw] bg-orange-800 flex flex-col pb-25">
          {/* finally, this div contains the actual play area where everything happens. */}
          {p2 && (
            <div className="flex-1 w-full grid grid-cols-5 items-center bg-red-600 gap-5 px-5">
              <EnemyPassiveZone
                title="Player 2 Passive"
                cards={p2.playArea.passiveCards}
                className="col-span-1"
              />
              <EnemyMonsterZone
                title="Player 2 Monsters"
                cardsIds={p2.playArea.monsterCards}
                clickable={!!selectedAttackerId}
                onCardClick={onSelectTarget}
                className="col-span-3"
              />
              <EnemyCharacterPanel
                player={opponentState}
                selectedAttackerId={selectedAttackerId}
                handleAttackTarget={onSelectTarget}
                selectedAttackerCard={selectedAttackerCard}
              />
            </div>
          )}

          {p1 && (
            <div className="flex-1 w-full grid grid-cols-5 items-center bg-blue-600 gap-5 px-5">
              <AlliedCharacterPanel
                player={currentState}
                className="col-span-1"
              />
              <MonsterZone
                title="Player 1 Monsters"
                cardsIds={p1.playArea.monsterCards}
                clickable
                onCardClick={onSelectAttacker}
                selectedCardId={selectedAttackerId}
                isCardDisabled={(cardId) =>
                  getCardById(cardId)?.isActive === false
                }
                className="col-span-3"
              />
              <PassiveZone
                title="Player 1 Passive"
                cards={p1.playArea.passiveCards}
                className="col-span-1"
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
