"use client";

import { FaRegCircleQuestion } from "react-icons/fa6";

export default function GameHelpTooltip() {
  return (
    <div className="group absolute top-4 right-4 z-40">
      <button
        type="button"
        aria-label="Aide"
        className="flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white shadow-md cursor-help transition-colors hover:bg-black/70"
      >
        <FaRegCircleQuestion className="h-6 w-6" />
      </button>

      <div className="pointer-events-none absolute right-0 mt-2 w-[min(90vw,28rem)] rounded-xl border border-white/25 bg-black/85 p-4 text-sm text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100">
        Pour gagner, vous devez réduire les points de vie de la carte personnage
        adverse à 0. A chaque tour, vous piochez une carte et gagnez de
        l&apos;or. L&apos;or sert à jouer vos cartes. Pour cibler une carte avec
        une des votres, cliquez d&apos;abord sur votre carte puis sur la cible.
        Vous pouvez aussi double-cliquer sur une carte pour l'afficher en grand.
        Cliquez en dehors de la carte pour dézoomer.
      </div>
    </div>
  );
}
