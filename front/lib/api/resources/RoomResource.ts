import { ApiClient } from "../api";

export class RoomResource {
	constructor(private client: ApiClient) {
	}

	async create() {
		return this.client.post('/rooms/create')
	}

	async start(id: string) {
		return this.client.post(`/rooms/${id}/start`)
	}
}
