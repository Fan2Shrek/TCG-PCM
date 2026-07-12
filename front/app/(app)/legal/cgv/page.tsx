import type { Metadata } from "next";
import LegalPageLayout from "@/components/organisms/legal/LegalPageLayout";
import LegalSection from "@/components/molecules/legal/LegalSection";

export const metadata: Metadata = {
  title: "Conditions Générales de Vente",
};

export default function CgvPage() {
  return (
    <LegalPageLayout
      title="Conditions Générales de Vente"
      subtitle="En vigueur au 12/07/2026"
    >
      <LegalSection title="1. Objet">
        <p>
          À ce jour, le Service ne propose aucune vente de contenu payant :
          l&apos;accès au jeu, la création de compte, l&apos;ouverture de
          boosters et l&apos;ensemble des fonctionnalités sont entièrement
          gratuits. Les présentes Conditions Générales de Vente (CGV) ont
          vocation à s&apos;appliquer si une offre payante venait à être
          proposée à l&apos;avenir.
        </p>
      </LegalSection>

      <LegalSection title="2. Évolution de l'offre">
        <p>
          Si des produits ou services payants (boosters premium, cosmétiques,
          abonnements, etc.) sont introduits ultérieurement, les présentes CGV
          seront mises à jour pour préciser les prix, modalités de paiement,
          droit de rétractation et conditions de livraison applicables, avant
          toute mise en vente.
        </p>
      </LegalSection>

      <LegalSection title="3. Aucune monnaie réelle en jeu">
        <p>
          Les éléments virtuels du jeu (cartes, boosters, monnaie in-game) n&apos;ont
          aucune valeur monétaire réelle et ne peuvent faire l&apos;objet d&apos;un
          remboursement, d&apos;un échange contre de l&apos;argent réel ou d&apos;une
          revente en dehors du Service.
        </p>
      </LegalSection>

      <LegalSection title="4. Contact">
        <p>
          Pour toute question relative à ces conditions, vous pouvez nous
          contacter via la{" "}
          <a href="/legal/contact" className="font-semibold text-primary underline">
            page contact
          </a>
          .
        </p>
      </LegalSection>
    </LegalPageLayout>
  );
}
