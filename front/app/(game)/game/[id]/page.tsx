import { serverApiGet } from "@/lib/api/server";
import { getCurrentUser } from "@/lib/auth/session";
import { GameState } from "@/lib/game/type/gameState";
import GameBoard from "@/components/organisms/game/GameBoard";
import { GameProvider } from "@/contexts/GameContext";

type GameResponse = {
  state: GameState;
  mercure_token: string;
};

export default async function GamePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  const [{ state, mercure_token }, user] = await Promise.all([
    serverApiGet<GameResponse>(`/game/${id}`),
    getCurrentUser(),
  ]);

  return (
    <GameProvider gameId={id} game={state} mercureToken={mercure_token} username={user?.username}>
      <GameBoard />
    </GameProvider>
  );
}
