import { PlayerActionType } from "@/lib/game/type/playerAction";
import { ChatMessage } from "@/lib/game/type/chatMessage";
import { ApiClient } from "../api";

export class GameResource {
	constructor(private client: ApiClient) {
	}

	async getGame(id: string) {
		return this.client.get(`/game/${id}`)
	}

	async play(id: string, action: PlayerActionType, payload: unknown = {}) {
		return this.client.post(`/game/${id}/play`, {
		  actionId: action,
		  payload,
		})
	}

	async getChatHistory(id: string) {
		return this.client.get<ChatMessage[]>(`/game/${id}/chat`)
	}

	async sendChatMessage(id: string, message: string) {
		return this.client.post(`/game/${id}/chat`, { message })
	}
}
