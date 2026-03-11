import { AuthResource } from "./resources/AuthResource";
import { BoosterResource } from "./resources/BoosterResource";

export class ApiClient {
	private baseUrl: string;

	auth: AuthResource;
  	booster: BoosterResource;

	constructor(baseUrl: string) {
		this.baseUrl = baseUrl;

		this.auth = new AuthResource(this);
		this.booster = new BoosterResource(this);
	}

	async request<T>(endpoint: string, options?: RequestInit): Promise<T> {
		const response = await fetch(`${this.baseUrl}${endpoint}`, options);
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

const client = new ApiClient(process.env.NEXT_API_URL || 'http://localhost:8000/api');

export default client;
