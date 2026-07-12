"use client";

import { Booster } from "@/app/types/booster";
import { CSSProperties, useCallback, useMemo, useState } from "react";
import { useWindowWidth } from "./useWindowWidth";

type Style = CSSProperties;

export function useBoosterCarousel(
  boosters: Booster[],
  maxRadius: number = 320,
) {
  const [frontIndex, setFrontIndex] = useState(0);
  const screenWidth = useWindowWidth();

  const rotateTo = useCallback(
    (index: number) => {
      if (index === frontIndex) {
        return;
      }

      setFrontIndex(index);
    },
    [frontIndex],
  );

  const getBoosterStyle = useCallback(
    (index: number): Style => {
      const count = boosters.length;

      const relative = index - frontIndex;

      const angle = (relative / count) * Math.PI * 2;

      const radiusX = Math.min(maxRadius, screenWidth / 3);
      const radiusY = 30;

      const x = Math.sin(angle) * radiusX;
      const y = Math.cos(angle) * radiusY;

      // -1 (back) -> 1 (front)
      const depth = Math.cos(angle);

      const scale = 0.7 + ((depth + 1) / 2) * 0.5;

      return {
        transform: `translate(${x}px, ${y}px) scale(${scale})`,
        zIndex: Math.round((depth + 1) * 100),
      };
    },
    [boosters.length, frontIndex, maxRadius, screenWidth],
  );

  const frontBooster = useMemo(
    () => boosters[frontIndex],
    [boosters, frontIndex],
  );

  return {
    frontBooster,
    rotateTo,
    getBoosterStyle,
  };
}
