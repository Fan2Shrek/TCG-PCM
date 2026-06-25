import Card from "@/components/molecules/Card";
import PlayerHealthBar from "@/components/molecules/game/PlayerHealthBar";
import CardsHand from "../../organisms/CardsHand";
import { GameContext } from "@/contexts/GameContext";
import { useContext } from "react";
import { BasicCard } from "@/components/types/card";

type PlayerStatsProps = {
  playerCard: BasicCard;
  health: number;
  maxHealth: number;
};

export default ({ playerCard, health, maxHealth }: PlayerStatsProps) => {

  return (
    <div className="flex flex-col items-center gap-4 min-w-64">
      <Card card={playerCard} />
      <PlayerHealthBar health={health} maxHealth={maxHealth} />
    </div>
  );
}
