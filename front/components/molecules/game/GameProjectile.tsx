import { useEffect, useState, useRef } from "react";
import Image from "@/components/atoms/Image";
import { CardSet } from "@/constants/card";

type GameProjectileProps = {
  startPosition: { x: number; y: number };
  endPosition: { x: number; y: number };
  duration: number;
  cardSet: CardSet;
  onAnimationComplete?: () => void;
};

export default function GameProjectile({
  startPosition,
  endPosition,
  duration,
  cardSet,
  onAnimationComplete,
}: GameProjectileProps) {
  const [position, setPosition] = useState(startPosition);
  const animationCompleted = useRef(false);

  useEffect(() => {
    requestAnimationFrame(() => {
      setPosition(endPosition);
    });
  }, [endPosition]);

  const handleTransitionEnd = () => {
    if (onAnimationComplete && !animationCompleted.current) {
      animationCompleted.current = true;
      onAnimationComplete();
    }
  };

  const getProjectileSrc = (set: CardSet): string => {
    switch (set) {
      case CardSet.TBOI:
        return "/game/projectile/isaac.webp";
      case CardSet.BTD6:
        return "/game/projectile/btd.webp";
      case CardSet.ORIGINAL:
      default:
        return "/game/projectile/original.webp";
    }
  };

  const projectileSrc = getProjectileSrc(cardSet);

  const dx = endPosition.x - startPosition.x;
  const dy = endPosition.y - startPosition.y;
  const angle = Math.atan2(dy, dx) * (180 / Math.PI);

  return (
    <div
      className="w-16 h-16 absolute z-90"
      style={{
        left: 0,
        top: 0,
        transform: `translate(${position.x}px, ${position.y}px) rotate(${angle}deg)`,
        transition: `transform ${duration}ms ease-out`,
        zIndex: 100,
      }}
      onTransitionEnd={handleTransitionEnd}
    >
      <Image
        src={projectileSrc}
        alt="Projectile"
        width={64}
        height={64}
      />
    </div>
  );
}
