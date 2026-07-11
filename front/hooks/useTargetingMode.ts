"use client";

import { useEffect, useState } from "react";
import { emitter } from "@/lib/eventBus";

export default function useTargetingMode() {
  const [isTargeting, setIsTargeting] = useState(false);

  useEffect(() => {
    const handleTargetingChange = (value: boolean) => {
      setIsTargeting(value);
    };

    emitter.on("game:targeting-changed", handleTargetingChange);

    return () => {
      emitter.off("game:targeting-changed", handleTargetingChange);
    };
  }, []);

  return isTargeting;
}
