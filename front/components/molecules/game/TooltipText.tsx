export enum TooltipTextPosition {
  BOTTOM_RIGHT = "bottom-right",
  BOTTOM_CENTER = "bottom-center",
  TOP_RIGHT = "top-right",
  TOP_CENTER = "top-center",
}

type TooltipTextProps = {
  text: string;
  isVisible: boolean;
  className?: string;
  position?: TooltipTextPosition;
  tooltipRef?: React.RefObject<HTMLDivElement | null>;
};

export default function TooltipText({
  text,
  isVisible,
  className,
  position = TooltipTextPosition.BOTTOM_RIGHT,
  tooltipRef,
}: TooltipTextProps) {
  const horizontalClass =
    position === TooltipTextPosition.BOTTOM_CENTER ||
    position === TooltipTextPosition.TOP_CENTER
      ? "left-1/2 -translate-x-1/2"
      : "right-0";

  const verticalClass =
    position === TooltipTextPosition.TOP_CENTER ||
    position === TooltipTextPosition.TOP_RIGHT
      ? "bottom-full mb-2"
      : "top-full mt-2";

  const visibilityClass = isVisible
    ? "opacity-100 pointer-events-auto"
    : "opacity-0 pointer-events-none";

  return (
    <div
      ref={tooltipRef}
      className={`absolute w-[min(90vw,28rem)] rounded-xl border border-white/25 bg-black/85 p-4 text-sm text-white shadow-lg transition-opacity duration-150 ${horizontalClass} ${verticalClass} ${visibilityClass} ${className ?? ""}`}
    >
      {text}
    </div>
  );
}
