import { GameContext } from "@/context/GameContext";
import { useContext } from "react";
import Card from "../Card";

type BoardRowProps = {
  title: string;
  cards: string[];
};

export default ({ title, cards }: BoardRowProps) => {
  const { getCardById } = useContext(GameContext);

  return (
    <div className="flex flex-col items-center gap-2">
      <div className="text-sm opacity-70">{title}</div>

      <div className="flex gap-2">
		{cards.map((cardId) => (
		  <Card key={cardId} card={getCardById(cardId)} />
		))}
      </div>
    </div>
  );
}
