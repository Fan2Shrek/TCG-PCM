"use client";

type MobileGameDisclaimerProps = {
  isVisible: boolean;
};

export default function MobileGameDisclaimer({
  isVisible,
}: MobileGameDisclaimerProps) {
  if (!isVisible) {
    return null;
  }

  return (
    <div className="absolute top-4 left-1/2 z-40 w-[90vw] max-w-xl -translate-x-1/2 rounded-lg border border-white/25 bg-black/85 px-4 py-2 text-center text-sm text-white">
      Cette expérience est conçue pour ordinateur.
    </div>
  );
}
