import { PlayerActionType } from "@/lib/game/type/playerAction";
import { ApiClient } from "../api";

export class GameResource {
	constructor(private client: ApiClient) {
	}

	async getGame(id: string) {
		return this.client.get(`/game/${id}`)
	}

	async play(id: string, action: PlayerActionType, payload: any = {}) {
		return this.client.post(`/game/${id}/play`, {
		  actionId: action,
		  payload,
		})
	}
}
