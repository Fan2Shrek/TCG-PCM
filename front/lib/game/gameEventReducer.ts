import { GameEventType } from "@/lib/game/type/eventType";
import { GameEvent } from "@/lib/game/type/gameEvent";
import { GameState } from "@/lib/game/type/gameState";
import { emitter } from "@/lib/eventBus";

export type AnnouncementTone = "neutral" | "positive" | "negative";

export type AnnouncementPayload = {
  text: string;
  tone: AnnouncementTone;
  presentation?: "normal" | "giant";
};

export function getPlayerKey(
  state: GameState,
  playerId: string,
): "player1" | "player2" {
  return state.player1.player.id === playerId ? "player1" : "player2";
}

export function animateGameEvent(
  state: GameState,
  event: GameEvent,
): AnnouncementPayload | null {
  if (event.type === GameEventType.DICE_ROLLED) {
    if (!event.data.faces) return null;
    const rollValue = event.data.result;

    return {
      text: rollValue === null ? "🎲 Lancer de dés" : `🎲 ${rollValue}`,
      tone: "neutral",
      presentation: "giant",
    };
  }

  if (!event.view) return null;

  const view = event.view;

  switch (event.type) {
    case GameEventType.TURN_STARTED: {
      const player = getPlayerKey(state, String(view.currentPlayer));
      return {
        text: `Tour de ${state[player].player.name}`,
        tone: "neutral",
      };
    }

    case GameEventType.COINS_GAINED:
    case GameEventType.COINS_LOST: {
      const playerKey = getPlayerKey(state, view.playerId);
      const previousCoins = state[playerKey].coins;
      const nextCoins = view.total;

      if (nextCoins !== previousCoins) {
        const delta = nextCoins - previousCoins;
        return {
          text: `${state[playerKey].player.name} ${delta > 0 ? "+" : ""}${delta} pièces`,
          tone: delta > 0 ? "positive" : "negative",
        };
      }

      return null;
    }

    case GameEventType.HEAL:
    case GameEventType.DAMAGE: {
      if (typeof view.total !== "number") {
        return null;
      }

      const playerKey = getPlayerKey(state, view.playerId);
      const previousHealth = state[playerKey].healthPoints;
      const nextHealth = view.total;

      if (nextHealth !== previousHealth) {
        const delta = nextHealth - previousHealth;
        return {
          text: `${state[playerKey].player.name} ${delta > 0 ? "+" : ""}${delta} PV`,
          tone: delta > 0 ? "positive" : "negative",
        };
      }

      return null;
    }

    default:
      return null;
  }
}

