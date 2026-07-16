import { TbGhost3, TbSwords, TbUserStar, TbShieldCheckeredFilled } from "react-icons/tb";

export default function CombatDiagram() {
  return (
    <div className="flex flex-col items-center gap-3">
      <div className="flex w-full flex-col items-center justify-center gap-4 rounded-2xl border-2 border-ink-outline bg-muted p-4 sm:flex-row">
        <div className="flex flex-col items-center gap-1 rounded-xl border-2 border-ink-outline bg-cherry/15 px-4 py-3">
          <TbGhost3 className="text-3xl text-cherry" />
          <p className="text-sm font-bold text-cherry">Votre monstre</p>
          <p className="text-xs text-muted-foreground">disponible (non épuisé)</p>
        </div>

        <div className="flex flex-col items-center text-primary">
          <TbSwords className="text-3xl" />
          <p className="text-xs font-semibold">attaque</p>
        </div>

        <div className="flex flex-col items-center gap-1 rounded-xl border-2 border-ink-outline bg-card px-4 py-3">
          <div className="flex gap-2">
            <TbGhost3 className="text-3xl text-muted-foreground" />
            <TbUserStar className="text-3xl text-muted-foreground" />
          </div>
          <p className="text-sm font-bold">Monstre ou personnage adverse</p>
          <p className="text-xs text-muted-foreground">encaisse les dégâts</p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
        <div className="flex items-center gap-2 rounded-xl border-2 border-ink-outline bg-muted p-3 text-sm">
          <TbSwords className="shrink-0 text-lg text-primary" />
          Les dégâts infligés = l&apos;attaque du monstre (bonus d&apos;effets inclus).
        </div>
        <div className="flex items-center gap-2 rounded-xl border-2 border-ink-outline bg-muted p-3 text-sm">
          <TbShieldCheckeredFilled className="shrink-0 text-lg text-primary" />
          Un monstre défenseur peut réduire les dégâts reçus (bouclier).
        </div>
        <div className="flex items-center gap-2 rounded-xl border-2 border-ink-outline bg-muted p-3 text-sm">
          <TbGhost3 className="shrink-0 text-lg text-primary" />
          Après avoir attaqué, un monstre s&apos;épuise jusqu&apos;à votre prochain tour.
        </div>
      </div>
    </div>
  );
}
