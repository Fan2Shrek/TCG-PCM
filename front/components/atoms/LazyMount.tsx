"use client";

import { ReactNode } from "react";
import { useInViewport } from "@/hooks/useInViewport";

type LazyMountProps = {
  children: ReactNode;
  placeholderClassName?: string;
  rootMargin?: string;
};

const LazyMount = ({
  children,
  placeholderClassName,
  rootMargin,
}: LazyMountProps) => {
  const { ref, isVisible } = useInViewport<HTMLDivElement>(rootMargin);

  return (
    <div ref={ref} className={isVisible ? undefined : placeholderClassName}>
      {isVisible ? children : null}
    </div>
  );
};

export default LazyMount;
