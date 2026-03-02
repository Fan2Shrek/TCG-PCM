<?php

use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
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
            300,
            [
                'card1',
                'card2',
            ],
            [],
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            100,
            [
                'card3',
            ],
            [],
        ),
        0,
        null,
        [
            'card1' => new CardState(
                'card1',
                'Spicy-D6',
                [],
            ),
            'card2' => new CardState(
                'card2',
                'Spicy-D6',
                [],
            ),
            'card3' => new CardState(
                'card3',
                'Spicy-D6',
                [],
            ),
        ],
    ),
    'events' => [
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'card1',
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
            'cardId' => 'card3',
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
            'cardId' => 'card2',
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
            290,
            [],
            [],
            [
                'card1',
                'card2',
            ]
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            0,
            [],
            [],
            [
                'card3',
            ]
        ),
        0,
    ),
];
