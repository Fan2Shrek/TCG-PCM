import CardsHand from "../../organisms/CardsHand";
import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import PlayerStats from "@/components/molecules/game/PlayerStats";

type Props = {
  player: any;
  selectedAttackerId: string | null;
  handleAttackTarget: (targetId: string) => void;
  selectedAttackerCard: any; // Assuming this is a card object
};

export default ({ player, selectedAttackerId, handleAttackTarget, selectedAttackerCard }: Props) => {
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
      <div className="mt-3 text-center text-sm text-white/70">
        {selectedAttackerId ? "Choisis une cible pour attaquer" : "Choisis un monstre pour attaquer"}
      </div>
      <div className="grid grid-cols-[1fr_auto_1fr] items-center w-full p-4">
        {playerCard && (
          <div className="col-start-3 justify-self-end">
            <PlayerStats
              playerCard={playerCard}
              health={player.healthPoints}
              maxHealth={player.maxHealthPoints}
              asOpponent={true}
            />
          </div>
        )}
      </div>
    </button>
  );
};
