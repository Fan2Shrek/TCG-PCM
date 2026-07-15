import { useMemo } from "react";
import { PlayerState } from "@/lib/game/type/gameState";
import PlayZone from "@/components/molecules/game/PlayZone";
import DrawPile from "@/components/molecules/game/DrawPile";
import Cemetery from "@/components/molecules/game/Cemetery";
import PlayerCharacterDisplay from "@/components/molecules/game/PlayerCharacterDisplay";
import {
  GAMEBOARD_TILT,
  GAMEBOARD_ANIMATION_DURATION,
  GAMEBOARD_ANIMATION_TIMING,
} from "@/constants/gameArea";
import PlayerStatsDisplay from "@/components/molecules/game/PlayerStatsDisplay";
import OpponentHand from "@/components/molecules/game/OpponentHand";
import GameAttack from "@/components/molecules/game/GameAttack";

type GameMainAreaProps = {
  opponentState: PlayerState;
  currentState: PlayerState;
  className?: string;
  isCardDragged: boolean;
  onPlayZoneClick?: () => void;
  isPlayZoneSelectable?: boolean;
};

export default function GameMainArea({
  className,
  opponentState,
  currentState,
  isCardDragged,
  onPlayZoneClick,
  isPlayZoneSelectable = false,
}: GameMainAreaProps) {
  const opponentDiscardCardIds = useMemo(
    () => Object.keys(opponentState.discardPile),
    [opponentState.discardPile],
  );
  const currentDiscardCardIds = useMemo(
    () => Object.keys(currentState.discardPile),
    [currentState.discardPile],
  );
  return (
    <div
      className={`relative flex-1 flex md:flex-col md:items-center md:justify-center transform-gpu w-1250 h-1250 overflow-auto md:overflow-hidden" ${className || ""}`}
    >
      {/* parent div to apply transform 3d to the game area */}
      <div
        className="game-board relative flex items-center justify-center"
        style={{
          transform: isCardDragged
            ? "perspective(1500px) rotateX(0deg) rotateZ(0deg) scale(var(--game-board-scale, 0.3))"
            : `perspective(1000px) rotateX(${GAMEBOARD_TILT}deg) rotateZ(0deg) scale(var(--game-board-scale, 0.3))`,
          transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
        }}
      >
        <GameAttack />
        {/* this one above is to apply the rotation on the whole board while taking +10% than the max screen size. This is to make sure it takes up the entire screen, even if the component is tilted.*/}
        <div className="h-[70vh] min-h-300 w-[85vw] min-w-420  flex flex-col relative -mt-60">
          {/* OpponentHand positioned absolutely, logged user's hand is in gameboard instead as an overlay */}
          <OpponentHand
            numCards={opponentState.hand.length || 0}
            className="absolute left-1/2 -translate-x-1/2 -top-8 z-1"
            currentPlayerId={currentState.player.id}
          />

          {/* finally, this div contains the actual play area where everything happens. */}
          <div className="w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-red-600 p-3">
            <div className="flex flex-col gap-4 justify-end col-span-1 items-center h-full p-2">
              <Cemetery cardIds={opponentDiscardCardIds} />
              <DrawPile
                numCards={opponentState.drawPile.length}
                isCardDragged={isCardDragged}
                playerId={currentState.player.id}
                isOpponent
              />
            </div>
            <div className="flex flex-col col-span-3 items-center">
              <PlayZone
                passiveCardIds={opponentState.playArea.passiveCards}
                monsterCardIds={opponentState.playArea.monsterCards}
                isOpponent
              />
            </div>
            <div className="flex flex-col col-span-1 items-center gap-2">
              <PlayerStatsDisplay
                money={opponentState.coins}
                health={opponentState.healthPoints}
              />
              <PlayerCharacterDisplay player={opponentState} />
            </div>
          </div>

          <div className="w-full h-1/2 relative grid grid-cols-5 items-center gap-5 bg-blue-600 p-3">
            <div className="flex flex-col col-span-1 items-center gap-2">
              <PlayerCharacterDisplay player={currentState} />
              <PlayerStatsDisplay
                money={currentState.coins}
                health={currentState.healthPoints}
              />
            </div>
            <div className="flex flex-col col-span-3 items-center h-full">
              <PlayZone
                passiveCardIds={currentState.playArea.passiveCards}
                monsterCardIds={currentState.playArea.monsterCards}
                onClick={onPlayZoneClick}
                isSelectableTarget={isPlayZoneSelectable}
              />
            </div>
            <div className="flex flex-col gap-4 justify-start col-span-1 items-center h-full p-2">
              <DrawPile
                numCards={currentState.drawPile.length}
                isCardDragged={isCardDragged}
                playerId={currentState.player.id}
              />
              <Cemetery cardIds={currentDiscardCardIds} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
