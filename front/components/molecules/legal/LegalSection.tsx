import type { ReactNode } from "react";

type LegalSectionProps = {
  title: string;
  children: ReactNode;
};

export default function LegalSection({ title, children }: LegalSectionProps) {
  return (
    <section className="space-y-3">
      <h2 className="text-lg font-bold text-black md:text-xl">{title}</h2>
      <div className="space-y-3 text-black/70">{children}</div>
    </section>
  );
}
