import type { Metadata } from "next";
import LegalPageLayout from "@/components/organisms/legal/LegalPageLayout";
import LegalSection from "@/components/molecules/legal/LegalSection";

export const metadata: Metadata = {
  title: "Conditions Générales d'Utilisation",
};

export default function CguPage() {
  return (
    <LegalPageLayout
      title="Conditions Générales d'Utilisation"
      subtitle="En vigueur au 12/07/2026"
    >
      <LegalSection title="1. Objet">
        <p>
          Les présentes Conditions Générales d&apos;Utilisation (CGU) régissent
          l&apos;accès et l&apos;utilisation du jeu de cartes à collectionner
          (ci-après le &laquo;&nbsp;Service&nbsp;&raquo;). En créant un compte
          ou en utilisant le Service, l&apos;utilisateur accepte sans réserve
          les présentes CGU.
        </p>
      </LegalSection>

      <LegalSection title="2. Accès au service">
        <p>
          Le Service est accessible gratuitement à tout utilisateur disposant
          d&apos;un accès à internet. L&apos;inscription nécessite la création
          d&apos;un compte avec une adresse e-mail et un mot de passe valides.
          L&apos;utilisateur est responsable de la confidentialité de ses
          identifiants de connexion.
        </p>
      </LegalSection>

      <LegalSection title="3. Compte utilisateur">
        <p>
          Chaque utilisateur ne peut créer et utiliser qu&apos;un seul compte.
          Les informations fournies lors de l&apos;inscription doivent être
          exactes et à jour. Le Service se réserve le droit de suspendre ou
          supprimer tout compte en cas de non-respect des présentes CGU.
        </p>
      </LegalSection>

      <LegalSection title="4. Contenu et propriété intellectuelle">
        <p>
          L&apos;ensemble des éléments du Service (cartes, illustrations,
          textes, logos, code source) est protégé par le droit de la propriété
          intellectuelle. Toute reproduction, représentation ou exploitation
          non autorisée est interdite.
        </p>
      </LegalSection>

      <LegalSection title="5. Comportement des utilisateurs">
        <p>
          L&apos;utilisateur s&apos;engage à ne pas exploiter de faille, à ne
          pas tenter d&apos;accéder aux systèmes du Service de manière non
          autorisée, et à adopter un comportement respectueux envers les
          autres joueurs.
        </p>
      </LegalSection>

      <LegalSection title="6. Responsabilité">
        <p>
          Le Service est fourni &laquo;&nbsp;en l&apos;état&nbsp;&raquo;, sans
          garantie de disponibilité continue. L&apos;éditeur ne saurait être
          tenu responsable des interruptions de service, pertes de données ou
          dommages indirects résultant de l&apos;utilisation du Service.
        </p>
      </LegalSection>

      <LegalSection title="7. Modification des CGU">
        <p>
          Les présentes CGU peuvent être modifiées à tout moment. Les
          utilisateurs seront informés de toute modification substantielle. La
          poursuite de l&apos;utilisation du Service après modification vaut
          acceptation des nouvelles CGU.
        </p>
      </LegalSection>

      <LegalSection title="8. Contact">
        <p>
          Pour toute question relative aux présentes CGU, vous pouvez nous
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
