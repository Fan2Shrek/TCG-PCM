"use client";

import { use, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { MdContentCopy, MdPlayArrow, MdLogout } from "react-icons/md";
import { useCurrentUser } from "@/hooks/useCurrentUser";
import { useRoom } from "@/contexts/RoomContext";
import api from "@/lib/api/api";
import type { Deck } from "@/app/types/deck";
import { Button } from "@/components/ui/button";
import DeckSelect from "@/components/molecules/rooms/DeckSelect";
import { Switch } from "@/components/ui/switch";
import { toast } from "sonner";

const WaitingPage = ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);
  const router = useRouter();
  const { user: currentUser } = useCurrentUser();
  const {
    userRoom,
    isLoading: isContextLoading,
    lastEvent,
    clearRoom,
    refetchRoom,
  } = useRoom();

  const room = userRoom && userRoom.id === id ? userRoom : null;
  const [decks, setDecks] = useState<Deck[]>([]);
  const [selectedDeckId, setSelectedDeckId] = useState("");
  const [isDecksLoading, setIsDecksLoading] = useState(false);
  const [isChangingDeck, setIsChangingDeck] = useState(false);

  const isPrivate = room?.isPrivate ?? false;

  const playerCount = room?.opponent ? 2 : 1;
  const isOwner = room?.owner?.username === currentUser?.username;

  const sortedDecks = useMemo(
    () =>
      [...decks].sort((a, b) => {
        const favoriteDelta =
          Number(Boolean(b.isFavorite)) - Number(Boolean(a.isFavorite));

        if (favoriteDelta !== 0) {
          return favoriteDelta;
        }

        return a.name.localeCompare(b.name, "fr", { sensitivity: "base" });
      }),
    [decks],
  );

  useEffect(() => {
    let cancelled = false;

    const fetchDecks = async () => {
      setIsDecksLoading(true);
      try {
        const userDecks = await api.deck.listMine();
        if (!cancelled) {
          setDecks(userDecks);
        }
      } catch (error) {
        const message =
          error instanceof Error
            ? error.message
            : "Impossible de charger vos decks";
        toast.error("Erreur", { description: message });
      } finally {
        if (!cancelled) {
          setIsDecksLoading(false);
        }
      }
    };

    fetchDecks();

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!room || !currentUser || sortedDecks.length === 0) {
      return;
    }

    const activeDeckId =
      room.owner.username === currentUser.username
        ? room.ownerDeck?.id
        : room.opponent?.username === currentUser.username
          ? room.opponentDeck?.id
          : undefined;

    if (activeDeckId) {
      setSelectedDeckId(String(activeDeckId));
      return;
    }

    if (!selectedDeckId) {
      setSelectedDeckId(String(sortedDecks[0].id));
    }
  }, [currentUser, room, selectedDeckId, sortedDecks]);

  useEffect(() => {
    if (!isContextLoading && !room) {
      router.push("/rooms");
    }
  }, [isContextLoading, room, router]);

  useEffect(() => {
    if (!room && currentUser && lastEvent === "owner_left") {
      router.push("/rooms");
      toast.error("Le créateur a quitté la salle");
    }

    if (lastEvent === "game_started" && room) {
      router.push(`/game/${id}`);
    }

    if (lastEvent === "opponent_removed" && !isOwner && currentUser) {
      router.push("/rooms");
      toast.info("Vous avez été expulsé de la salle");
    }
  }, [room, currentUser, lastEvent, router, id, isOwner]);

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
      toast.success("ID de la salle copié");
    } catch {
      toast.error("Erreur", { description: "Impossible de copier l'ID" });
    }
  };

  const handleTogglePrivate = async (newValue: boolean) => {
    try {
      await api.room.togglePrivate(id, newValue);
      await refetchRoom();
      const status = newValue
        ? "La salle est maintenant privée"
        : "La salle est maintenant publique";
      toast.success(status);
    } catch (error) {
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
      clearRoom();
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
      toast.success("Adversaire expulsé");
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Erreur lors de l'expulsion";
      toast.error("Erreur", { description: message });
    }
  };

  const handleDeckChange = async (deckId: string) => {
    if (!deckId || !room) {
      return;
    }

    setSelectedDeckId(deckId);
    setIsChangingDeck(true);
    try {
      await api.room.changeDeck(room.id, deckId);
      await refetchRoom();
      toast.success("Deck sélectionné");
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Erreur lors du changement de deck";
      toast.error("Erreur", { description: message });
    } finally {
      setIsChangingDeck(false);
    }
  };

  return (
    <div className="flex flex-col items-center justify-center flex-1">
      {isContextLoading ? (
        <div className="text-center">
          <p className="text-black/60">Chargement de la salle...</p>
        </div>
      ) : !room ? (
        <div className="text-center">
          <p className="text-black/60">Salle non trouvée</p>
        </div>
      ) : (
        <div className="w-full max-w-3xl rounded-lg bg-slate-100 border border-black/40 overflow-hidden p-6">
          <div className="flex items-center justify-end">
            {isOwner && (
              <div className="flex items-center gap-3 pb-4">
                <label
                  htmlFor="private-toggle"
                  className="text-sm text-black/60"
                >
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
                  ID de la salle:{" "}
                  <span className="text-black text-xl">{id}</span>
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
                  {isOwner && room?.opponent && (
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
                  <span>{room?.owner.username}</span>
                  {room?.owner.username === currentUser?.username ? (
                    <DeckSelect
                      decks={sortedDecks}
                      value={selectedDeckId}
                      onChange={handleDeckChange}
                      isLoading={isDecksLoading}
                      disabled={isChangingDeck}
                    />
                  ) : null}
                </div>
                {room?.opponent && (
                  <div className="flex items-center justify-between p-3 rounded bg-white/50 text-black">
                    <span>{room.opponent.username}</span>
                    {room.opponent.username === currentUser?.username ? (
                      <DeckSelect
                        decks={sortedDecks}
                        value={selectedDeckId}
                        onChange={handleDeckChange}
                        isLoading={isDecksLoading}
                        disabled={isChangingDeck}
                      />
                    ) : null}
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
      )}
    </div>
  );
};

export default WaitingPage;
