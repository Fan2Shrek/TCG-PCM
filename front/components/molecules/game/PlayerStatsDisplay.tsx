import Image from "@/components/atoms/Image";

type PlayerStatsDisplayProps = {
  money: number;
  health: number;
};

export default function PlayerStatsDisplay({ money, health }: PlayerStatsDisplayProps) {
  return (
    <div className="flex flex-col gap-2">
      <div className="flex flex-row items-center gap-2 flex-nowrap bg-amber-50 rounded-full p-2">
        <Image src="/icons/coins.svg" alt="Money" width={32} height={32} />
        <span className="text-amber-500 text-xl">{money}</span>
      </div>
      <div className="flex flex-row items-center gap-2 flex-nowrap bg-rose-50 rounded-full p-2">
        <Image src="/icons/heart.svg" alt="Health" width={32} height={32} />
        <span className="text-rose-500 text-xl">{health}</span>
      </div>
    </div>
  );
}
