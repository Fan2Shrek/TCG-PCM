"use client";

import CardRow from "./CardRow";

type EnemyPlayZoneProps = {
  passiveCardIds: string[];
  monsterCardIds: string[];
  className?: string;
  selectedCardId?: string | null;
  onSelectCard?: (cardId: string | null) => void;
  hoveredTargetId?: string | null;
};

export default function EnemyPlayZone({ passiveCardIds, monsterCardIds, className = "", selectedCardId, onSelectCard, hoveredTargetId }: EnemyPlayZoneProps) {
  return (
    <div className={`w-full min-h-110 transition-all duration-200 rounded-xl flex flex-col items-center justify-between p-2 ${className}`}>
      <CardRow cardIds={passiveCardIds} isLoggedPlayerSide={false} selectedCardId={selectedCardId} onSelectCard={onSelectCard} hoveredTargetId={hoveredTargetId} />
      <CardRow cardIds={monsterCardIds} isLoggedPlayerSide={false} selectedCardId={selectedCardId} onSelectCard={onSelectCard} hoveredTargetId={hoveredTargetId} />
    </div>
  );
}
