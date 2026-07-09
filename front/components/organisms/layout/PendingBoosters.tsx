import TimeRemaining from "@/components/atoms/dailyBoosters/TimeRemaining";
import ProgressBar from "@/components/atoms/dailyBoosters/ProgressBar";
import PendingBoostersList from "@/components/molecules/dailyBoosters/PendingBoostersList";
import { useBoosterTokensContext } from "@/contexts/BoosterTokensContext";

type PendingBoostersProps = {
  className?: string;
};

export default ({ className }: PendingBoostersProps) => {
  const { tokens, minutesTilNextToken, progressToNextToken } =
    useBoosterTokensContext();

  return (
    <div className={`w-92 flex flex-col gap-2 ${className || ""}`}>
      <ProgressBar progress={progressToNextToken} className="w-48" />
      <div className="flex flex-column items-center gap-5 w-full">
        <PendingBoostersList pendingBoosters={tokens} className="w-48" />
        <TimeRemaining timeRemaining={minutesTilNextToken} />
      </div>
    </div>
  );
};
