<?php

use App\Enum\CardEffectEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\Effect\EffectState;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;

return [
    'initialGameState' => new GameState(
        new PlayerState(
            new Player(
                '1',
                'replay2_player1',
            ),
            300,
            300,
            'player1_characater',
            [
                'player1_card1',
                'player1_card2',
            ],
            [
                'player1_card3' => 'Spicy-D6',
            ],
            5,
            new PlayArea(),
        ),
        new PlayerState(
            new Player(
                '2',
                'replay2_player2',
            ),
            100,
            100,
            'player2_characater',
            [
                'player2_card1',
            ],
            [
                'player2_card2' => 'Spicy-D6',
            ],
            5,
            new PlayArea(),
        ),
        0,
        0,
        null,
        [
            'player1_card1' => new CardState(
                'player1_card1',
                'HackedZone',
                '1',
                [],
            ),
            'player1_card2' => new CardState(
                'player1_card2',
                'Spicy-D6',
                '1',
                [],
            ),
            'player2_card1' => new CardState(
                'player2_card1',
                'Spicy-D6',
                '2',
                [],
            ),
            'player1_characater' => new CardState(
                'player1_characater',
                'Pierrot',
                '1',
                [],
                [
                    'turnRemainingBeforeAction' => 1,
                ],
            ),
            'player2_characater' => new CardState(
                'player2_characater',
                'Pierrot',
                '2',
                [],
                [
                    'turnRemainingBeforeAction' => 2,
                ],
            ),
        ],
    ),
    'events' => [
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'player1_card1',
            'playerId' => '1',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 3
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 2
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 1
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 1.5
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 1.5
        ]),
        GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => '1',
        ]),
        // pb
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 1.5
        ]),
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'player2_card1',
            'playerId' => '2',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'sides' => 6,
            'result' => 6,
        ]),
        GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => '2',
        ]),
        GameEvent::game(GameEventTypeEnum::CARD_RUNTIME_VALUE, [
            'value' => 'player2_characater',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'min' => 0.3,
            'max' => 3,
            'result' => 1.5
        ]),
        GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'cardId' => 'player1_card2',
            'playerId' => '1',
        ]),
        GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'sides' => 6,
            'result' => 6,
        ]),
    ],
    'finalGameState' => new GameState(
        new PlayerState(
            new Player(
                '1',
                'replay2_player1',
            ),
            240,
            300,
            'player1_characater',
            [
                'player1_card3',
            ],
            [],
            3,
            new PlayArea([
                'player1_card1',
            ]),
            [
                'player1_card2',
            ],
        ),
        new PlayerState(
            new Player(
                '2',
                'replay2_player2',
            ),
            40,
            100,
            'player2_characater',
            [
                'player2_card2',
            ],
            [],
            7,
            new PlayArea(),
            [
                'player2_card1',
            ]
        ),
        0,
        0,
        null,
        [
            'player1_card1' => new CardState(
                'player1_card1',
                'HackedZone',
                '1',
                [
                    new EffectState(
                        CardEffectEnum::HACKED,
                        [
                            'value' => 3.0,
                        ]
                    ),
                ],
            ),
            'player1_characater' => new CardState(
                'player1_characater',
                'Pierrot',
                '1',
                [
                    new EffectState(
                        CardEffectEnum::HACKED,
                        [
                            'value' => 1.5,
                        ]
                    ),
                ],
                [
                    'turnRemainingBeforeAction' => 3,
                ],
            ),
            'player2_characater' => new CardState(
                'player2_characater',
                'Pierrot',
                '2',
                [
                    new EffectState(
                        CardEffectEnum::HACKED,
                        [
                            'value' => 1.5,
                        ]
                    ),
                    new EffectState(
                        CardEffectEnum::TORNED,
                    ),
                ],
                [
                    'turnRemainingBeforeAction' => 1,
                ],
            ),
            'player1_card3' => new CardState(
                'player1_card3',
                'Spicy-D6',
                '1',
                [
                    new EffectState(
                        CardEffectEnum::HACKED,
                        [
                            'value' => 1.5,
                        ]
                    ),
                ],
            ),
            'player2_card2' => new CardState(
                'player2_card2',
                'Spicy-D6',
                '2',
                [
                    new EffectState(
                        CardEffectEnum::HACKED,
                        [
                            'value' => 1.5,
                        ]
                    ),
                ],
            ),
        ],
    ),
];
