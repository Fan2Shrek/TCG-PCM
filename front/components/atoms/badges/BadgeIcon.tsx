import type { ReactNode } from "react";
import { TbLock, TbPackage, TbSword, TbTrophy } from "react-icons/tb";
import HoloFoil from "@/components/atoms/HoloFoil";
import GoldenFoil from "@/components/atoms/GoldenFoil";
import RainbowFoil from "@/components/atoms/RainbowFoil";
import type { BadgeName } from "@/app/types/badge";

const FOIL_SRC = "/card/card_foil.png";
const MASK_SRC = "/card/card_mask.png";
const STATIC_TILT = { x: 0, y: 0 };

const badgeIconByName: Record<BadgeName, ReactNode> = {
  opened_booster: <TbPackage />,
  game_played: <TbSword />,
  game_win: <TbTrophy />,
};

const foilComponentByLevel = {
  3: HoloFoil,
  4: GoldenFoil,
  5: RainbowFoil,
} as const;

export type BadgeIconProps = {
  badgeName: BadgeName;
  level: number;
  size?: number;
  className?: string;
};

const BadgeIcon = ({ badgeName, level, size = 48, className }: BadgeIconProps) => {
  const isLocked = level <= 0;
  const FoilComponent =
    foilComponentByLevel[level as keyof typeof foilComponentByLevel];

  return (
    <div
      className={`relative flex items-center justify-center rounded-full overflow-hidden border-2 ${
        isLocked
          ? "border-muted-foreground/30 bg-muted/40 text-muted-foreground/50 grayscale"
          : "border-primary/60 bg-primary/10 text-primary"
      } ${className ?? ""}`}
      style={{ width: size, height: size, fontSize: size * 0.45 }}
    >
      {isLocked ? <TbLock /> : badgeIconByName[badgeName]}
      {FoilComponent && (
        <FoilComponent tilt={STATIC_TILT} foil={FOIL_SRC} mask={MASK_SRC} />
      )}
    </div>
  );
};

export default BadgeIcon;
