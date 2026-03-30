import BoosterSlot from "@/components/atoms/dailyBoosters/BoosterSlot";
import EmptySlot from "@/components/atoms/dailyBoosters/EmptySlot";

type PendingBoostersListProps = {
  pendingBoosters: number,
  className?: string,
};

export default ({ pendingBoosters, className }: PendingBoostersListProps) => {

    const maxBoosters: number = 5;
    const emptySlots = maxBoosters - pendingBoosters;

  return (
    <div className={`w-auto bg-primary border border-white rounded-md drop-shadow-lg flex flex-row items-center gap-2 px-2 py-1 ${className || ''}`}>
      {Array.from({ length: pendingBoosters }).map((_, i) => (
        <BoosterSlot key={i} />
      ))}
      {Array.from({ length: emptySlots }).map((_, i) => (
        <EmptySlot key={i} />
      ))}
    </div>
  );
}
