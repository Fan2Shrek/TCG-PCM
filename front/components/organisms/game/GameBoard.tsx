import PlayerPanel from "@/components/atoms/game/PlayerPanel";
import BoardRow from "@/components/molecules/game/BoardRow";
import { GameContext } from "@/context/GameContext";
import type { GameAnnouncement } from "@/context/GameContext";
import { getCurrentUser } from "@/lib/utils";
import { emitter } from "@/lib/eventBus";
import { useContext, useEffect, useMemo, useRef, useState } from "react";
import CardsHand from "../CardsHand";
import PlayerHealthBar from "@/components/molecules/game/PlayerHealthBar";

export default () => {
  const { game, getCardById, announcements, actions } = useContext(GameContext);
  const playBoxRef = useRef<HTMLDivElement>(null);
  const [selectedAttackerId, setSelectedAttackerId] = useState<string | null>(
    null,
  );
  const giantAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation === "giant",
  );
  const giantAnnouncement =
    giantAnnouncements[giantAnnouncements.length - 1] ?? null;
  const regularAnnouncements = announcements.filter(
    (announcement: GameAnnouncement) => announcement.presentation !== "giant",
  );

  useEffect(() => {
    const handler = (data: { id: string }) => {
      const rect = playBoxRef.current?.getBoundingClientRect();

      if (!rect) {
        return;
      }

      const isInside = true;

      isInside && actions.playCard(data.id);
    };

    emitter.on("card:played", handler);

    return () => emitter.off("card:played", handler);
  }, [actions]);

  if (!game) {
    return <div>Loading...</div>;
  }

  const connectedPlayer =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player1.player.id
      : game.player2.player.id;

  const currentState =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player1
      : game.player2;
  const opponentState =
    game.player1.player.name === getCurrentUser()?.username
      ? game.player2
      : game.player1;
  const selectedAttackerCard = useMemo(
    () => (selectedAttackerId ? getCardById(selectedAttackerId) : undefined),
    [getCardById, selectedAttackerId],
  );

  useEffect(() => {
    if (!selectedAttackerId) {
      return;
    }

    if (selectedAttackerCard?.isActive === false) {
      setSelectedAttackerId(null);
    }
  }, [selectedAttackerCard, selectedAttackerId]);

  const handleSelectAttacker = (cardId: string) => {
    if (selectedAttackerId === cardId) {
      setSelectedAttackerId(null);
      return;
    }

    setSelectedAttackerId(cardId);
  };

  const handleAttackTarget = (targetId: string) => {
    if (!selectedAttackerId) {
      return;
    }

    actions.attack(selectedAttackerId, targetId);
    setSelectedAttackerId(null);
  };

  return (
    <div className="relative flex flex-col h-screen bg-green-900 text-white">
      <div className="pointer-events-none absolute left-1/2 top-4 z-20 flex w-full max-w-md -translate-x-1/2 flex-col gap-2 px-4">
        {regularAnnouncements.map((announcement: GameAnnouncement) => (
          <div
            key={announcement.id}
            className={`rounded-full border px-4 py-2 text-center text-sm font-semibold shadow-lg backdrop-blur-sm ${
              announcement.tone === "positive"
                ? "border-emerald-300/60 bg-emerald-500/20 text-emerald-100"
                : announcement.tone === "negative"
                  ? "border-rose-300/60 bg-rose-500/20 text-rose-100"
                  : "border-white/20 bg-black/30 text-white"
            }`}
          >
            {announcement.text}
          </div>
        ))}
      </div>

      {giantAnnouncement && (
        <div className="pointer-events-none absolute inset-0 z-30 flex items-center justify-center px-6">
          <div className="flex min-h-64 min-w-64 flex-col items-center justify-center rounded-[2.5rem] border border-white/20 bg-black/50 px-10 py-8 text-center shadow-[0_0_60px_rgba(255,255,255,0.18)] backdrop-blur-md">
            <div className="text-5xl sm:text-6xl">🎲</div>
            <div className="mt-4 text-7xl font-black leading-none tracking-tight text-white drop-shadow-[0_0_18px_rgba(255,255,255,0.55)] sm:text-[8rem]">
              {giantAnnouncement.text.replace(/^🎲\s*/, "")}
            </div>
          </div>
        </div>
      )}

      <PlayerHealthBar
        health={opponentState.healthPoints}
        maxHealth={opponentState.maxHealthPoints}
      />
      <div className="flex justify-center p-4 border-b border-green-700">
        <button
          type="button"
          onClick={() => handleAttackTarget(opponentState.player.id)}
          disabled={!selectedAttackerId}
          className={`rounded-xl transition ${selectedAttackerId ? "cursor-pointer hover:scale-[1.01]" : "cursor-not-allowed"} ${selectedAttackerCard ? "ring-4 ring-red-400 ring-offset-2 ring-offset-green-900" : ""}`}
          aria-label={`Attaquer ${opponentState.player.name}`}
        >
          <PlayerPanel player={opponentState} />
        </button>
      </div>

      <div
        ref={playBoxRef}
        className="flex flex-1 flex-col items-center justify-center gap-6"
      >
        <BoardRow
          title="Player 2 Monsters"
          cards={opponentState.playArea.monsterCards}
          clickable={!!selectedAttackerId}
          onCardClick={handleAttackTarget}
        />
        <BoardRow
          title="Player 2 Passive"
          cards={opponentState.playArea.passiveCards}
        />

        <BoardRow
          title="Player 1 Monsters"
          cards={currentState.playArea.monsterCards}
          clickable
          onCardClick={handleSelectAttacker}
          selectedCardId={selectedAttackerId}
          isCardDisabled={(cardId) => getCardById(cardId)?.isActive === false}
        />
        <BoardRow
          title="Player 1 Passive"
          cards={currentState.playArea.passiveCards}
        />
      </div>

      <div className="border-t border-green-700 p-4">
        <PlayerPanel player={currentState} />

        <div className="mt-3 text-center text-sm text-white/70">
          {selectedAttackerId
            ? "Choisis une cible pour attaquer"
            : "Choisis un monstre pour attaquer"}
        </div>

        <div className="flex gap-2 mt-4 justify-center">
          <CardsHand
            cards={currentState.hand.map((cardId: string) =>
              getCardById(cardId),
            )}
          />
        </div>
        <PlayerHealthBar
          health={currentState.healthPoints}
          maxHealth={currentState.maxHealthPoints}
        />
      </div>
      {connectedPlayer == game.currentPlayer && (
        <button
          className="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded"
          onClick={actions.endTurn}
        >
          end
        </button>
      )}
    </div>
  );
}
