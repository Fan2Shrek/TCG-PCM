export type CardLayer = {
  src: string;
  alt?: string;
  depth: number;
};

export type CardModel = {
  id: string;
  backImage?: string;
  frontLayers?: CardLayer[] | null;
  // overlayEffect removed: shimmer/shininess feature is disabled for now
};

export type CardSize = "xs" | "sm" | "md" | "lg" | "xl";

export const CardSizeClass: Record<CardSize, string> = {
  xs: "w-24",
  sm: "w-32",
  md: "w-44 ",
  lg: "w-60",
  xl: "w-[25vw] max-w-[480px]",
};

