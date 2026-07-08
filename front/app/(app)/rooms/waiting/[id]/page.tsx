"use client";

import { use, useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { MdContentCopy, MdPlayArrow, MdLogout } from "react-icons/md";
import { useAuth } from "@/contexts/AuthContext";
import useMercure from "@/hooks/useMercure";
import api from "@/lib/api/api";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { toast } from "sonner";
import { Room } from "@/types/room";

const WaitingPage = ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);
  const router = useRouter();
  const { user: currentUser } = useAuth();
  const [room, setRoom] = useState<Room | null>(null);
  const [isPrivate, setIsPrivate] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchRoom = async () => {
      try {
        const data = await api.room.getById(id);
        setRoom(data as Room);
        setIsPrivate((data as Room).isPrivate);
      } catch (error) {
        const message =
          error instanceof Error ? error.message : "Erreur lors du chargement";
        toast.error("Erreur", { description: message });
      } finally {
        setIsLoading(false);
      }
    };
    fetchRoom();
  }, [id]);

  const handleStart = async () => {
    try {
      await api.room.start(id);
      router.push(`/game/${id}`);
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Erreur lors du démarrage du jeu";
      toast.error("Erreur", { description: message });
    }
  };

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(id);
      toast.success("ID de la salle copié avec succès!");
    } catch {
      toast.error("Erreur", { description: "Impossible de copier l'ID" });
    }
  };

  const handleTogglePrivate = async (newValue: boolean) => {
    try {
      setIsPrivate(newValue);
      await api.room.togglePrivate(id, newValue);
      const status = newValue
        ? "La salle est maintenant privée"
        : "La salle est maintenant publique";
      toast.success(status);
    } catch (error) {
      setIsPrivate(!newValue);
      const message =
        error instanceof Error
          ? error.message
          : "Erreur lors du changement de statut";
      toast.error("Erreur", { description: message });
    }
  };

  const handleLeave = async () => {
    try {
      await api.room.leave(id);
      router.push("/rooms");
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Erreur lors de la déconnexion";
      toast.error("Erreur", { description: message });
    }
  };

  const handleRemoveOpponent = async () => {
    try {
      await api.room.removeOpponent(id);
      setRoom((prev) => (prev ? { ...prev, opponent: null } : null));
      toast.success("Adversaire expulsé");
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Erreur lors de l'expulsion";
      toast.error("Erreur", { description: message });
    }
  };

  useMercure(`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${id}`, {
    opponent_joined: (message: {
      data: { opponent: { id: string; username: string } };
    }) => {
      setRoom((prev) =>
        prev
          ? {
              ...prev,
              opponent: message.data.opponent,
            }
          : null,
      );
    },
    opponent_removed: () => {
      router.push("/rooms");
      toast.info("Vous avez été expulsé de la salle");
    },
    owner_left: () => {
      router.push("/rooms");
      if (!isOwner) {
        toast.error("Le créateur a quitté la salle");
      }
    },
  });

  if (isLoading) {
    return (
      <div className="flex flex-col items-center justify-center flex-1">
        <p className="text-black/60">Chargement...</p>
      </div>
    );
  }

  if (!room) {
    return (
      <div className="flex flex-col items-center justify-center flex-1">
        <p className="text-black/60">Salle non trouvée</p>
      </div>
    );
  }

  const playerCount = room.opponent ? 2 : 1;
  const isOwner = currentUser?.username === room.owner.username;

  return (
    <div className="flex flex-col items-center justify-center flex-1">
      <div className="w-full max-w-3xl rounded-lg bg-slate-100 border border-black/40 overflow-hidden p-6">
        <div className="flex items-center justify-end">
          {isOwner && (
            <div className="flex items-center gap-3 pb-4">
              <label htmlFor="private-toggle" className="text-sm text-black/60">
                Salle privée
              </label>
              <Switch
                id="private-toggle"
                checked={isPrivate}
                onChange={handleTogglePrivate}
              />
            </div>
          )}
        </div>

        <div className="space-y-6">
          <div className="rounded-lg border border-black/20 bg-black/5 p-6">
            <div className="flex flex-row items-center justify-between gap-4">
              <p className="text-sm text-black/60">
                ID de la salle: <span className="text-black text-xl">{id}</span>
              </p>
              <Button onClick={handleCopy} variant="default" size="lg">
                <MdContentCopy className="h-5 w-5" />
                Copier l'ID
              </Button>
            </div>
          </div>

          <div className="rounded-lg border border-black/20 bg-black/5 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-semibold text-black">
                Joueurs ({playerCount}/2)
              </h3>
              <div className="flex gap-3">
                {room.opponent && (
                  <Button onClick={handleStart} variant="default" size="lg">
                    <MdPlayArrow className="h-5 w-5" />
                    Démarrer le jeu
                  </Button>
                )}
                <Button onClick={handleLeave} variant="destructive" size="lg">
                  <MdLogout className="h-5 w-5" />
                  Quitter
                </Button>
              </div>
            </div>
            <div className="space-y-2">
              <div className="flex items-center justify-between p-3 rounded bg-white/50 text-black">
                {room.owner.username}
              </div>
              {room.opponent && (
                <div className="flex items-center justify-between p-3 rounded bg-white/50 text-black">
                  <span>{room.opponent.username}</span>
                  {isOwner && (
                    <Button
                      onClick={handleRemoveOpponent}
                      variant="destructive"
                      size="sm"
                    >
                      Expulser
                    </Button>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WaitingPage;
