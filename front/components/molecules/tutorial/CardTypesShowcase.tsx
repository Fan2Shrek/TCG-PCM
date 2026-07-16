import { TbUserStar, TbGhost3, TbFlask, TbShieldHalfFilled } from "react-icons/tb";
import { CardType } from "@/constants/card";

type CardTypeInfo = {
  type: CardType;
  icon: typeof TbUserStar;
  colorClass: string;
  label: string;
  description: string;
  stats: string[];
};

const CARD_TYPES: CardTypeInfo[] = [
  {
    type: CardType.CHARACTER,
    icon: TbUserStar,
    colorClass: "border-sky-300 bg-sky-50 text-sky-700",
    label: "Personnage",
    description: "Votre avatar pour la partie, choisi lors de la création du deck. Il n'est pas joué depuis la main.",
    stats: ["Définit vos PV de départ", "Donne souvent un pouvoir passif"],
  },
  {
    type: CardType.MONSTER,
    icon: TbGhost3,
    colorClass: "border-rose-300 bg-rose-50 text-rose-700",
    label: "Monstre",
    description: "Posé sur le plateau en payant son coût. Peut attaquer une fois par tour tant qu'il n'est pas épuisé.",
    stats: ["Coût en pièces", "Points de vie", "Attaque"],
  },
  {
    type: CardType.CONSUMABLE,
    icon: TbFlask,
    colorClass: "border-amber-300 bg-amber-50 text-amber-700",
    label: "Carte à usage unique",
    description: "Résout un effet immédiat (dégâts, soin, pioche...) puis part au cimetière.",
    stats: ["Coût en pièces", "Effet immédiat"],
  },
  {
    type: CardType.PASSIVE,
    icon: TbShieldHalfFilled,
    colorClass: "border-emerald-300 bg-emerald-50 text-emerald-700",
    label: "Carte passive",
    description: "Reste en jeu une fois posée et applique un effet permanent (zone, aura...).",
    stats: ["Coût en pièces", "Effet permanent"],
  },
];

export default function CardTypesShowcase() {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
      {CARD_TYPES.map(({ type, icon: Icon, colorClass, label, description, stats }) => (
        <div key={type} className={`rounded-xl border-2 p-4 ${colorClass}`}>
          <div className="mb-2 flex items-center gap-2">
            <Icon className="text-2xl" />
            <p className="font-bold">{label}</p>
          </div>
          <p className="text-sm text-current/70">{description}</p>
          <ul className="mt-2 flex flex-wrap gap-2">
            {stats.map((stat) => (
              <li key={stat} className="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold">
                {stat}
              </li>
            ))}
          </ul>
        </div>
      ))}
    </div>
  );
}
