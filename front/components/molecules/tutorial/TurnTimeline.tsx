import type { ReactNode } from "react";
import { TbCoinFilled, TbCardsFilled, TbSwords, TbPlayerPlay, TbFlagFilled } from "react-icons/tb";

type TurnStep = {
  icon: ReactNode;
  title: string;
  description: string;
};

const STEPS: TurnStep[] = [
  {
    icon: <TbPlayerPlay />,
    title: "Début de tour",
    description: "Vos monstres épuisés redeviennent disponibles pour attaquer.",
  },
  {
    icon: <TbCoinFilled />,
    title: "Revenu",
    description: "Vous gagnez 3 pièces (certains personnages en donnent plus).",
  },
  {
    icon: <TbCardsFilled />,
    title: "Pioche",
    description: "Vous piochez automatiquement 1 carte.",
  },
  {
    icon: <TbSwords />,
    title: "Jouer / Attaquer",
    description: "Jouez des cartes en payant leur coût, et attaquez avec vos monstres disponibles.",
  },
  {
    icon: <TbFlagFilled />,
    title: "Fin de tour",
    description: "C'est au tour de l'adversaire. Quand les 2 joueurs ont joué, un nouveau round commence.",
  },
];

export default function TurnTimeline() {
  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
      {STEPS.map((step, index) => (
        <div key={step.title} className="relative flex flex-col items-center rounded-xl border-2 border-ink-outline bg-muted p-4 text-center">
          <span className="absolute -top-3 -left-3 flex h-7 w-7 items-center justify-center rounded-full border-2 border-ink-outline bg-primary text-sm font-bold text-white">
            {index + 1}
          </span>
          <span className="mb-2 text-3xl text-primary">{step.icon}</span>
          <p className="font-bold">{step.title}</p>
          <p className="mt-1 text-sm text-muted-foreground">{step.description}</p>
        </div>
      ))}
    </div>
  );
}
