<?php

use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;

return [
    'gameState' => new GameState(
        new PlayerState(
            new Player(
                '1',
                'replay1_player1',
            ),
            300,
            300,
            'player1_character',
            [
                'player1_card1',
            ],
            [
                'player1_card2' => 'Spicy-D6',
            ],
            new PlayArea(),
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            100,
            100,
            'player2_character',
            [],
            [
                'player2_card1' => 'Spicy-D6',
            ],
            new PlayArea(),
        ),
        0,
        null,
        [
            'player1_character' => new CardState(
                'player1_character',
                'dummy_character',
                '1',
                [],
            ),
            'player2_character' => new CardState(
                'player2_character',
                'dummy_character',
                '2',
                [],
            ),
            'player1_card1' => new CardState(
                'player1_card1',
                'Spicy-D6',
                '1',
                [],
            ),
        ],
    ),
    'events' => [
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'player1_card1',
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
            'cardId' => 'player2_card1',
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
            'cardId' => 'player1_card2',
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
            300,
            'player1_character',
            [],
            [],
            new PlayArea(),
            [
                'player1_card1',
                'player1_card2',
            ],
        ),
        new PlayerState(
            new Player(
                '2',
                'replay1_player2',
            ),
            0,
            100,
            'player2_character',
            [],
            [],
            new PlayArea(),
            [
                'player2_card1',
            ]
        ),
        0,
        null,
        [
            'player1_character' => new CardState(
                'player1_character',
                'dummy_character',
                '1',
                [],
            ),
            'player2_character' => new CardState(
                'player2_character',
                'dummy_character',
                '2',
                [],
            ),
        ]
    ),
];
