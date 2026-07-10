"use client";

import { useCallback, useMemo } from "react";
import Booster from "./Booster";
import { BoosterType } from "@/constants/booster";

type InteractiveBoosterProps = {
  boosterType: BoosterType;
  className?: string;
  onClick?: (boosterType: BoosterType) => void;
  showGlare?: boolean;
  dimmed?: boolean;
};

export default function InteractiveBooster({
  boosterType,
  className,
  onClick,
  showGlare = false,
  dimmed = false,
}: InteractiveBoosterProps) {
  const handleClick = useCallback(() => {
    onClick?.(boosterType);
  }, [boosterType, onClick]);

  const animationStyle = useMemo(
    () =>
      ({
        "--duration": `${4.5 + Math.random() * 1.5}s`,
        "--delay": `${-Math.random() * 5}s`,
        "--rotate": `${1 + Math.random() * 1.5}deg`,
        "--rotateX": `${1 + Math.random() * 1.5}deg`,
        "--translate": `${1 + Math.random() * 1.1}px`,
      }) as React.CSSProperties,
    [],
  );

  return (
    <div
      className="cursor-pointer animate-booster-float"
      onClick={handleClick}
      style={{
        filter: "drop-shadow(1px 86px 12px rgba(0,0,0,0.55))",
        ...animationStyle,
      }}
    >
      <Booster
        boosterType={boosterType}
        className={className}
        showGlare={showGlare}
        dimmed={dimmed}
      />
    </div>
  );
}
