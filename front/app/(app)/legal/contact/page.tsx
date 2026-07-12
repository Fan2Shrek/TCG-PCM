import type { Metadata } from "next";
import { TbMail } from "react-icons/tb";
import LegalPageLayout from "@/components/organisms/legal/LegalPageLayout";
import LegalSection from "@/components/molecules/legal/LegalSection";

export const metadata: Metadata = {
  title: "Contact",
};

const CONTACT_EMAIL = "tcg@contact.fr";

export default function ContactPage() {
  return (
    <LegalPageLayout title="Contact">
      <LegalSection title="Nous contacter">
        <p>
          Pour toute question, remarque ou signalement concernant le Service
          (bug, compte, contenu inapproprié, demande liée à vos données
          personnelles), vous pouvez nous écrire à l&apos;adresse suivante
          :
        </p>
        <a
          href={`mailto:${CONTACT_EMAIL}`}
          className="inline-flex items-center gap-2 text-lg font-semibold text-primary underline"
        >
          <TbMail className="text-xl" />
          {CONTACT_EMAIL}
        </a>
      </LegalSection>

      <LegalSection title="Délai de réponse">
        <p>
          Nous nous efforçons de répondre à toute demande dans un délai
          raisonnable. Merci de préciser votre nom d&apos;utilisateur ainsi
          qu&apos;une description claire de votre demande.
        </p>
      </LegalSection>
    </LegalPageLayout>
  );
}
