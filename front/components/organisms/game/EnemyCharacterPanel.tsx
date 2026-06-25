import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import { PlayerState } from "@/lib/game/type/gameState";
import { BasicCard } from "@/components/types/card";
import PlayerHealthBar from "@/components/molecules/game/PlayerHealthBar"; // Import PlayerHealthBar
import Card from "@/components/molecules/Card";

type Props = {
  player: PlayerState;
  selectedAttackerId: string | null;
  handleAttackTarget: (targetId: string) => void;
  selectedAttackerCard?: BasicCard;
};

export default function EnemyCharacterPanel({
  player,
  selectedAttackerId,
  handleAttackTarget,
  selectedAttackerCard,
}: Props) {
  const { getCardById } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);

  return (
    <button
      type="button"
      onClick={() => handleAttackTarget(player.player.id)}
      disabled={!selectedAttackerId}
      className={`rounded-xl transition ${selectedAttackerId ? "cursor-pointer hover:scale-[1.01]" : "cursor-not-allowed"} ${selectedAttackerCard ? "ring-4 ring-red-400 ring-offset-2 ring-offset-green-900" : ""}`}
      aria-label={`Attaquer ${player.player.name}`}
    >
      <div className="grid grid-cols-[1fr_auto_1fr] items-center w-full p-4">
        {playerCard && (
          <div className="col-start-3 justify-self-end relative">
            <div className="absolute -top-30 left-1/2 -translate-x-1/2">
              <PlayerHealthBar
                health={player.healthPoints}
                maxHealth={player.maxHealthPoints}
              />
            </div>
            <Card card={playerCard} />
          </div>
        )}
      </div>
    </button>
  );
}
