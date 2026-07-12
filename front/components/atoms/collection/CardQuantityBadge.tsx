type CardQuantityBadgeProps = {
  quantity: number;
  className?: string;
};

export default function CardQuantityBadge({ quantity, className }: CardQuantityBadgeProps) {
  return (
    <p
      className={`rounded-full border border-yellow-300/70 bg-yellow-100 text-yellow-900 shadow-sm font-extrabold px-2 py-0.5 text-xs ${className ?? ""}`}
    >
      ×{quantity}
    </p>
  );
}