export function applyGameView(
  state: GameState,
  event: GameEvent,
  currentUsername?: string,
): GameState {
  if (!event.view) return state;

  const next = { ...state };
  const view = event.view;

  switch (event.type) {
    case GameEventType.CARD_DRAWN: {
      const playerKey = getPlayerKey(state, view.playerId);
      const player = state[playerKey];
      // skip
      if (!view.card && player.player.name === currentUsername) {
        return next;
      }

      const newHand = [...player.hand, view.cardId];

      const newDrawPile = player.drawPile.filter((id) => id !== view.cardId);

      next[playerKey] = {
        ...player,
        hand: newHand,
        drawPile: newDrawPile,
      };

      if (view.card) {
        next.cards = {
          ...next.cards,
          [view.card.instanceId]: view.card,
        };
      }

      emitter.emit("game:card-drawn", {
        playerId: view.playerId,
        cardId: view.cardId,
      });

      return next;
    }

    case GameEventType.TURN_STARTED: {
      return {
        ...state,
        currentPlayerId: String(view.currentPlayer),
      };
    }

    case GameEventType.CARD_DISCARDED:
    case GameEventType.CARD_PLACE_IN_PLAY_AREA:
    case GameEventType.CARD_PLACE_IN_MONSTER_AREA: {
      const cardId = view.cardId;
      const card = state.cards[cardId];

      const playerKey = getPlayerKey(state, view.playerId);
      const player = state[playerKey];

      const nextPlayer = {
        ...player,
        hand: player.hand.filter((id) => id !== cardId),
      };

      if (event.type === GameEventType.CARD_DISCARDED) {
        if (nextPlayer.playArea.monsterCards.includes(cardId)) {
          nextPlayer.playArea.monsterCards =
            nextPlayer.playArea.monsterCards.filter((id) => id !== cardId);
        } else if (nextPlayer.playArea.passiveCards.includes(cardId)) {
          nextPlayer.playArea.passiveCards =
            nextPlayer.playArea.passiveCards.filter((id) => id !== cardId);
        }

        return {
          ...state,
          [playerKey]: {
            ...nextPlayer,
            discardPile: {
              ...player.discardPile,
              [cardId]: card?.instanceId ?? cardId,
            },
          },
        };
      }

      const cardToEmit = view.card || card;
      if (cardToEmit) {
        emitter.emit("card:played", { card: cardToEmit });
      }

      return {
        ...state,
        [playerKey]: {
          ...nextPlayer,
          playArea: {
            passiveCards:
              event.type === GameEventType.CARD_PLACE_IN_PLAY_AREA
                ? [...player.playArea.passiveCards, cardId]
                : player.playArea.passiveCards,
            monsterCards:
              event.type === GameEventType.CARD_PLACE_IN_MONSTER_AREA
                ? [...player.playArea.monsterCards, cardId]
                : player.playArea.monsterCards,
          },
        },
        cards: {
          ...state.cards,
          ...(view.card ? { [cardId]: view.card } : {}),
        },
      };
    }

    case GameEventType.COINS_GAINED:
    case GameEventType.COINS_LOST: {
      const nextCoins = view.total;

      const playerKey = getPlayerKey(state, view.playerId);
      const player = state[playerKey];
      const previousCoins = player.coins;
      const delta = nextCoins - previousCoins;

      emitter.emit("game:coins-changed", {
        playerId: view.playerId,
        delta,
      });

      return {
        ...state,
        [playerKey]: {
          ...state[playerKey],
          coins: nextCoins,
        },
      };
    }

    case GameEventType.HEAL:
    case GameEventType.DAMAGE: {
      if (view.cardId && view.card) {
        return {
          ...state,
          cards: {
            ...state.cards,
            [view.cardId]: view.card,
          },
        };
      }

      if (typeof view.total !== "number") {
        return state;
      }

      const nextHealth = view.total;

      const playerKey = getPlayerKey(state, view.playerId);
      const player = state[playerKey];
      const previousHealth = player.healthPoints;
      const delta = nextHealth - previousHealth;

      emitter.emit("game:health-changed", {
        playerId: view.playerId,
        delta,
        type: event.type === GameEventType.DAMAGE ? "damage" : "heal",
      });

      return {
        ...state,
        [playerKey]: {
          ...state[playerKey],
          healthPoints: nextHealth,
        },
      };
    }

    case GameEventType.EFFECT_ADDED:
    case GameEventType.UPDATE_CARD_STATE: {
      const cardId = view.cardId;

      if (!cardId || !view.card) {
        return state;
      }

      return {
        ...state,
        cards: {
          ...state.cards,
          [cardId]: view.card,
        },
      };
    }

    case GameEventType.MONSTER_DIED: {
      const cardId = view.cardId;
      const playerKey = getPlayerKey(state, view.playerId);
      const player = state[playerKey];

      return {
        ...state,
        [playerKey]: {
          ...player,
          playArea: {
            ...player.playArea,
            monsterCards: player.playArea.monsterCards.filter(
              (id) => id !== cardId,
            ),
          },
          discardPile: {
            ...player.discardPile,
            [cardId]: cardId,
          },
        },
      };
    }

    default:
      return state;
  }
}
