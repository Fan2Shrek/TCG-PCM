import { CardSize } from "../types/card";

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

export const calculateTilt = (x: number, y: number, currentRy: number) => {
  const rotateX = (y - NORMALIZED_CENTER) * MAX_Y_TILT;
  const rotateY = (NORMALIZED_CENTER - x) * MAX_X_TILT + (currentRy > HALF_ROTATION ? FLIP_DEG : 0);
  return { x: rotateX, y: rotateY, z: 0 };
};

export const calculateGlare = (x: number, y: number, currentRy: number) => {
  const glareX = currentRy > HALF_ROTATION ? (1 - x) * 100 : x * 100;
  const glareY = y * 100;
  return { x: glareX, y: glareY };
};

export const getCardWidthRem = (size: CardSize): number => {
  if (typeof document === 'undefined') {
    return 0;
  }
  const root = document.documentElement;
  const cssVar = `--spacing-card-${size}`;
  const value = getComputedStyle(root).getPropertyValue(cssVar).trim();
  return value ? parseFloat(value) : 0;
};

export const remToPx = (rem: number): number => {
  if (typeof document === 'undefined') {
    return 0;
  }
  const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
  return rem * rootFontSize;
};

export const getCardAspectRatio = (): number => {
  if (typeof document === 'undefined') {
    return 5 / 7; // fallback to default
  }
  const root = document.documentElement;
  const value = getComputedStyle(root).getPropertyValue('--aspect-card').trim();
  if (!value) {
    return 5 / 7;
  }
  const [width, height] = value.split('/').map(v => parseFloat(v.trim()));
  return width / height;
};

export const cardsHandComputeArcParameters = (totalCards: number, cardWidthPx: number, maxAngle: number) => {
  const maxArcAngle = Math.min(maxAngle, 10 + totalCards * 10);
  const arcAngleRadian = (maxArcAngle * Math.PI) / 180;

  const radius = cardWidthPx * 1.5;

  return { arcAngleRadian, radius };
}

export const cardsHandComputeCardPosition = (index: number, totalCards: number, arcAngleRadian: number, radius: number) => {
  const middleIndex = (totalCards - 1) / 2;
  const normalizedIndex = index - middleIndex;

  const angle = middleIndex === 0 ? 0 : (normalizedIndex / middleIndex) * (arcAngleRadian / 2);
  const x = radius * Math.sin(angle);
  const y = -radius * Math.cos(angle);

  const rotation = (angle * 180) / Math.PI;

  return { x, y, rotation };
}

export const clamp = (v: number) => Math.max(0, Math.min(1, v));
