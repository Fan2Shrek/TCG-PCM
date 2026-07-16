import type { ReactNode } from "react";
import { TbBolt, TbGhost2, TbAlertTriangleFilled } from "react-icons/tb";

type EffectInfo = {
  icon: ReactNode;
  name: string;
  colorClass: string;
  description: string;
};

const EFFECTS: EffectInfo[] = [
  {
    icon: <TbGhost2 />,
    name: "Hacked",
    colorClass: "border-fuchsia-300 bg-fuchsia-50 text-fuchsia-700",
    description: "Multiplie une statistique de la carte (attaque, coût...) à la hausse ou à la baisse.",
  },
  {
    icon: <TbAlertTriangleFilled />,
    name: "Torned",
    colorClass: "border-orange-300 bg-orange-50 text-orange-700",
    description: "30% de chances d'annuler l'action de la carte affectée à chaque tentative (jet de dé caché).",
  },
  {
    icon: <TbBolt />,
    name: "Power Boost",
    colorClass: "border-violet-300 bg-violet-50 text-violet-700",
    description: "Augmente l'attaque d'un monstre tant que l'effet est actif.",
  },
];

export default function EffectsShowcase() {
  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
      {EFFECTS.map((effect) => (
        <div key={effect.name} className={`rounded-xl border-2 p-4 ${effect.colorClass}`}>
          <div className="mb-1 flex items-center gap-2 font-bold">
            <span className="text-xl">{effect.icon}</span>
            {effect.name}
          </div>
          <p className="text-sm text-current/70">{effect.description}</p>
        </div>
      ))}
    </div>
  );
}
