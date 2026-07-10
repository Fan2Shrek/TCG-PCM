import Image from "../Image";

type BoosterSlotProps = {
  className?: string;
  animationDurationMs?: number;
};

export default ({
  className,
  animationDurationMs = 6000,
}: BoosterSlotProps) => {
  const boosterPath: string = "/menu/booster_pending.jpg";

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
