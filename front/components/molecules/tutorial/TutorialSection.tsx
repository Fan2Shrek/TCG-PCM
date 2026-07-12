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
      className={`w-full rounded-2xl border-2 border-slate-400/40 bg-slate-200/75 p-6 shadow-[0_14px_40px_-22px_rgba(15,23,42,0.55)] backdrop-blur-sm md:p-8 ${className ?? ""}`}
    >
      <h2 className="mb-4 flex items-center gap-3 text-xl font-bold text-black md:text-2xl">
        <span className="text-2xl text-primary md:text-3xl">{icon}</span>
        {title}
      </h2>
      <div className="space-y-4 text-black/70">{children}</div>
    </section>
  );
}
