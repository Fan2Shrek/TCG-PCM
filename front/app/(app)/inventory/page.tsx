import { serverApiGet } from "@/lib/api/server";
import InteractiveCard from "@/components/molecules/InteractiveCard";
import { CardModel } from "@/lib/cards/types/card";

type InventoryResponse = {
	cards: { card: CardModel; quantity: number }[];
};

export default async function Inventory() {
	const inventory = await serverApiGet<InventoryResponse>("/inventory");

	return (
		<div className="pt-90 flex flex-row gap-5">
			{inventory.cards.map(({ card, quantity }) => (
				<div key={card.instanceId}>
					<InteractiveCard card={card} />
					<p>{quantity}x {card.name}</p>
				</div>
			))}
		</div>
	);
}
