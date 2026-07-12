import Link from "next/link";

const legalLinks = [
  { label: "CGU", href: "/legal/cgu" },
  { label: "CGV", href: "/legal/cgv" },
  { label: "Contact", href: "/legal/contact" },
];

export default function Footer() {
  return (
    <footer className="mt-auto flex flex-col items-center gap-2 px-4 py-6 text-sm text-white/70">
      <nav className="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
        {legalLinks.map((link) => (
          <Link key={link.href} href={link.href} className="underline-offset-4 hover:text-white hover:underline">
            {link.label}
          </Link>
        ))}
      </nav>
    </footer>
  );
}
