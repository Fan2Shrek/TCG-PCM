"use client";

import { PlayerActionType } from "@/lib/game/type/playerAction";

export async function playGameAction(gameId: string, actionId: PlayerActionType, payload: unknown = {}) {
  const response = await fetch(`/api/game/${gameId}/play`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ actionId, payload }),
  });

  if (!response.ok) {
    const errorBody = (await response.json().catch(() => null)) as { detail?: string } | null;
    throw new Error(errorBody?.detail || `API request failed with status ${response.status}`);
  }

  if (response.status === 204) {
    return {};
  }

  return response.json();
}
