import { GameEventTypeEnum } from "./eventType";
import { GameState } from "./gameState";

export type GameEvent = {
  type: GameEventTypeEnum;
  data: any;
  partialState: Partial<GameState>;
}
