"use client";

import { Booster } from "@/app/types/booster";
import InteractiveBooster from "@/components/molecules/boosters/InteractiveBooster";
import { BoosterType } from "@/constants/booster";
import { useBoosterCarousel } from "@/hooks/useBoosterCarousel";

const BOOSTERS: Booster[] = [
  {
    id: "btd",
    boosterType: BoosterType.BTD,
    description: "Monkey business.",
  },
  {
    id: "original",
    boosterType: BoosterType.ORIGINAL,
    description: "The classic experience.",
  },
  {
    id: "isaac",
    boosterType: BoosterType.ISAAC,
    description: "Tears everywhere.",
  },
  // {
  //   boosterId: "isaac2",
  //   boosterType: BoosterType.ISAAC,
  //   description: "Tears everywhere.",
  // },
  // {
  //   boosterId: "isaac3",
  //   boosterType: BoosterType.ISAAC,
  //   description: "Tears everywhere.",
  // },
];

export default function BoostersPage() {
  const { frontBooster, rotateTo, getBoosterStyle } =
    useBoosterCarousel(BOOSTERS);

  return (
    <div className="min-h-screen ">
      <div className="flex min-h-screen flex-col items-center justify-center gap-20 overflow-hidden">
        <div className="relative w-full h-120" style={{ perspective: 1800 }}>
          {BOOSTERS.map((booster, index) => (
            <div
              key={booster.id}
              className="absolute transition-all duration-700 ease-out left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 "
              style={{
                ...getBoosterStyle(index),
                transformOrigin: "center",
              }}
            >
              <InteractiveBooster
                boosterType={booster.boosterType}
                onClick={() => rotateTo(index)}
                showGlare={booster.id === frontBooster.id}
                dimmed={booster.id !== frontBooster.id}
              />
            </div>
          ))}
        </div>

        <div
          key={frontBooster.id}
          className="text-center transition-all animate-in fade-in duration-300 bg-slate-200 max-w-lg w-full min-h-32 border-2 border-primary"
        >
          <p className="mt-2 text-lg">{frontBooster.description}</p>
        </div>
      </div>
    </div>
  );
}
