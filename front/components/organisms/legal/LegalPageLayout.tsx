import type { ReactNode } from "react";

type LegalPageLayoutProps = {
  title: string;
  subtitle?: string;
  children: ReactNode;
};

export default function LegalPageLayout({ title, subtitle, children }: LegalPageLayoutProps) {
  return (
    <main className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 pb-12">
      <header className="text-center text-white">
        <h1 className="text-3xl font-extrabold drop-shadow-md md:text-4xl">{title}</h1>
        {subtitle && <p className="mt-2 text-white/80">{subtitle}</p>}
      </header>

      <div className="w-full rounded-2xl border-2 border-slate-400/40 bg-slate-200/75 p-6 shadow-[0_14px_40px_-22px_rgba(15,23,42,0.55)] backdrop-blur-sm md:p-8">
        <div className="space-y-6">{children}</div>
      </div>
    </main>
  );
}
