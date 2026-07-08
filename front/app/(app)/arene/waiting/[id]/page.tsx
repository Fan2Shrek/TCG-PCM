"use client";

import { use, useState } from "react";
import { useRouter } from "next/navigation";
import { MdContentCopy, MdPlayArrow } from "react-icons/md";
import useMercure from "@/hooks/useMercure";
import api from "@/lib/api/api";
import { Button } from "@/components/ui/button";
import { toast } from "sonner";

const WaitingPage = ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);
  const router = useRouter();
  const [opponent, setOpponent] = useState<string | null>(null);

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
      toast.success("ID de la salle copié!");
    } catch {
      toast.error("Erreur", { description: "Impossible de copier l'ID" });
    }
  };

  useMercure(`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${id}`, {
    opponent_joined: (message: { data: { opponent: string } }) => {
      setOpponent(message.data.opponent);
    },
  });

  return (
    <div className="flex flex-col items-center justify-center flex-1">
      {" "}
      <div className="w-full max-w-2xl rounded-lg bg-slate-100 border border-black/40 overflow-hidden">
        <div className="p-8">
          <h1 className="text-3xl font-bold text-black mb-8 text-center">
            En attente d'adversaire
          </h1>

          <div className="space-y-6 mb-8">
            <div className="rounded-lg border border-black/20 bg-black/5 p-6 text-center">
              <p className="text-sm text-black/60 mb-2">ID de la salle</p>
              <p className="text-xl font-mono text-black break-all">{id}</p>
            </div>

            <div className="rounded-lg border border-black/20 bg-black/5 p-6 text-center">
              {opponent ? (
                <>
                  <p className="text-sm text-black/60 mb-2">Adversaire</p>
                  <p className="text-xl font-semibold text-black">{opponent}</p>
                  <p className="text-xs text-green-600 mt-2">
                    ✓ Prêt à commencer
                  </p>
                </>
              ) : (
                <>
                  <p className="text-sm text-black/60 mb-2">En attente...</p>
                  <p className="text-black/40 text-lg">
                    Aucun adversaire pour le moment
                  </p>
                </>
              )}
            </div>
          </div>

          <div className="flex gap-3 justify-center">
            <Button onClick={handleCopy} variant="default" size="lg">
              <MdContentCopy className="h-5 w-5" />
              Copier l'ID
            </Button>
            {opponent && (
              <Button onClick={handleStart} variant="default" size="lg">
                <MdPlayArrow className="h-5 w-5" />
                Démarrer le jeu
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default WaitingPage;
