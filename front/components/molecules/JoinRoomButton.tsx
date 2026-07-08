"use client";

import { useTransition } from "react";

import { joinRoomAction } from "@/lib/actions/room";
import { Button } from "@/components/ui/button";

export default function JoinRoomButton({ roomId }: { roomId: string }) {
	const [isPending, startTransition] = useTransition();

	return (
		<Button
			onClick={() => startTransition(() => joinRoomAction(roomId))}
			disabled={isPending}
			className="rounded-full"
		>
			{isPending ? "..." : "rejoinde"}
		</Button>
	);
}
