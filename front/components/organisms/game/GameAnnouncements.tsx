import type { GameAnnouncement } from "@/contexts/GameContext";

type GameAnnouncementsProps = {
  regularAnnouncements: GameAnnouncement[];
  giantAnnouncement: GameAnnouncement | null;
  selectedAttackerId: string | null;
};

export default function GameAnnouncements({
  regularAnnouncements,
  giantAnnouncement,
  selectedAttackerId,
}: GameAnnouncementsProps) {
  return (
    <>
      <div className="pointer-events-none absolute left-1/2 top-30 z-20 flex w-full max-w-md -translate-x-1/2 flex-col gap-2 px-4 lg:top-4">
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
        {selectedAttackerId && (
          <div className="rounded-full border border-blue-300/60 bg-blue-500/20 text-blue-100 px-4 py-2 text-center text-sm font-semibold shadow-lg backdrop-blur-sm">
            Choisis une cible pour attaquer
          </div>
        )}
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
    </>
  );
}
