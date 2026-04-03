import BoardRow from "@/components/molecules/game/BoardRow";
import { BasicCard } from "@/components/types/card";
import { GameState } from "@/lib/game/type/gameState";

type GameMainAreaProps = {
  game: GameState | null;
  selectedAttackerId: string | null;
  onSelectAttacker: (cardId: string) => void;
  onSelectTarget: (cardId: string) => void;
  getCardById: (id: string) => BasicCard | undefined;
  className?: string;
};

export default function GameMainArea({
  game,
  className,
  selectedAttackerId,
  onSelectAttacker,
  onSelectTarget,
  getCardById,
}: GameMainAreaProps) {
  const p1 = game?.player1;
  const p2 = game?.player2;

  return (
    <div
      className={`relative flex-1 flex flex-col items-center justify-center transform-3d transform-gpu w-full h-full  ${className || ""}`}
    >
      <div
        className="w-full h-full flex flex-col items-center gap-6 bg-orange-800 max-w-[1500px] max-h-[1000px]"
        style={{
          transform: "perspective(1000px) rotateX(40deg) rotateZ(0deg)",
        }}
      >
        {p2 && (
          <>
            <BoardRow
              title="Player 2 Monsters"
              cards={p2.playArea.monsterCards}
              clickable={!!selectedAttackerId}
              onCardClick={onSelectTarget}
            />
            <BoardRow
              title="Player 2 Passive"
              cards={p2.playArea.passiveCards}
            />
          </>
        )}

        {p1 && (
          <>
            <BoardRow
              title="Player 1 Monsters"
              cards={p1.playArea.monsterCards}
              clickable
              onCardClick={onSelectAttacker}
              selectedCardId={selectedAttackerId}
              isCardDisabled={(cardId) =>
                getCardById(cardId)?.isActive === false
              }
            />
            <BoardRow
              title="Player 1 Passive"
              cards={p1.playArea.passiveCards}
            />
          </>
        )}
      </div>
      <div className="absolute bottom-10 right-1/15 left-1/15 h-[50px] bg-orange-900" />

      <div className="absolute -bottom-30 left-1/15 w-[200px] h-[200px] bg-orange-900" />
      <div className="absolute -bottom-30 right-1/15 w-[200px] h-[200px] bg-orange-900" />
    </div>
  );
}
