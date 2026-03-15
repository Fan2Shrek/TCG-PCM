export type CardLayer = {
  src: string;
  depth: number;
  alt?: string | null;
  foilEffect?: foilEffects | null;
  foil?: string | null;
  mask?: string | null;
};

export type CardModel = {
  id: string;
  backImage?: string;
  frontLayers?: CardLayer[] | null;
};

export type CardWithPosition = {
  card: CardModel;
  rank: number;
  x: number;
  y: number;
  rotation: number;
}

export type CardSize = 'sm' | 'md' | 'lg' | 'xl';

export const CardSizeMap: Record<CardSize, string> = {
  sm: 'w-card-sm',
  md: 'w-card-md',
  lg: 'w-card-lg',
  xl: 'w-card-xl',
};

export enum foilEffects {
  HOLO = 'Holographic',
  RAINBOW = 'Rainbow',
  GOLDEN = 'Golden',
}
