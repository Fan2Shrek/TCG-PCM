import { CardEffect, CardRaririty, FoilEffects } from "@/constants/card";

export type BasicCard = {
  name: string;
  description: string;
  image: string;
  rarity: CardRaririty;
  set: string;
  instanceId: string;
  effects: CardEffect[];
  isActive: boolean;
};

export type CardLayer = {
  src: string;
  depth: number;
  alt?: string | null;
  foilEffect?: FoilEffect | null;
  foil?: string | null;
  mask?: string | null;
};

export type CardWithPosition = {
  card: BasicCard;
  rank: number;
  x: number;
  y: number;
  rotation: number;
};

export type FoilEffect = (typeof FoilEffects)[keyof typeof FoilEffects];
