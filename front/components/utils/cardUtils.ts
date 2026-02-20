export const MAX_X_TILT = 30;
export const MAX_Y_TILT = 45;

export const FLIP_DEG = 180;
export const HALF_ROTATION = 90;
export const NORMALIZED_CENTER = 0.5;

export const DEFAULT_TILT = { x: 0, y: 0 };
export const DEFAULT_GLARE = { x: 50, y: 50 };

export const NORMAL_ANIMATION_DURATION_MS = 300;
export const SNAPBACK_ANIMATION_DURATION_MS = 1000;
export const SNAPBACK_DELAY_MS = 150;

export const calculateTilt = (x: number, y: number, currentRy: number) => {
  const rotateX = (y - NORMALIZED_CENTER) * MAX_Y_TILT;
  const rotateY = (NORMALIZED_CENTER - x) * MAX_X_TILT + (currentRy > HALF_ROTATION ? FLIP_DEG : 0);
  return { x: rotateX, y: rotateY };
};

export const calculateGlare = (x: number, y: number, currentRy: number) => {
  const glareX = currentRy > HALF_ROTATION ? (1 - x) * 100 : x * 100;
  const glareY = y * 100;
  return { x: glareX, y: glareY };
};

export const clamp = (v: number) => Math.max(0, Math.min(1, v));
