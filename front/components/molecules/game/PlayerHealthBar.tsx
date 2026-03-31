import ProgressBar from "@/components/atoms/dailyBoosters/ProgressBar";
import { useMemo } from "react";

type Props = {
  health: number;
  maxHealth: number;
}

export default ({ health, maxHealth }: Props) => {
  const progress = useMemo(() => health/maxHealth * 100, [health, maxHealth]);

  return (
	  <div>
		<ProgressBar progress={progress} text={`${health} / ${maxHealth}`} />
	  </div>
	);
}
