"use client";

import CardRow from "./CardRow";

type EnemyPlayZoneProps = {
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
};

export default function EnemyPlayZone({ passiveCardIds, monsterCardIds, className = "" }: EnemyPlayZoneProps) {
  return (
    <div className={`transition-all duration-200 rounded-xl flex flex-col items-center justify-between p-2 min-h-72 ${className}`}>
      <CardRow cardIds={passiveCardIds} />
      <CardRow cardIds={monsterCardIds} />
    </div>
  );
}
