import Image from "../Image";

type BoosterSlotProps = {
  className?: string;
  animationDurationMs?: number;
};

export default function BoosterSlot({
  className,
  animationDurationMs = 6000,
}: BoosterSlotProps) {
  const boosterPath: string = "/booster/pending_booster.webp";

  return (
    <Image
      src={boosterPath}
      alt="Booster"
      width={32}
      height={32}
      className={`animate-booster-title-float ${className || ""}`}
      style={{ animationDuration: `${animationDurationMs}ms` }}
    />
  );
};
