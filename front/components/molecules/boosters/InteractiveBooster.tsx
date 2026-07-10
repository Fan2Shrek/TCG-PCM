"use client";

import { useCallback, useMemo, useState } from "react";
import Booster from "./Booster";
import { BoosterType } from "@/constants/booster";
import { BoosterOpeningPhase } from "@/lib/boosterOpening/phases";

export enum BoosterMotionType {
  FLOAT = "float",
  SHAKE = "shake",
  NONE = "none",
}

type InteractiveBoosterProps = {
  boosterType: BoosterType;
  className?: string;
  onClick?: (boosterType: BoosterType) => void;
  showGlare?: boolean;
  brightness?: number;
  openingPhase?: BoosterOpeningPhase;
  shotCardCount?: number;
  motionType?: BoosterMotionType;
  disableShadow?: boolean;
  isCursorAvailable?: boolean;
};

export default function InteractiveBooster({
  boosterType,
  className,
  onClick,
  showGlare = false,
  brightness = 100,
  openingPhase,
  shotCardCount = 0,
  motionType = BoosterMotionType.FLOAT,
  disableShadow = false,
  isCursorAvailable = true,
}: InteractiveBoosterProps) {
  const BASE_SHAKE_DURATION = 0.4;
  const HOVER_SHAKE_DURATION = 0.15;
  const [isHovering, setIsHovering] = useState(false);

  const handleClick = useCallback(() => {
    onClick?.(boosterType);
  }, [boosterType, onClick]);

  const handlePointerEnter = useCallback(() => {
    if (!isCursorAvailable || motionType !== BoosterMotionType.SHAKE) {
      return;
    }

    setIsHovering(true);
  }, [isCursorAvailable, motionType]);

  const handlePointerLeave = useCallback(() => {
    setIsHovering(false);
  }, []);

  const animationStyle = useMemo(
    () =>
      ({
        "--duration": `${4.5 + Math.random() * 1.5}s`,
        "--delay": `${-Math.random() * 5}s`,
        "--rotate": `${1 + Math.random() * 1.5}deg`,
        "--rotateX": `${1 + Math.random() * 1.5}deg`,
        "--translate": `${1 + Math.random() * 1.1}px`,
        "--shake-duration": `${
          isCursorAvailable &&
          motionType === BoosterMotionType.SHAKE &&
          isHovering
            ? HOVER_SHAKE_DURATION
            : BASE_SHAKE_DURATION
        }s`,
      }) as React.CSSProperties,
    [isCursorAvailable, motionType, isHovering],
  );

  const motionClass =
    motionType === BoosterMotionType.SHAKE
      ? "animate-booster-shake"
      : motionType === BoosterMotionType.FLOAT
        ? "animate-booster-float"
        : "";

  return (
    <div
      className={`cursor-pointer transition-[filter] duration-200 ease-in-out ${motionClass}`}
      onClick={handleClick}
      onPointerEnter={handlePointerEnter}
      onPointerLeave={handlePointerLeave}
      style={{
        filter: `drop-shadow(1px 56px 12px rgba(0,0,0,${
          disableShadow ? 0 : 0.55
        }))`,
        ...animationStyle,
      }}
    >
      <Booster
        boosterType={boosterType}
        className={className}
        showGlare={showGlare}
        brightness={brightness}
        openingPhase={openingPhase}
        shotCardCount={shotCardCount}
      />
    </div>
  );
}
