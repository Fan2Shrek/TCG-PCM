import { createRoomAction } from "@/lib/actions/room";

export default function Arene() {
	return (
		<div className="flex flex-col items-center justify-center h-screen">
			<form action={createRoomAction}>
				<button type="submit" className="pt-40">create</button>
			</form>
		</div>
	);
}
