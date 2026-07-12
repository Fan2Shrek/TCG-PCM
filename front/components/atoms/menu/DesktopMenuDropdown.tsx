"use client";

import type { ReactNode } from "react";
import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { HiDotsHorizontal } from "react-icons/hi";

export type DesktopMenuDropdownItem = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
};

type DesktopMenuDropdownProps = {
  items: DesktopMenuDropdownItem[];
  isItemActive: (linkTo?: string) => boolean;
};

export default function DesktopMenuDropdown({ items, isItemActive }: DesktopMenuDropdownProps) {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLLIElement>(null);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen]);

  const hasActiveItem = items.some((item) => isItemActive(item.linkTo));

  return (
    <li ref={containerRef} className="relative flex items-center text-white">
      <button
        type="button"
        onClick={() => setIsOpen((prev) => !prev)}
        aria-expanded={isOpen}
        aria-label="Plus d'options"
        className={`flex items-center justify-center text-3xl cursor-pointer ${hasActiveItem ? "text-yellow-300" : ""}`}
      >
        <HiDotsHorizontal />
      </button>

      {isOpen && (
        <ul className="absolute top-full right-0 mt-3 min-w-48 overflow-hidden rounded-2xl border-2 border-white bg-primary py-1 shadow-lg">
          {items.map((item) => {
            const active = isItemActive(item.linkTo);
            const content = (
              <>
                <span className="text-xl">{item.icon}</span>
                <span className="font-bold whitespace-nowrap">{item.label}</span>
              </>
            );

            return (
              <li key={item.label}>
                {item.onClick ? (
                  <button
                    onClick={() => {
                      setIsOpen(false);
                      item.onClick?.();
                    }}
                    className={`flex w-full items-center gap-2 px-4 py-2 text-left text-white cursor-pointer hover:bg-white/10 ${active ? "underline" : ""}`}
                  >
                    {content}
                  </button>
                ) : (
                  <Link
                    href={item.linkTo!}
                    onClick={() => setIsOpen(false)}
                    className={`flex items-center gap-2 px-4 py-2 text-white hover:bg-white/10 ${active ? "underline" : ""}`}
                  >
                    {content}
                  </Link>
                )}
              </li>
            );
          })}
        </ul>
      )}
    </li>
  );
}
