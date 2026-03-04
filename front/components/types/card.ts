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

export type CardInHand = {
  card: CardModel;
  position: number;
  x: number;
  y: number;
  rotation: number;
}

export type CardSize = 'sm' | 'md' | 'lg' | 'xl';

// on stock les class tailwindcss ici pour pouvoir utiliser leur valeurs correspondantes
// pour calcul de pos et etc par rapport à taille de la carte sans avoir à faire du ref sur les éléments

export const CardSizeMap: Record<CardSize, string> = {
  sm: 'w-card-sm',
  md: 'w-card-md',
  lg: 'w-card-lg',
  xl: 'w-card-xl',
};


export const cardAspectRatio = 'aspect-card';

export enum foilEffects {
  HOLO = 'Holographic',
  RAINBOW = 'Rainbow',
  GOLDEN = 'Golden',
}
