"use client";

import { useState } from "react";
import { MdCheckCircle, MdLogout } from "react-icons/md";
import { useCurrentUser } from "@/hooks/useCurrentUser";
import { useTrade } from "@/contexts/TradeContext";
import { Button } from "@/components/ui/button";
import { getImage } from "@/lib/api/api";
import TradeCardPicker from "./TradeCardPicker";

type PlayerAvatarProps = {
  profilePicturePath?: string | null;
};

const PlayerAvatar = ({ profilePicturePath }: PlayerAvatarProps) => (
  <div
    className="h-9 w-9 shrink-0 rounded-full bg-cover bg-center border border-black/20"
    style={{
      backgroundImage: `url(${profilePicturePath ? getImage(profilePicturePath) : "/menu/default_profile_picture.webp"})`,
    }}
  />
);

type OfferSlotProps = {
  title: string;
  card: string | null;
  confirmed: boolean;
  editable: boolean;
  onPick: () => void;
};

const OfferSlot = ({ title, card, confirmed, editable, onPick }: OfferSlotProps) => (
  <div className="flex-1 rounded-lg border border-black/20 bg-white/50 p-4">
    <div className="mb-3 flex items-center justify-between">
      <h3 className="text-sm font-semibold text-black">{title}</h3>
      {confirmed && (
        <span className="flex items-center gap-1 text-sm font-medium text-green-700">
          <MdCheckCircle /> Prêt
        </span>
      )}
    </div>

    {card ? (
      <div className="flex items-center justify-between rounded bg-black/5 p-3">
        <span className="font-mono text-sm text-black">{card}</span>
        {editable && (
          <Button onClick={onPick} variant="outline" size="sm">
            Changer
          </Button>
        )}
      </div>
    ) : editable ? (
      <Button onClick={onPick} variant="default" size="sm" className="w-full">
        Choisir une carte
      </Button>
    ) : (
      <p className="text-center text-sm text-black/50">En attente de sélection...</p>
    )}
  </div>
);

export default function TradeSessionClient() {
  const { user: currentUser } = useCurrentUser();
  const { trade, isLoading, isSubmitting, actions } = useTrade();
  const [showPicker, setShowPicker] = useState(false);

  if (isLoading || !trade) {
    return (
      <div className="flex flex-1 items-center justify-center">
        <p className="text-black/60">Chargement de l&apos;échange...</p>
      </div>
    );
  }

  const isInitiator = trade.initiator.username === currentUser?.username;
  const me = isInitiator ? trade.initiator : trade.recipient;
  const other = isInitiator ? trade.recipient : trade.initiator;
  const myCard = isInitiator ? trade.initiatorCard : trade.recipientCard;
  const otherCard = isInitiator ? trade.recipientCard : trade.initiatorCard;
  const myConfirmed = isInitiator ? trade.initiatorConfirmed : trade.recipientConfirmed;
  const otherConfirmed = isInitiator ? trade.recipientConfirmed : trade.initiatorConfirmed;

  const handleSelect = (cardId: string) => {
    setShowPicker(false);
    actions.offerCard(cardId);
  };

  return (
    <div className="flex flex-1 flex-col items-center justify-center p-4">
      <div className="w-full max-w-2xl rounded-lg bg-slate-100 border border-black/40 p-6">
        <div className="mb-6 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <PlayerAvatar profilePicturePath={me.profilePicturePath} />
            <span className="font-medium text-black">{me.username} (vous)</span>
          </div>
          <span className="text-black/40">contre</span>
          <div className="flex items-center gap-2">
            <span className="font-medium text-black">{other.username}</span>
            <PlayerAvatar profilePicturePath={other.profilePicturePath} />
          </div>
        </div>

        <div className="flex flex-col gap-4 sm:flex-row">
          <OfferSlot
            title="Votre offre"
            card={myCard}
            confirmed={myConfirmed}
            editable={"active" === trade.status}
            onPick={() => setShowPicker(true)}
          />
          <OfferSlot title={`Offre de ${other.username}`} card={otherCard} confirmed={otherConfirmed} editable={false} onPick={() => {}} />
        </div>

        {"active" === trade.status ? (
          <div className="mt-6 flex justify-end gap-2">
            <Button onClick={actions.cancel} variant="destructive" size="lg" disabled={isSubmitting}>
              <MdLogout className="h-5 w-5" />
              Annuler
            </Button>
            <Button
              onClick={actions.confirm}
              variant="default"
              size="lg"
              disabled={isSubmitting || !myCard || myConfirmed}
            >
              <MdCheckCircle className="h-5 w-5" />
              {myConfirmed ? "En attente de l'autre joueur" : "Confirmer"}
            </Button>
          </div>
        ) : (
          <p className="mt-6 text-center text-black/60">
            {"completed" === trade.status ? "Échange finalisé." : "Échange annulé."}
          </p>
        )}
      </div>

      {showPicker && <TradeCardPicker onSelect={handleSelect} onClose={() => setShowPicker(false)} />}
    </div>
  );
}
