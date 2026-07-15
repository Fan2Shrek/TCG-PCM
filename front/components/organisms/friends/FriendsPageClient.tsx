"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { MdSwapHoriz, MdPersonRemove, MdCheck, MdClose } from "react-icons/md";
import client from "@/lib/api/api";
import { getImage } from "@/lib/api/api";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useFriendship } from "@/contexts/FriendshipContext";
import { FriendshipUser } from "@/types/friendship";
import { useCurrentUser } from "@/hooks/useCurrentUser";

type Tab = "friends" | "requests" | "add";

const PlayerAvatar = ({ profilePicturePath }: { profilePicturePath?: string | null }) => (
  <div
    className="h-9 w-9 shrink-0 rounded-full bg-cover bg-center border border-black/20"
    style={{
      backgroundImage: `url(${profilePicturePath ? getImage(profilePicturePath) : "/menu/default_profile_picture.webp"})`,
    }}
  />
);

export default function FriendsPageClient() {
  const router = useRouter();
  const { user: currentUser } = useCurrentUser();
  const { friends, pendingRequests, refresh } = useFriendship();
  const [tab, setTab] = useState<Tab>("friends");
  const [search, setSearch] = useState("");
  const [results, setResults] = useState<FriendshipUser[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [isBusy, setIsBusy] = useState<string | null>(null);

  const handleSearch = async (query: string) => {
    setSearch(query);
    if (!query.trim()) {
      setResults([]);
      return;
    }
    setIsSearching(true);
    try {
      const found = await client.friend.search(query);
      setResults(found);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsSearching(false);
    }
  };

  const handleSend = async (username: string) => {
    setIsBusy(username);
    try {
      await client.friend.send(username);
      toast.success(`Demande envoyée à ${username}`);
      setResults((prev) => prev.filter((u) => u.username !== username));
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsBusy(null);
    }
  };

  const handleAccept = async (id: string) => {
    setIsBusy(id);
    try {
      await client.friend.accept(id);
      await refresh();
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsBusy(null);
    }
  };

  const handleDecline = async (id: string) => {
    setIsBusy(id);
    try {
      await client.friend.decline(id);
      await refresh();
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsBusy(null);
    }
  };

  const handleRemove = async (id: string) => {
    setIsBusy(id);
    try {
      await client.friend.remove(id);
      await refresh();
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsBusy(null);
    }
  };

  const handleTrade = async (friendId: number) => {
    setIsBusy(String(friendId));
    try {
      const { id } = await client.trade.create(friendId);
      router.push(`/trades/${id}`);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsBusy(null);
    }
  };

  return (
    <div className="mx-auto w-full max-w-2xl p-4">
      <div className="mb-4 flex gap-2">
        <Button variant={"friends" === tab ? "default" : "outline"} onClick={() => setTab("friends")}>
          Amis ({friends.length})
        </Button>
        <Button variant={"requests" === tab ? "default" : "outline"} onClick={() => setTab("requests")}>
          Demandes ({pendingRequests.length})
        </Button>
        <Button variant={"add" === tab ? "default" : "outline"} onClick={() => setTab("add")}>
          Ajouter
        </Button>
      </div>

      {"friends" === tab && (
        <div className="space-y-2">
          {0 === friends.length && <p className="text-black/60">Vous n&apos;avez pas encore d&apos;amis.</p>}
          {friends.map((friendship) => {
            const friend =
              friendship.requester.username === currentUser?.username
                ? friendship.addressee
                : friendship.requester;

            return (
              <div key={friendship.id} className="flex items-center justify-between rounded-lg border border-black/20 bg-slate-100 p-3">
                <div className="flex items-center gap-3">
                  <PlayerAvatar profilePicturePath={friend.profilePicturePath} />
                  <span className="text-black">{friend.username}</span>
                </div>
                <div className="flex gap-2">
                  <Button size="sm" onClick={() => handleTrade(friend.id)} disabled={isBusy === String(friend.id)}>
                    <MdSwapHoriz /> Échanger
                  </Button>
                  <Button
                    size="sm"
                    variant="destructive"
                    onClick={() => handleRemove(friendship.id)}
                    disabled={isBusy === friendship.id}
                  >
                    <MdPersonRemove />
                  </Button>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {"requests" === tab && (
        <div className="space-y-2">
          {0 === pendingRequests.length && <p className="text-black/60">Aucune demande en attente.</p>}
          {pendingRequests.map((request) => (
            <div key={request.id} className="flex items-center justify-between rounded-lg border border-black/20 bg-slate-100 p-3">
              <div className="flex items-center gap-3">
                <PlayerAvatar profilePicturePath={request.requester.profilePicturePath} />
                <span className="text-black">{request.requester.username}</span>
              </div>
              <div className="flex gap-2">
                <Button size="sm" onClick={() => handleAccept(request.id)} disabled={isBusy === request.id}>
                  <MdCheck /> Accepter
                </Button>
                <Button size="sm" variant="destructive" onClick={() => handleDecline(request.id)} disabled={isBusy === request.id}>
                  <MdClose /> Refuser
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}

      {"add" === tab && (
        <div className="space-y-3">
          <Input placeholder="Rechercher un pseudo..." value={search} onChange={(event) => handleSearch(event.target.value)} />
          {isSearching && <p className="text-black/60">Recherche...</p>}
          <div className="space-y-2">
            {results.map((user) => (
              <div key={user.id} className="flex items-center justify-between rounded-lg border border-black/20 bg-slate-100 p-3">
                <div className="flex items-center gap-3">
                  <PlayerAvatar profilePicturePath={user.profilePicturePath} />
                  <span className="text-black">{user.username}</span>
                </div>
                <Button size="sm" onClick={() => handleSend(user.username)} disabled={isBusy === user.username}>
                  Ajouter
                </Button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
