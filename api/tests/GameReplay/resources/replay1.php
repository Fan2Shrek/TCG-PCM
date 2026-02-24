<?php

use App\Enum\GameEventTypeEnum;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

return [
    'gameState' => new GameState(
        new PlayerState(
            new Player(
                '1',
                'replay1_player1',
            ),
            30,
            [
                'Spicy-D6',
                'Spicy-D6',
            ],
            [],
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            10,
            [
                'Spicy-D6',
            ],
            [],
        ),
        0,
    ),
    'events' => [
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'Spicy-D6',
            'playerId' => '1',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'sides' => 6,
            'result' => 6,
        ]),
        GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => '1',
        ]),
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'Spicy-D6',
            'playerId' => '2',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'sides' => 6,
            'result' => 1,
        ]),
        GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => '2',
        ]),
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'Spicy-D6',
            'playerId' => '1',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'slide' => 6,
            'result' => 4,
        ]),
    ],
    'finalGameState' => new GameState(
        new PlayerState(
            new Player(
                '1',
                'replay1_player1',
            ),
            29,
            [],
            [],
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            0,
            [],
            [],
        ),
        0,
    ),
];
