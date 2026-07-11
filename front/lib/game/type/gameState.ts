export type GameState = {
  player1: PlayerState;
  player2: PlayerState;
  currentPlayerId: string;
  cards: Record<string, CardState>;
};

export type PlayerState = {
  player: Player;
  healthPoints: number;
  maxHealthPoints: number;
  characterCardId: string;
  hand: string[];
  drawPile: string[];
  coins: number;
  playArea: PlayArea;
  discardPile: Record<string, string>;
};

export type Player = {
  id: string;
  name: string;
};

type PlayArea = {
  passiveCards: string[];
  monsterCards: string[];
};

export type CardState = {
  instanceId: string;
  effects: EffectState[];
  values: unknown;
};

type EffectState = {
  effect: string;
  data: unknown;
};
