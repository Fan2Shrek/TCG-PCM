"use client";

import { use, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import api from "../../../../lib/api/api";
import { GameState } from "@/lib/game/type/gameState";
import GameBoard from "@/components/organisms/game/GameBoard";
import { GameProvider } from "@/contexts/GameContext";
import { useAuth } from "@/contexts/AuthContext";
import { useRoom } from "@/contexts/RoomContext";

export default ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);
  const router = useRouter();
  const { user: currentUser } = useAuth();
  const { userRoom } = useRoom();

  const [game, setGame] = useState<GameState | null>(null);
  const [isLoadingGame, setIsLoadingGame] = useState(true);

  useEffect(() => {
    const fetchGame = async () => {
      try {
        const data = (await api.game.getGame(id)) as any;
        setGame(data.state || data);

        if (data.mercure_token) {
          document.cookie = `mercureAuthorization=${data.mercure_token}; path=/; max-age=3600; secure; samesite=strict`;
        }
        setIsLoadingGame(false);
      } catch (error) {
        console.error("Failed to fetch game:", error);
        router.push("/rooms");
        toast.error("Partie non trouvée");
        setIsLoadingGame(false);
      }
    };

    fetchGame();
  }, [id, router]);

  useEffect(() => {
    if (!isLoadingGame && userRoom && currentUser && userRoom.id !== id) {
      router.push("/rooms");
      toast.error("Vous n'avez pas accès à cette partie");
    }
  }, [isLoadingGame, userRoom, id, currentUser, router]);

  if (isLoadingGame) {
    return (
      <div className="flex items-center justify-center h-screen">
        Chargement...
      </div>
    );
  }

  if (!game) {
    return (
      <div className="flex items-center justify-center h-screen">
        Partie non trouvée
      </div>
    );
  }

  return (
    <GameProvider gameId={id} game={game}>
      <GameBoard />
    </GameProvider>
  );
};
