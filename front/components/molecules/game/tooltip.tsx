"use client";

import { useEffect, useRef, useState } from "react";
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
  const containerRef = useRef<HTMLDivElement>(null);

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

  const positionClass =
    position === TooltipPosition.BOTTOM_CENTER
      ? "left-1/2 -translate-x-1/2"
      : "right-0";

  const visibilityClass = isOpen
    ? "opacity-100 pointer-events-auto"
    : "opacity-0 pointer-events-none group-hover:opacity-100";

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

      <div
        className={`absolute top-full mt-2 w-[min(90vw,28rem)] rounded-xl border border-white/25 bg-black/85 p-4 text-sm text-white shadow-lg transition-opacity duration-150 ${positionClass} ${visibilityClass}`}
      >
        {text}
      </div>
    </div>
  );
}
