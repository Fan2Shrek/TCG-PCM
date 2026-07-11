import { ApiClient } from "../api";

export class BoosterResource {
  constructor(private client: ApiClient) {}

  public getObtainableCards(type: string, offset = 0, step?: number) {
    const params = new URLSearchParams();
    params.set("type", type);

    if (offset > 0) {
      params.set("offset", offset.toString());
    }

    if (step !== undefined) {
      params.set("step", step.toString());
    }

    const query = params.toString();

    return this.client.get(
      `/boosters/cards${query ? `?${query}` : ""}`,
    );
  }

  public open(type: string) {
    return this.client.post("/boosters/open", { type });
  }
}
