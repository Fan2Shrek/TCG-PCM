import { ApiClient } from "../api";

export class AuthResource {
  constructor(private client: ApiClient) {}

  async login(username: string, password: string) {
	return this.client.post('/login_check', { username, password });
  }

  async register(username: string, password: string) {
	return this.client.post('/register', { username, password });
  }
}
