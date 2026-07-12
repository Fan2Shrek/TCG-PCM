import BoosterSlot from "@/components/atoms/dailyBoosters/BoosterSlot";
import EmptySlot from "@/components/atoms/dailyBoosters/EmptySlot";

type PendingBoostersListProps = {
  pendingBoosters: number;
  className?: string;
};

export default function PendingBoostersList({ pendingBoosters, className }: PendingBoostersListProps) {
  const maxBoosters: number = 5;
  const emptySlots = maxBoosters - pendingBoosters;

  const getSlotDurationMs = (index: number) => {
    const durations = [5600, 6100, 5850, 6350, 6000];
    return durations[index % durations.length];
  };

  return (
    <div
      className={`w-auto flex flex-row items-center gap-2 px-2 py-1 ${className || ""}`}
    >
      {Array.from({ length: pendingBoosters }).map((_, i) => (
        <BoosterSlot key={i} animationDurationMs={getSlotDurationMs(i)} />
      ))}
      {Array.from({ length: emptySlots }).map((_, i) => (
        <EmptySlot key={i} />
      ))}
    </div>
  );
};
