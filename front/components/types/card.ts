export type CardLayer = {
  src: string;
  depth: number;
  alt?: string | null;
  holographicEffect?: HolographicEffect | null;
  foil?: string | null;
  mask?: string | null;
};

export type CardModel = {
  id: string;
  backImage?: string;
  frontLayers?: CardLayer[] | null;
};

export type CardSize = "xs" | "sm" | "md" | "lg" | "xl";

export const CardSizeClass: Record<CardSize, string> = {
  xs: "w-24",
  sm: "w-32",
  md: "w-44 ",
  lg: "w-60",
  xl: "w-[25vw] max-w-[480px]",
};

export enum HolographicEffect {
  RAINBOW = "rainbow",
}