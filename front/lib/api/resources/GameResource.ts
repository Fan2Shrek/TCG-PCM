import { ApiClient } from "../api";

export class GameResource {
	constructor(private client: ApiClient) {
	}

	async getGame(id: string) {
		return this.client.get(`/game/${id}`)
	}
}
