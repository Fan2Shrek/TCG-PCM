"use client";

import { useLayoutEffect, useRef, useState } from "react";

/**
 * Measures how many of `itemCount` nav items actually fit and collapses the
 * rest into an overflow menu.
 *
 * Nav sits in a flex row next to another widget (e.g. the daily-booster
 * indicator) whose width is unrelated to the nav's own content. Sizing the
 * items list with `flex-1 min-w-0` inside a nav that itself auto-sizes to its
 * content creates a circular layout (the nav's size depends on the list,
 * which depends on the nav's size), which browsers resolve by collapsing the
 * list to a small, seemingly arbitrary width instead of the space actually
 * free in the row. To avoid that, the available width is computed directly
 * from independent, non-circular measurements: the row's width minus the
 * previous sibling's width (and gap/padding), minus the nav's own "chrome"
 * (everything besides the items list, e.g. the overflow button and the
 * profile icon).
 */
export function useNavOverflow(itemCount: number, dep?: string) {
  const rootRef = useRef<HTMLDivElement>(null);
  const navRef = useRef<HTMLElement>(null);
  const itemsRef = useRef<HTMLUListElement>(null);
  const measureRef = useRef<HTMLUListElement>(null);
  const [visibleCount, setVisibleCount] = useState(itemCount);

  useLayoutEffect(() => {
    const root = rootRef.current;
    const nav = navRef.current;
    const items = itemsRef.current;
    const measure = measureRef.current;
    const row = root?.parentElement;
    if (!root || !nav || !items || !measure || !row) {
      return;
    }

    const compute = () => {
      const rowStyles = getComputedStyle(row);
      const paddingLeft = parseFloat(rowStyles.paddingLeft) || 0;
      const paddingRight = parseFloat(rowStyles.paddingRight) || 0;
      const gapPx =
        parseFloat(rowStyles.columnGap || rowStyles.gap || "0") || 0;

      const prevSibling = root.previousElementSibling as HTMLElement | null;
      const prevSiblingWidth = prevSibling
        ? prevSibling.getBoundingClientRect().width
        : 0;

      const availableForRoot =
        row.getBoundingClientRect().width -
        paddingLeft -
        paddingRight -
        prevSiblingWidth -
        gapPx;

      const chromeWidth =
        nav.getBoundingClientRect().width - items.getBoundingClientRect().width;
      const availableForItems = availableForRoot - chromeWidth;

      const children = Array.from(measure.children) as HTMLElement[];
      let total = 0;
      let count = 0;
      for (const child of children) {
        total += child.offsetWidth;
        if (total > availableForItems) {
          break;
        }
        count += 1;
      }
      setVisibleCount(count);
    };

    compute();

    const observer = new ResizeObserver(compute);
    observer.observe(row);
    return () => observer.disconnect();
  }, [itemCount, dep]);

  return { rootRef, navRef, itemsRef, measureRef, visibleCount };
}
