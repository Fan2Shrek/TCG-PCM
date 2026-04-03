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
  asOpponent: boolean;
};

export default ({ playerCard, health, maxHealth, asOpponent = false }: PlayerStatsProps) => {

  return (
    <div className={`flex items-center gap-4 min-w-64 ${asOpponent ? 'flex-row-reverse' : 'flex-row'}`}>
      <Card card={playerCard} />
      <PlayerHealthBar health={health} maxHealth={maxHealth} />
    </div>
  );
}
