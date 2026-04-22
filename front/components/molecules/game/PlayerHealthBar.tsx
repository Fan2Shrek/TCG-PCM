import ProgressBar from "@/components/atoms/dailyBoosters/ProgressBar";
import { useMemo } from "react";

type Props = {
  health: number;
  maxHealth: number;
}

export default ({ health, maxHealth }: Props) => {
  const progress = useMemo(() => {
    if (maxHealth <= 0) return 0;
    return Math.max(0, Math.min(100, (health / maxHealth) * 100));
  }, [health, maxHealth]);

  const color = useMemo(() => {
    const hue = Math.round((progress / 100) * 120);
    return `hsl(${hue}, 85%, 45%)`;
  }, [progress]);

  return (
    <div>
      <ProgressBar
        progress={progress}
        text={`${health} / ${maxHealth}`}
        color={color}
      />
    </div>
  );
}
