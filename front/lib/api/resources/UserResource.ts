import { ApiClient } from "../api";

export class UserResource {
  constructor(private client: ApiClient) {}

  async getInventory() {
    return this.client.get(`/inventory`);
  }

  async getUser() {
    return this.client.get(`/user`);
  }

  public generateBoosterTokens() {
    return this.client.post("/user/generate_booster_tokens");
  }
}
