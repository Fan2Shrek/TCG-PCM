import { GameState, PlayerState } from "@/lib/game/type/gameState";
import PlayZone from "@/components/molecules/game/PlayZone";
import EnemyPlayZone from "@/components/molecules/game/EnemyPlayZone";
import DrawPile from "@/components/molecules/game/DrawPile";
import Cemetery from "@/components/molecules/game/Cemetery";
import PlayerCharacterDisplay from "@/components/molecules/game/PlayerCharacterDisplay";
import { GAMEBOARD_TILT } from "@/constants/gameArea";
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

export default function GameMainArea({ game, className, selectedAttackerId, onSelectAttacker, onSelectTarget, selectedAttackerCard, opponentState, currentState, isCardDragged, hoveredTargetId }: GameMainAreaProps) {
  const loggedPlayer = game?.player1.player.id === currentState.player.id ? game?.player1 : game?.player2;
  const oppositePlayer = loggedPlayer === game?.player1 ? game?.player2 : game?.player1;

  return (
    <div className={`relative flex-1 flex flex-col items-center justify-center transform-gpu w-1250 h-1250  ${className || ""}`}>
      {/* parent div to apply transform 3d to the game area */}
      <div
        className='absolute -inset-[20%] flex items-center justify-center bg-orange-800 transition-transform duration-300'
        style={{
          transform: isCardDragged ? "perspective(1500px) rotateX(0deg) rotateZ(0deg)" : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg) scale(0.9)`,
        }}
      >
        {/* this one above is to apply the rotation on the whole board while taking +10% than the max screen size. This is to make sure it takes up the entire screen, even if the component is tilted.*/}
        <div className='h-[70vh] min-h-280 w-[85vw] min-w-420 bg-orange-800 flex flex-col relative -mt-50'>
          {/* OpponentHand positioned absolutely */}
          {!isCardDragged && <OpponentHand numCards={oppositePlayer?.hand.length || 0} className='absolute left-1/2 -translate-x-1/2 top-8 z-1' />}

          {/* finally, this div contains the actual play area where everything happens. */}
          {oppositePlayer && (
            <div className='w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-red-600 p-3'>
              <div className='flex flex-col gap-4 justify-end col-span-1 items-center h-full p-2'>
                <Cemetery cardIds={oppositePlayer.discardPile} />
                <DrawPile numCards={oppositePlayer.drawPile.length} />
              </div>
              <div className='flex flex-col col-span-3 items-center'>
                <EnemyPlayZone passiveCardIds={oppositePlayer.playArea.passiveCards} monsterCardIds={oppositePlayer.playArea.monsterCards} selectedCardId={selectedAttackerId} onSelectCard={(id) => id && onSelectAttacker(id)} hoveredTargetId={hoveredTargetId} />
              </div>
              <div className='flex flex-col col-span-1 items-center gap-2'>
                <PlayerStatsDisplay money={opponentState.coins} health={opponentState.healthPoints} />
                <PlayerCharacterDisplay player={opponentState} isTargeting={selectedAttackerId !== null} hoveredTargetId={hoveredTargetId} />
              </div>
            </div>
          )}

          {loggedPlayer && (
            <div className='w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-blue-600 p-3'>
              <div className='flex flex-col col-span-1 items-center gap-2'>
                <PlayerCharacterDisplay player={currentState} isTargeting={selectedAttackerId !== null} hoveredTargetId={hoveredTargetId} />
                <PlayerStatsDisplay money={currentState.coins} health={currentState.healthPoints} />
              </div>
              <div className='flex flex-col col-span-3 items-center h-full'>
                <PlayZone passiveCardIds={loggedPlayer.playArea.passiveCards} monsterCardIds={loggedPlayer.playArea.monsterCards} selectedCardId={selectedAttackerId} onSelectCard={(id) => id && onSelectAttacker(id)} hoveredTargetId={hoveredTargetId} />
              </div>
              <div className='flex flex-col gap-4 justify-start col-span-1 items-center h-full p-2'>
                <DrawPile numCards={loggedPlayer.drawPile.length} />
                <Cemetery cardIds={loggedPlayer.discardPile} />
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
