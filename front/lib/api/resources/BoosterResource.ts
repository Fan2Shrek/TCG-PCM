import { ApiClient } from "../api";

export class BoosterResource {
  constructor(private client: ApiClient) {}

  public open() {
	return this.client.post('boosters/open');
  }
}
