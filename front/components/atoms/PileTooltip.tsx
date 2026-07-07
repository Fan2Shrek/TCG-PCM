type PileTooltipProps = {
  isVisible: boolean;
  count: number;
  label: string;
  isMirrored?: boolean;
};

export default function PileTooltip({
  isVisible,
  count,
  label,
  isMirrored = false,
}: PileTooltipProps) {
  if (count <= 0) return;

  return (
    <div
      className={`absolute bg-gray-900 text-white text-sm px-3 py-1 rounded whitespace-nowrap pointer-events-none transition-all duration-300
        z-10 top-1/2 -translate-y-1/2 ${isVisible ? "opacity-100" : "opacity-0 pointer-events-none"}
    ${isMirrored ? `left-full ml-2 ${isVisible ? "translate-x-0" : "-translate-x-2"}` : `right-full mr-2 ${isVisible ? "translate-x-0" : "translate-x-2"}`}`}
    >
      {count} {label}
    </div>
  );
}
