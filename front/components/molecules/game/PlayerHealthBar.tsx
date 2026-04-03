import HeartIcon from "@/components/atoms/icons/heartIcon";
import HeartMask from "@/components/atoms/icons/heartMask";

type Props = {
  health: number;
  maxHealth: number;
  size?: number;
};

export default function PlayerHealthBar({
  health,
  maxHealth,
  size = 96,
}: Props) {
  const healthRatio = Math.max(0, Math.min(1, health / maxHealth));

  const containerColor = "#FFFFFF99";
  const containerSize = size + 24;
  const fillColor = "#b60707";

  const maskId = `heart-mask-${Math.random().toString(36).slice(0, 9)}`;

  return (
    <div className="relative inline-block w-fit h-fit text-center">
      <HeartIcon color={containerColor} size={containerSize} />

      <div
        className="absolute left-1/2 top-1/2"
        style={{
          width: `${size}px`,
          height: `${size}px`,
          transform: "translate(-50%, -50%)",
        }}
      >
        <HeartMask id={maskId} size={size} />

        <div
          className="absolute bottom-0 left-0 w-full overflow-hidden"
          style={{ height: size, clipPath: `url(#${maskId})` }}
        >
          <div
            className="absolute left-0 w-full bg-red-500"
            style={{
              bottom: 0,
              height: `${healthRatio * 100}%`,
            }}
          />
        </div>
      </div>

      <div className="absolute inset-0 top-1/3">
        <div className="flex flex-row flex-nowrap items-end justify-center text-xs font-bold text-black pointer-events-none">
          <span className="text-xl">{health}</span>
          <span className="text-[0.8em] mb-1">/ {maxHealth}</span>
        </div>
      </div>
    </div>
  );
}
