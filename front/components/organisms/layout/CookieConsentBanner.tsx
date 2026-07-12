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
      <div className="flex w-full max-w-3xl flex-col items-center gap-3 rounded-2xl border-2 border-slate-400/40 bg-slate-100 p-4 text-sm text-black shadow-[0_14px_40px_-22px_rgba(15,23,42,0.55)] md:flex-row md:justify-between">
        <p className="text-black/70">
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
