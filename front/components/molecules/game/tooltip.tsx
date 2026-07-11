"use client";

import TooltipText, {
  TooltipTextPosition,
} from "@/components/molecules/game/TooltipText";
import { useCallback, useEffect, useRef, useState } from "react";
import { FaRegCircleQuestion } from "react-icons/fa6";

export enum TooltipPosition {
  BOTTOM_RIGHT = "bottom-right",
  BOTTOM_CENTER = "bottom-center",
}

type TooltipProps = {
  text: string;
  className?: string;
  position?: TooltipPosition;
};

export default function Tooltip({
  text,
  className,
  position = TooltipPosition.BOTTOM_RIGHT,
}: TooltipProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [shouldFlip, setShouldFlip] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const tooltipRef = useRef<HTMLDivElement>(null);

  const updateFlipState = useCallback(() => {
    const container = containerRef.current;
    const tooltip = tooltipRef.current;

    if (!container || !tooltip) {
      return;
    }

    const triggerRect = container.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();

    const viewportHeight = window.innerHeight;
    const spaceBelow = viewportHeight - triggerRect.bottom;
    const spaceAbove = triggerRect.top;
    const requiredSpace = tooltipRect.height + 8;

    setShouldFlip(spaceBelow < requiredSpace && spaceAbove > spaceBelow);
  }, []);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent | TouchEvent) => {
      const target = event.target;
      if (!(target instanceof Node)) {
        return;
      }

      if (!containerRef.current?.contains(target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("touchstart", handleClickOutside);

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("touchstart", handleClickOutside);
    };
  }, []);

  useEffect(() => {
    updateFlipState();

    window.addEventListener("resize", updateFlipState);
    window.addEventListener("scroll", updateFlipState, true);

    return () => {
      window.removeEventListener("resize", updateFlipState);
      window.removeEventListener("scroll", updateFlipState, true);
    };
  }, [isOpen, text, updateFlipState]);

  const tooltipPosition = shouldFlip
    ? position === TooltipPosition.BOTTOM_CENTER
      ? TooltipTextPosition.TOP_CENTER
      : TooltipTextPosition.TOP_RIGHT
    : position === TooltipPosition.BOTTOM_CENTER
      ? TooltipTextPosition.BOTTOM_CENTER
      : TooltipTextPosition.BOTTOM_RIGHT;

  return (
    <div
      ref={containerRef}
      className={`group relative inline-flex ${className ?? ""}`}
      onClick={(event) => event.stopPropagation()}
    >
      <button
        type="button"
        aria-label="Aide"
        className="flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white shadow-md cursor-help transition-colors hover:bg-black/70"
        onClick={() => setIsOpen((open) => !open)}
      >
        <FaRegCircleQuestion className="h-6 w-6" />
      </button>

      <TooltipText
        text={text}
        isVisible={isOpen}
        position={tooltipPosition}
        tooltipRef={tooltipRef}
        className={!isOpen ? "group-hover:opacity-100" : ""}
      />
    </div>
  );
}
