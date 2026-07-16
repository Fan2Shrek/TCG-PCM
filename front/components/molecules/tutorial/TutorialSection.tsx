import type { ReactNode } from "react";

type TutorialSectionProps = {
  icon: ReactNode;
  title: string;
  children: ReactNode;
  className?: string;
};

export default function TutorialSection({ icon, title, children, className }: TutorialSectionProps) {
  return (
    <section
      className={`w-full rounded-3xl border-2 border-ink-outline bg-card p-6 shadow-[var(--sticker-shadow-lg)] md:p-8 ${className ?? ""}`}
    >
      <h2 className="mb-4 flex items-center gap-3 font-display text-xl font-extrabold md:text-2xl">
        <span className="text-2xl text-primary md:text-3xl">{icon}</span>
        {title}
      </h2>
      <div className="space-y-4 text-muted-foreground">{children}</div>
    </section>
  );
}
