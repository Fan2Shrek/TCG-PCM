'use client';

import { useState, useEffect } from "react";
import api from "../../lib/api/api";
import Card from "@/components/molecules/Card";
import InteractiveCard from "@/components/molecules/InteractiveCard";

export default () => {
	const [inventory, setInventory] = useState(null);

	useEffect(() => {
		const fetchGame = async () => {
			return await api.user.getInventory()
		};

		fetchGame().then((data) => setInventory(data)).catch(console.error)
	}, []);

	if (!inventory) {
		return <div>loading</div>;
	}

	return <div className="pt-90 flex flex-row gap-5">
		{inventory.cards.map(({card, quantity}) => (
			<div>
				<InteractiveCard card={card} />
				<p>{quantity}x {card.name}</p>
			</div>
		))}
	</div>;
}
