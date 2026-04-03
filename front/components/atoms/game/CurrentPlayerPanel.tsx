import CardsHand from "../../organisms/CardsHand";
import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import PlayerStats from "@/components/molecules/game/PlayerStats";

type Props = {
  player: any;
  selectedAttackerId: string | null;
};

export default ({ player, selectedAttackerId }: Props) => {
  const { getCardById } = useContext(GameContext);

  const playerCard = getCardById(player.characterCardId);

  return (
    <>
      <div className="mt-3 text-center text-sm text-white/70">
        {selectedAttackerId
          ? "Choisis une cible pour attaquer"
          : "Choisis un monstre pour attaquer"}
      </div>
      <div className="grid grid-cols-[1fr_auto_1fr] items-center w-full p-4">
        {playerCard && (
          <div className="col-start-1 justify-self-start">
            <PlayerStats
              playerCard={playerCard}
              health={player.healthPoints}
              maxHealth={player.maxHealthPoints}
              asOpponent={false}
            />
          </div>
        )}

        <div className="col-start-2 justify-self-center">
          <CardsHand
            cards={player.hand.map((cardId: string) => getCardById(cardId))}
          />
        </div>
      </div>
    </>
  );
};
