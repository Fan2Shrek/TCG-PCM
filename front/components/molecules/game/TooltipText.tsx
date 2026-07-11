export enum TooltipTextPosition {
  BOTTOM_RIGHT = "bottom-right",
  BOTTOM_CENTER = "bottom-center",
}

type TooltipTextProps = {
  text: string;
  isVisible: boolean;
  className?: string;
  position?: TooltipTextPosition;
};

export default function TooltipText({
  text,
  isVisible,
  className,
  position = TooltipTextPosition.BOTTOM_RIGHT,
}: TooltipTextProps) {
  const positionClass =
    position === TooltipTextPosition.BOTTOM_CENTER
      ? "left-1/2 -translate-x-1/2"
      : "right-0";

  const visibilityClass = isVisible
    ? "opacity-100 pointer-events-auto"
    : "opacity-0 pointer-events-none";

  return (
    <div
      className={`absolute top-full mt-2 w-[min(90vw,28rem)] rounded-xl border border-white/25 bg-black/85 p-4 text-sm text-white shadow-lg transition-opacity duration-150 ${positionClass} ${visibilityClass} ${className ?? ""}`}
    >
      {text}
    </div>
  );
}
