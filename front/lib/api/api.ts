import { AuthResource } from "./resources/AuthResource";
import { BoosterResource } from "./resources/BoosterResource";
import { GameResource } from "./resources/GameResource";

export class ApiClient {
	auth: AuthResource;
  	booster: BoosterResource;
	game: GameResource;

	constructor(
	  private baseUrl: string,
	  private token: string | null = null,
	) {

		this.auth = new AuthResource(this);
		this.booster = new BoosterResource(this);
		this.game = new GameResource(this);
	}

	async request<T>(endpoint: string, options?: RequestInit): Promise<T> {
	    const headers: HeadersInit = {
		  "Content-Type": "application/json",
		  ...(this.token && { Authorization: `Bearer ${this.token}` }),
		};
		const response = await fetch(`${this.baseUrl}${endpoint}`, {
		  ...options,
		  headers,
		});

		if (!response.ok) {
			throw new Error(`API request failed with status ${response.status}`);
		}
		return response.json();
	}

	async get<T>(endpoint: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET' });
	}

	async post<T>(endpoint: string, body: any = {}): Promise<T> {
		return this.request<T>(endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(body),
		});
	}
}

const token =
  typeof document !== "undefined"
    ? document.cookie
        .split("; ")
        .find((row) => row.startsWith("token="))
        ?.split("=")[1] || null
    : null;

const client = new ApiClient(
  process.env.NEXT_API_URL || 'http://localhost:8000/api',
  token,
);

export default client;
