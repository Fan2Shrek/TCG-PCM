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

	async list() {
		return this.client.get(`/waiting-rooms`)
	}

	async join(id: string) {
		return this.client.post(`/rooms/${id}/join`)
	}
}
