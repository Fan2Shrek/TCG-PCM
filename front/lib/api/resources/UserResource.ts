import { ApiClient } from "../api";

export class UserResource {
	constructor(private client: ApiClient) {
	}

	async getInventory() {
		return this.client.get(`/inventory`)
	}
}
