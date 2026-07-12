import Link from "next/link";
import {
  TbTargetArrow,
  TbClipboardList,
  TbRefresh,
  TbCards,
  TbSwords,
  TbCoinFilled,
  TbSparkles,
  TbStarsFilled,
  TbPlayCardStar,
} from "react-icons/tb";
import TutorialSection from "@/components/molecules/tutorial/TutorialSection";
import GameSetupStats from "@/components/molecules/tutorial/GameSetupStats";
import TurnTimeline from "@/components/molecules/tutorial/TurnTimeline";
import CardTypesShowcase from "@/components/molecules/tutorial/CardTypesShowcase";
import CombatDiagram from "@/components/molecules/tutorial/CombatDiagram";
import RarityTable from "@/components/molecules/tutorial/RarityTable";
import EffectsShowcase from "@/components/molecules/tutorial/EffectsShowcase";
import type { DeckLimits } from "@/app/types/deck";
import { serverApiGet } from "@/lib/api/server";

export default async function HowToPlayPage() {
  const deckLimits = await serverApiGet<DeckLimits>("/decks/limits").catch(() => null);

  return (
    <main className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 pb-12">
      <header className="text-center text-white">
        <h1 className="text-3xl font-extrabold drop-shadow-md md:text-4xl">
          Comment jouer
        </h1>
        <p className="mt-2 text-white/80">Le guide complet des règles du jeu</p>
      </header>

      <TutorialSection icon={<TbTargetArrow />} title="Objectif">
        <p>
          Chaque partie oppose <strong>2 joueurs</strong>. Vous incarnez un
          personnage avec un nombre de points de vie (PV) fixe. Faites tomber
          les PV de l&apos;adversaire à 0 pour gagner la partie.
        </p>
      </TutorialSection>

      <TutorialSection icon={<TbClipboardList />} title="Mise en place">
        <p>
          Avant le combat, chaque joueur choisit son deck de 50 cartes et son
          personnage dans un salon (
          <Link href="/rooms" className="font-semibold text-primary underline">
            room
          </Link>
          ). Une fois la partie lancée :
        </p>
        <GameSetupStats />
      </TutorialSection>

      <TutorialSection icon={<TbRefresh />} title="Déroulement d'un tour">
        <p>
          Le premier joueur commence, puis les tours alternent. Quand les deux
          joueurs ont joué, un nouveau round démarre.
        </p>
        <TurnTimeline />
      </TutorialSection>

      <TutorialSection icon={<TbCards />} title="Les types de cartes">
        <p>
          Il existe 4 types de cartes, chacune avec son propre rôle sur le
          plateau.
        </p>
        <CardTypesShowcase />
      </TutorialSection>

      <TutorialSection icon={<TbSwords />} title="Le combat">
        <p>
          Seuls les monstres peuvent attaquer : soit un monstre adverse, soit
          directement le personnage adverse.
        </p>
        <CombatDiagram />
      </TutorialSection>

      <TutorialSection icon={<TbCoinFilled />} title="Les pièces">
        <p>
          Jouer une carte coûte des pièces. Vous démarrez avec 5 pièces et en
          gagnez <strong>3 par tour</strong> (certains personnages en offrent
          davantage). Le coût d&apos;une carte dépend de sa rareté.
        </p>
      </TutorialSection>

      <TutorialSection icon={<TbSparkles />} title="Les effets">
        <p>
          Des cartes peuvent appliquer des effets qui modifient temporairement
          les statistiques ou le comportement d&apos;une carte.
        </p>
        <EffectsShowcase />
      </TutorialSection>

      <TutorialSection
        icon={<TbStarsFilled />}
        title="Rareté et construction de deck"
      >
        <p>
          La rareté d&apos;une carte détermine son coût de base et combien
          d&apos;exemplaires vous pouvez inclure dans un deck de 50 cartes.
        </p>
        {deckLimits && <RarityTable limits={deckLimits} />}
      </TutorialSection>

      <TutorialSection icon={<TbPlayCardStar />} title="Obtenir des cartes">
        <p>
          De nouvelles cartes s&apos;obtiennent en ouvrant des{" "}
          <Link
            href="/boosters"
            className="font-semibold text-primary underline"
          >
            boosters
          </Link>
          . Retrouvez ensuite toute votre collection dans{" "}
          <Link
            href="/inventory"
            className="font-semibold text-primary underline"
          >
            Mes cartes
          </Link>{" "}
          pour construire votre deck.
        </p>
      </TutorialSection>

      <div className="flex justify-center pt-2">
        <Link
          href="/rooms"
          className="rounded-full bg-primary px-8 py-3 text-lg font-bold text-white shadow-lg transition hover:brightness-110"
        >
          Lancer une partie
        </Link>
      </div>
    </main>
  );
}
