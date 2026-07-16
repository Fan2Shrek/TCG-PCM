"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/button";

const COOKIE_CONSENT_STORAGE_KEY = "cookie-consent";

export default function CookieConsentBanner() {
  const [isMounted, setIsMounted] = useState(false);
  const [consent, setConsent] = useState<string | null>(null);

  useEffect(() => {
    setIsMounted(true);
    setConsent(localStorage.getItem(COOKIE_CONSENT_STORAGE_KEY));
  }, []);

  function acknowledgeConsent() {
    localStorage.setItem(COOKIE_CONSENT_STORAGE_KEY, "acknowledged");
    setConsent("acknowledged");
  }

  if (!isMounted || consent) {
    return null;
  }

  return (
    <div className="fixed inset-x-0 bottom-0 z-[100] flex justify-center px-3 pb-3">
      <div className="flex w-full max-w-3xl flex-col items-center gap-3 rounded-2xl border-2 border-ink-outline bg-card p-4 text-sm shadow-[var(--sticker-shadow-lg)] md:flex-row md:justify-between">
        <p className="text-muted-foreground">
          Ce site utilise uniquement des cookies essentiels au fonctionnement du
          Service (authentification, session de jeu). Aucun cookie de mesure
          d&apos;audience ou publicitaire n&apos;est déposé. En savoir plus sur
          notre{" "}
          <Link
            href="/legal/cgu"
            className="font-semibold text-primary underline"
          >
            page CGU
          </Link>
          .
        </p>
        <Button onClick={acknowledgeConsent} className="shrink-0">
          J&apos;ai compris
        </Button>
      </div>
    </div>
  );
}
