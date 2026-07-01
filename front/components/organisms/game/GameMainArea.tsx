import { GameState, PlayerState } from "@/lib/game/type/gameState";
import PlayZone from "@/components/molecules/game/PlayZone";
import EnemyPlayZone from "@/components/molecules/game/EnemyPlayZone";
import DrawPile from "@/components/molecules/game/DrawPile";
import Cemetery from "@/components/molecules/game/Cemetery";
import PlayerCharacterDisplay from "@/components/molecules/game/PlayerCharacterDisplay";
import { GAMEBOARD_TILT, GAMEBOARD_ANIMATION_DURATION, GAMEBOARD_ANIMATION_TIMING } from "@/constants/gameArea";
import { BasicCard } from "@/lib/cards/types/card";
import PlayerStatsDisplay from "@/components/molecules/game/PlayerStatsDisplay";
import OpponentHand from "@/components/molecules/game/OpponentHand";

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
  hoveredTargetId?: string | null;
};

const objectToArray = (value: object): string[] => {
  return Object.values(value).filter((v): v is string => typeof v === "string");
};

export default function GameMainArea({
  game,
  className,
  selectedAttackerId,
  onSelectAttacker,
  opponentState,
  currentState,
  isCardDragged,
  hoveredTargetId,
}: GameMainAreaProps) {
  return (
    <div className={`game-main-area relative flex-1 flex flex-col items-center justify-center transform-gpu w-1250 h-1250  ${className || ""}`}>
      {/* parent div to apply transform 3d to the game area */}
      <div
        className='game-board absolute -inset-[50%] flex items-center justify-center bg-orange-800'
        style={{
          transform: isCardDragged
            ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)"
            : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg)`,
          transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
        }}
      >
        {/* this one above is to apply the rotation on the whole board while taking +10% than the max screen size. This is to make sure it takes up the entire screen, even if the component is tilted.*/}
        <div className='h-[70vh] min-h-280 w-[85vw] min-w-420 bg-orange-800 flex flex-col relative -mt-60'>
          {/* OpponentHand positioned absolutely */}
          <OpponentHand numCards={opponentState.hand.length || 0} className='absolute left-1/2 -translate-x-1/2 -top-8 z-1' />

          {/* finally, this div contains the actual play area where everything happens. */}
          <div className='w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-red-600 p-3'>
            <div className='flex flex-col gap-4 justify-end col-span-1 items-center h-full p-2'>
              <Cemetery cardIds={opponentState.discardPile} />
              {/*<DrawPile numCards={opponentState.drawPile.length} mirrored={true} isCardDragged={isCardDragged} />*/}
            </div>
            <div className='flex flex-col col-span-3 items-center'>
              <EnemyPlayZone
                passiveCardIds={objectToArray(opponentState.playArea.passiveCards)}
                monsterCardIds={objectToArray(opponentState.playArea.monsterCards)}
                selectedCardId={selectedAttackerId}
                onSelectCard={(id) => id && onSelectAttacker(id)}
                hoveredTargetId={hoveredTargetId}
              />
            </div>
            <div className='flex flex-col col-span-1 items-center gap-2'>
              <PlayerStatsDisplay money={opponentState.coins} health={opponentState.healthPoints} />
              <PlayerCharacterDisplay player={opponentState} isTargeting={selectedAttackerId !== null} hoveredTargetId={hoveredTargetId} />
            </div>
          </div>

          <div className='w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-blue-600 p-3'>
            <div className='flex flex-col col-span-1 items-center gap-2'>
              <PlayerCharacterDisplay player={currentState} isTargeting={selectedAttackerId !== null} hoveredTargetId={hoveredTargetId} />
              <PlayerStatsDisplay money={currentState.coins} health={currentState.healthPoints} />
            </div>
            <div className='flex flex-col col-span-3 items-center h-full'>
              <PlayZone
                passiveCardIds={objectToArray(currentState.playArea.passiveCards)}
                monsterCardIds={objectToArray(currentState.playArea.monsterCards)}
                selectedCardId={selectedAttackerId}
                onSelectCard={(id) => id && onSelectAttacker(id)}
                hoveredTargetId={hoveredTargetId}
              />
            </div>
            <div className='flex flex-col gap-4 justify-start col-span-1 items-center h-full p-2'>
              <DrawPile numCards={currentState.drawPile.length} mirrored={false} isCardDragged={isCardDragged} playerId={currentState.player.id} />
              <Cemetery cardIds={currentState.discardPile} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
