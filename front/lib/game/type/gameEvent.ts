import { GameEventTypeEnum } from "./eventType";

export type GameEvent = {
  type: GameEventTypeEnum;
  data: any;
  view: any;
}
