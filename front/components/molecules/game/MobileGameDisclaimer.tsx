"use client";

import { useState } from "react";
import { RxCross1 } from "react-icons/rx";

type MobileGameDisclaimerProps = {
  isVisible: boolean;
};

export default function MobileGameDisclaimer({
  isVisible,
}: MobileGameDisclaimerProps) {
  const [isDismissed, setIsDismissed] = useState(false);

  if (!isVisible || isDismissed) {
    return null;
  }

  return (
    <div className="absolute top-4 left-1/2 z-40 w-[90vw] max-w-xl -translate-x-1/2 rounded-lg border border-white/25 bg-black/85 px-4 py-2 pr-5 text-center text-sm text-white">
      <button
        type="button"
        aria-label="Fermer"
        onClick={() => setIsDismissed(true)}
        className="absolute right-2 top-2 text-lg leading-none text-white/80 transition hover:text-white cursor-pointer"
      >
        <RxCross1 />
      </button>
      Cette expérience est conçue pour ordinateur.
    </div>
  );
}
