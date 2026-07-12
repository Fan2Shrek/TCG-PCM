import type { ReactNode } from "react";
import { TbCoin, TbHeartFilled, TbCards, TbUsers } from "react-icons/tb";

type StatItem = {
  icon: ReactNode;
  label: string;
  value: string;
};

const STATS: StatItem[] = [
  {
    icon: <TbUsers />,
    label: "Joueurs",
    value: "2 (1 contre 1)",
  },
  {
    icon: <TbCards />,
    label: "Main de départ",
    value: "5 cartes piochées",
  },
  {
    icon: <TbCoin />,
    label: "Pièces de départ",
    value: "5 pièces",
  },
  {
    icon: <TbHeartFilled />,
    label: "Points de vie",
    value: "définis par le personnage (150 à 200 PV)",
  },
];

export default function GameSetupStats() {
  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
      {STATS.map((stat) => (
        <div
          key={stat.label}
          className="flex items-center gap-3 rounded-xl bg-black/5 border border-black/10 p-3"
        >
          <span className="text-2xl text-primary">{stat.icon}</span>
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-black/50">{stat.label}</p>
            <p className="font-bold text-black/80">{stat.value}</p>
          </div>
        </div>
      ))}
    </div>
  );
}
