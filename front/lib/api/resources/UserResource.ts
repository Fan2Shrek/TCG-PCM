import { ApiClient } from "../api";

export class UserResource {
  constructor(private client: ApiClient) {}

  async getInventory() {
    return this.client.get(`/inventory`);
  }

  async getUser() {
    return this.client.get(`/user`);
  }

  async getInventorySetStats() {
    return this.client.get(`/inventory/stats`);
  }

  public generateBoosterTokens() {
    return this.client.post("/user/generate_booster_tokens");
  }
}
