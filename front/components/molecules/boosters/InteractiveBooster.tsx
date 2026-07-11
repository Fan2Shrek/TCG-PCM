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

  const seededMotion = useMemo(() => {
    const seedSource = `${boosterType}:${className ?? ""}`;
    let hash = 0;

    for (let i = 0; i < seedSource.length; i += 1) {
      hash = (hash * 31 + seedSource.charCodeAt(i)) >>> 0;
    }

    const unit = (offset: number) => ((hash >> offset) & 1023) / 1023;

    return {
      duration: 4.5 + unit(0) * 1.5,
      delay: -unit(10) * 5,
      rotate: 1 + unit(20) * 1.5,
      rotateX: 1 + unit(4) * 1.5,
      translate: 1 + unit(14) * 1.1,
    };
  }, [boosterType, className]);

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
        "--duration": `${seededMotion.duration}s`,
        "--delay": `${seededMotion.delay}s`,
        "--rotate": `${seededMotion.rotate}deg`,
        "--rotateX": `${seededMotion.rotateX}deg`,
        "--translate": `${seededMotion.translate}px`,
        "--shake-duration": `${
          isCursorAvailable &&
          motionType === BoosterMotionType.SHAKE &&
          isHovering
            ? HOVER_SHAKE_DURATION
            : BASE_SHAKE_DURATION
        }s`,
      }) as React.CSSProperties,
    [seededMotion, isCursorAvailable, motionType, isHovering],
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
        brightness={brightness}
        openingPhase={openingPhase}
        shotCardCount={shotCardCount}
      />
    </div>
  );
}
