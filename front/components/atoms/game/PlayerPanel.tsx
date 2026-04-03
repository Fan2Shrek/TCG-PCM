import Card from "@/components/molecules/Card";
import PlayerHealthBar from "@/components/molecules/game/PlayerHealthBar";
import CardsHand from "../../organisms/CardsHand";
import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import PlayerStats from "@/components/molecules/game/PlayerStats";

type Props = {
  player: any;
  asOpponent?: boolean;
};

export default ({ player, asOpponent = false }: Props) => {
  const { getCardById } = useContext(GameContext);

	const playerCard = getCardById(player.characterCardId);

  return (
    <div className={`items-center gap-6 bg-green-800 p-3 rounded-lg`}>
			<div className="flex flex-row items-center gap-60">
				{playerCard && <PlayerStats playerCard={playerCard} health={player.healthPoints} maxHealth={player.maxHealthPoints} />}

				{!asOpponent && (
					<div className="relative flex gap-2 mt-4 justify-center">
					<CardsHand cards={player.hand.map((cardId: string) => getCardById(cardId))} />
					</div>
				)}
			</div>
			
			<div>
			Draw pile
			</div>
    </div>
  );
}
