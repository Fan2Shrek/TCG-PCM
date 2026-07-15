import { authApiGet } from "@/lib/api/authServer";
import { getCurrentUser } from "@/lib/auth/session";
import { GameState } from "@/lib/game/type/gameState";
import { ChatMessage } from "@/lib/game/type/chatMessage";
import GameBoard from "@/components/organisms/game/GameBoard";
import { GameProvider } from "@/contexts/GameContext";

type GameResponse = {
  state: GameState;
  mercure_token: string;
};

export default async function GamePage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  const [{ state, mercure_token }, chatHistory, user] = await Promise.all([
    authApiGet<GameResponse>(`/game/${id}`),
    authApiGet<ChatMessage[]>(`/game/${id}/chat`),
    getCurrentUser(),
  ]);

  return (
    <GameProvider
      gameId={id}
      game={state}
      mercureToken={mercure_token}
      username={user?.username}
      chatHistory={chatHistory}
    >
      <GameBoard />
    </GameProvider>
  );
}
