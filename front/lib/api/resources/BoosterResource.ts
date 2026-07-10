import { ApiClient } from "../api";

export class BoosterResource {
  constructor(private client: ApiClient) {}

  public open(type: string) {
    return this.client.post("/boosters/open", { type });
  }
}
