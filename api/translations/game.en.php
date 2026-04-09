<?php

use App\Enum\CardEffectEnum;
use App\Enum\CardRarityEnum;

return [
    // Cards
    'card' => [
        'Benjamin' => [
            'name' => 'Benjamin',
            'description' => 'Apply {{effect}} to {{value}} card',
        ],
        'Pierrot' => [
            'name' => 'Pierrot',
            'description' => 'Apply {{effect}} {{value1}} card every {{value2}} turns.',
        ],
        'D6' => [
            'name' => 'D6',
            'description' => 'Roll a six-sided dice and does that many damage',
        ],
        'Stonks' => [
            'name' => 'Stonks',
            'description' => 'Gains {{value}} coins at the start of each turn. Also, at the end of each turn, gain {{value2}}% of your current coins as interest (up to {{const}} coins).',
        ],
        'Gitman' => [
            'name' => 'Gitman',
            'description' => 'Does {{value}} time per commits in this projects.',
        ],
        'Spicy-D6' => [
            'name' => 'Spicy D6',
            'description' => 'Roll a six-sided dice and does {{value}} time that many damage',
        ],
        'Redbloons' => [
            'name' => 'Red Bloons',
            'description' => 'A cute little balloon',
        ],
        'HackedZone' => [
            'name' => 'Hacked Zone',
            'description' => 'Apply {{effect}} to all cards in both side',
        ],
        'PierreSaidNoMonsterZone' => [
            'name' => 'Pierre Said "No Monster Zone"',
            'description' => 'Discard all active monsters',
        ],
        'Placenta' => [
            'name' => 'Placenta',
            'description' => 'Heal {{value}} HP at the start of each turn.',
        ],
        'ImStillStanding' => [
            'name' => "I'm Still Standing",
            'description' => 'When the player dies, he comes back to life with {{value}}% of his max HP.',
        ],
        'Justice' => [
            'name' => 'Justice',
            'description' => 'Make current player draw has many cards equal to the number of cards in other player hand.',
        ],
        'ConsolationPrice' => [
            'name' => 'Consolation Price',
            'description' => 'Each monster death grans {{value}} gold to the player.',
        ],
        'ViciousBee' => [
            'name' => 'Vicious Bee',
            'description' => 'When kill, give a <card>ViciousStinger</card>',
        ],
        'ViciousStinger' => [
            'name' => 'Vicious Stinger',
            'description' => 'Give <effect>DMGBoost</effect> to one monster',
        ],
    ],
    // Effects
    'effects' => [
        CardEffectEnum::HACKED->value => [
            'name' => 'Hacked',
        ],
        CardEffectEnum::TORNED->value => [
            'name' => 'Torned',
        ],
    ],
    // Rarities
    'rarity' => array_reduce(CardRarityEnum::cases(), function ($carry, $item) {
        $carry[$item->value] = [
            'name' => $item->name,
        ];

        return $carry;
    }, []),
];
