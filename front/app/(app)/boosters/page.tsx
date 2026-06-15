'use client';

import { useState } from "react";
import api from "@/lib/api/api";
import Card from "@/components/molecules/Card";
import { Button } from "@/components/ui/button";

export default () => {
	const [cards, setCards] = useState(null);
	const [error, setError] = useState<string | null>(null);

	const handleOpen = async () => {
		setError(null);

		try {
			const res = await api.booster.open();
			setCards(res.cards);
		} catch (err) {
			setError(err instanceof Error ? err.message : 'mdr ca a explosé');
		}
	};

	return <div className="flex flex-col items-center justify-center h-screen gap-10">
		<Button
			onClick={handleOpen}
			className="px-6 py-3 h-auto rounded-full text-lg font-bold border-2 border-white hover:scale-105 transition-transform"
		>
			ouvrir un booster
		</Button>

		{error && <p className="text-red-500">{error}</p>}

		<div className="flex flex-row flex-wrap gap-5 justify-center">
			{cards && cards.map((card, i) => <Card key={i} card={card} />)}
		</div>
	</div>;
}
