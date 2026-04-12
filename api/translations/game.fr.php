<?php

use App\Enum\CardEffectEnum;
use App\Enum\CardRarityEnum;

return [
    // Cards
    'card' => [
        'Benjamin' => [
            'name' => 'Benjamin',
            'description' => 'Applique {{effect}} à {{value}} carte',
        ],
        'Pierrot' => [
            'name' => 'Pierrot',
            'description' => 'Applique {{effect}} à {{value1}} carte tous les {{value2}} tours.',
        ],
        'Stonks' => [
            'name' => 'Stonks',
            'description' => 'Gagne {{value}} pièces au début de chaque tour. De plus, à la fin de chaque tour, gagne {{value2}}% de vos pièces actuelles en intérêts (jusqu\'à {{const}} pièces).',
        ],
        'D6' => [
            'name' => 'D6',
            'description' => 'Lancez un dé à six faces et infligez autant de dégâts',
        ],
        'Gitman' => [
            'name' => 'Gitman',
            'description' => 'Inflige {{value}} fois le nombre de commits dans ce projet.',
        ],
        'Spicy-D6' => [
            'name' => 'D6 Épicé',
            'description' => 'Lancez un dé à six faces et inflige {{value}} fois autant de dégâts',
        ],
        'Redbloons' => [
            'name' => 'Ballon Rouges',
            'description' => 'Potit ballon tout mignon',
        ],
        'HackedZone' => [
            'name' => 'Zone Piratée',
            'description' => 'Applique {{effect}} à toutes les cartes des deux côtés',
        ],
        'PierreSaidNoMonsterZone' => [
            'name' => 'Pierre a dit "Pas de Zone Monstre"',
            'description' => 'Défaussez tous les monstres actifs',
        ],
        'Placenta' => [
            'name' => 'Placenta',
            'description' => 'Soigne {{value}} PV au début de chaque tour.',
        ],
        'ImStillStanding' => [
            'name' => "I'm Still Standing",
            'description' => 'Lorsque le joueur meurt, il revient à la vie avec {{value}}% de ses PV max.',
        ],
        'Justice' => [
            'name' => 'Justice',
            'description' => 'Faites piocher au joueur actuel autant de cartes que le nombre de cartes dans la main de l\'autre joueur.',
        ],
        'ConsolationPrice' => [
            'name' => 'Prix de consolation',
            'description' => 'Chaque mort de monstre accorde {{value}} pièces au joueur.',
        ],
        'ViciousBee' => [
            'name' => 'Abeille Vicieuse',
            'description' => 'A sa mort, donne une <card>ViciousStinger</card>.',
        ],
        'ViciousStinger' => [
            'name' => 'Dard Vicieux',
            'description' => 'Applique un buff de dégâts à une carte.',
        ],
        'Play2Hurt' => [
            'name' => 'Jouer pour blesser',
            'description' => 'Inflige {{value}} dégâts a un joeueur à chaque caerte jouée.',
        ],
    ],
    // Effects
    'effects' => [
        CardEffectEnum::HACKED->value => [
            'name' => 'Hacké',
        ],
        CardEffectEnum::TORNED->value => [
            'name' => 'Tordu',
        ],
        CardEffectEnum::POWER_BOOST->value => [
            'name' => 'Boost de puissance',
        ],
    ],
    'rarity' => [
        CardRarityEnum::COMMON->value => 'Commune',
        CardRarityEnum::UNCOMMON->value => 'Non commune',
        CardRarityEnum::RARE->value => 'Rare',
        CardRarityEnum::EPIC->value => 'Epique',
        CardRarityEnum::LEGENDARY->value => 'Légendaire',
    ],
];
