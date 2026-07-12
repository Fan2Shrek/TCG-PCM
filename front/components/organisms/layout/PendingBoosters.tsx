"use client";

import ProgressBar from "@/components/atoms/dailyBoosters/ProgressBar";
import PendingBoostersList from "@/components/molecules/dailyBoosters/PendingBoostersList";
import TooltipText, {
  TooltipTextPosition,
} from "@/components/molecules/game/TooltipText";
import { useBoosterTokensContext } from "@/contexts/BoosterTokensContext";
import { useMemo, useRef, useState } from "react";

type PendingBoostersProps = {
  className?: string;
};

export default function PendingBoosters({ className }: PendingBoostersProps) {
  const { tokens, maxTokens, minutesTilNextToken, progressToNextToken } =
    useBoosterTokensContext();
  const [isTooltipVisible, setIsTooltipVisible] = useState(false);
  const [isTooltipPinned, setIsTooltipPinned] = useState(false);
  const hoverTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const pinnedTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const clearHoverTimer = () => {
    if (hoverTimerRef.current) {
      clearTimeout(hoverTimerRef.current);
      hoverTimerRef.current = null;
    }
  };

  const clearPinnedTimer = () => {
    if (pinnedTimerRef.current) {
      clearTimeout(pinnedTimerRef.current);
      pinnedTimerRef.current = null;
    }
  };

  const handleMouseEnter = () => {
    if (isTooltipPinned) {
      return;
    }

    clearHoverTimer();
    hoverTimerRef.current = setTimeout(() => {
      setIsTooltipVisible(true);
    }, 650);
  };

  const handleMouseLeave = () => {
    clearHoverTimer();

    if (!isTooltipPinned) {
      setIsTooltipVisible(false);
    }
  };

  const handleClick = () => {
    clearHoverTimer();
    clearPinnedTimer();
    setIsTooltipPinned((previous) => {
      const nextPinned = !previous;
      setIsTooltipVisible(nextPinned);

      if (nextPinned) {
        pinnedTimerRef.current = setTimeout(() => {
          setIsTooltipPinned(false);
          setIsTooltipVisible(false);
          pinnedTimerRef.current = null;
        }, 5000);
      }

      return nextPinned;
    });
  };

  const hasMaxTokens = tokens >= maxTokens;

  const tooltipText = useMemo(() => {
    if (hasMaxTokens) {
      return "Nombre max de boosters atteint!";
    }

    if (minutesTilNextToken <= 60) {
      const minutes = Math.max(1, Math.ceil(minutesTilNextToken));
      return `Prochain booster dans ${minutes} minute${minutes > 1 ? "s" : ""}`;
    }

    const hours = Math.max(1, Math.ceil(minutesTilNextToken / 60));
    return `Prochain booster dans ${hours} heure${hours > 1 ? "s" : ""}`;
  }, [hasMaxTokens, minutesTilNextToken]);

  return (
    <div
      className={`w-92 flex flex-col gap-2 relative ${className || ""}`}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onClick={handleClick}
    >
      <ProgressBar
        progress={progressToNextToken}
        hasMaxTokens={hasMaxTokens}
        className="w-48"
      />
      <div className="flex flex-row justify-center items-center gap-5 w-full cursor-help">
        <PendingBoostersList pendingBoosters={tokens} className="w-48" />
      </div>
      <TooltipText
        text={tooltipText}
        isVisible={isTooltipVisible}
        position={TooltipTextPosition.BOTTOM_CENTER}
        className="pointer-events-none z-20 w-auto whitespace-nowrap rounded-md border-white/70 px-3 py-2 font-semibold"
      />
    </div>
  );
};
