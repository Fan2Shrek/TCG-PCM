import { CardSize } from "@/constants/card";

export const MAX_X_TILT = 30;
export const MAX_Y_TILT = 45;

export const FLIP_DEG = 180;
export const HALF_ROTATION = 90;
export const NORMALIZED_CENTER = 0.5;

export const DEFAULT_TILT = { x: 0, y: 0, z: 0 };
export const DEFAULT_GLARE = { x: 50, y: 50 };

export const NORMAL_ANIMATION_DURATION_MS = 300;
export const SNAPBACK_ANIMATION_DURATION_MS = 1000;
export const SNAPBACK_DELAY_MS = 150;

export const CARD_HAND_RADIUS_MULTIPLIER = 1.5;
export const CARD_HAND_MIN_ARC_ANGLE = 10;
export const CARD_HAND_ARC_ANGLE_SCALE = 10;
export const DEGREES_TO_RADIANS = Math.PI / 180;

export const CARD_WIDTH_PX = {
  sm: 128,
  md: 176,
  lg: 240,
  xl: 480,
};

export const CARD_ASPECT_RATIO = 5 / 7;

export const calculateTiltOnHover = (
  x: number,
  y: number,
  currentRy: number,
) => {
  const rotateX = (y - NORMALIZED_CENTER) * MAX_Y_TILT;
  const rotateY =
    (NORMALIZED_CENTER - x) * MAX_X_TILT +
    (currentRy > HALF_ROTATION ? FLIP_DEG : 0);
  return { x: rotateX, y: rotateY, z: 0 };
};

export const calculateGlareOnHover = (
  x: number,
  y: number,
  currentRy: number,
) => {
  const glareX = currentRy > HALF_ROTATION ? (1 - x) * 100 : x * 100;
  const glareY = y * 100;
  return { x: glareX, y: glareY };
};

export const getCardWidthPx = (size: CardSize): number => {
  return CARD_WIDTH_PX[size];
};

export const getCardAspectRatio = (): number => {
  return CARD_ASPECT_RATIO;
};

export const cardsHandComputeArcParameters = (
  totalCards: number,
  cardWidthPx: number,
  maxAngle: number,
  fanOut: boolean,
) => {
  const maxArcAngle = Math.min(
    maxAngle,
    CARD_HAND_MIN_ARC_ANGLE + totalCards * CARD_HAND_ARC_ANGLE_SCALE,
  );
  const arcAngleRadian = maxArcAngle * DEGREES_TO_RADIANS;

  const radius =
    cardWidthPx *
    (fanOut
      ? (CARD_HAND_RADIUS_MULTIPLIER * totalCards) / 1.95
      : CARD_HAND_RADIUS_MULTIPLIER);

  return { arcAngleRadian, radius };
};

export const cardsHandComputeCardPosition = (
  index: number,
  totalCards: number,
  arcAngleRadian: number,
  radius: number,
  hoveredCardIndex?: number,
) => {
  const middleIndex = (totalCards - 1) / 2;

  const effectiveMiddleIndex = hoveredCardIndex ?? middleIndex;
  const normalizedIndex = index - effectiveMiddleIndex;

  const angle =
    middleIndex === 0
      ? 0
      : (normalizedIndex / middleIndex) * (arcAngleRadian / 1.8);
  const x = radius * Math.sin(angle);
  const y = -radius * Math.cos(angle);

  const rotation = (angle * 180) / Math.PI;

  return { x, y, rotation };
};

export const clamp = (v: number) => Math.max(0, Math.min(1, v));
