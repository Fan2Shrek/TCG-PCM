type NewCardBadgeProps = {
  className?: string;
};

export default function NewCardBadge({ className }: NewCardBadgeProps) {
  return (
    <p
      className={`rounded-full border border-yellow-300/70 bg-yellow-100 text-yellow-900 uppercase tracking-wide shadow-sm font-extrabold
        md:whitespace-nowrap md:min-w-max px-4 py-1 text-xs} ${className ?? ""}`}
    >
      <span className="md:hidden">New!</span>
      <span className="hidden md:inline">Nouvelle carte!</span>
    </p>
  );
}
