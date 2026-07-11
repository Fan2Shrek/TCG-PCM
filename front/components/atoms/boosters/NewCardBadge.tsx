type NewCardBadgeProps = {
  compact?: boolean;
  className?: string;
};

export default function NewCardBadge({
  compact = false,
  className,
}: NewCardBadgeProps) {
  return (
    <p
      className={`rounded-full border border-yellow-300/70 bg-yellow-100 text-yellow-900 uppercase tracking-wide shadow-sm font-extrabold
        whitespace-nowrap min-w-max ${compact ? "px-4 py-1 text-xs" : "px-5 py-1 text-sm shadow-md"} ${className ?? ""}`}
    >
      Nouvelle carte!
    </p>
  );
}
