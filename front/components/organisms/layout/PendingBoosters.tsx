import TimeRemaining from "@/components/atoms/dailyBoosters/TimeRemaining";
import ProgressBar from "@/components/atoms/dailyBoosters/ProgressBar";
import PendingBoostersList from "@/components/molecules/dailyBoosters/PendingBoostersList";

type PendingBoostersProps = {
  className?: string,
};

export default ({ className }: PendingBoostersProps) => {

    const pendingBoosters: number = 3;
    const nextBoosterIn: number = 5;

  return (
      <div className={`w-92 flex flex-col gap-2 ${className || ''}`}>
        <ProgressBar progress={(pendingBoosters / nextBoosterIn) * 100} className="w-48" />
        <div className="flex flex-column items-center gap-5 w-full">
            <PendingBoostersList pendingBoosters={pendingBoosters} className="w-48" />
            <TimeRemaining timeRemaining={nextBoosterIn} />
        </div>
    </div>
  );
}
