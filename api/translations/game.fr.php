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
        'Banana' => [
            'name' => 'Banane',
            'description' => 'Soigne votre personnage de {{value}} PV.',
        ],
        'BananaFarm' => [
            'name' => 'Ferme à bananes',
            'description' => 'Gagne {{value}} pièce(s) au debut de chaque tour.',
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
            'name' => 'Ballon Rouge',
            'description' => 'Potit ballon tout mignon',
        ],
        'CamoBloon' => [
            'name' => 'Ballon Camo',
            'description' => 'A {{value}}% de chances d\'esquiver les dégâts reçus.',
        ],
        'MOAB' => [
            'name' => 'MOAB',
            'description' => 'Un énorme ballon.',
        ],
        'LeadBloon' => [
            'name' => 'Ballon de Plomb',
            'description' => 'Réduit les dégâts reçus de {{value}}.',
        ],
        'DartMonkey' => [
            'name' => 'Singe',
            'description' => 'Un potit singe.',
        ],
        'BoomerangMonkey' => [
            'name' => 'Singe Boomerang',
            'description' => 'Perd {{value}} PV après avoir attaqué.',
        ],
        'SuperMonkey' => [
            'name' => 'Super singe',
            'description' => 'Gagne +{{value}} attaque et PV pour chaque singe de votre côté lors de son arrivée en jeu.',
        ],
        'HackedZone' => [
            'name' => 'Zone Piratée',
            'description' => 'Applique {{effect}} à toutes les cartes des deux côtés',
        ],
        'PierreSaidNoMonsterZone' => [
            'name' => 'Pierre a dit "Pas de Zone Monstre"',
            'description' => 'Défaussez tous les monstres actifs',
        ],
        'Bomb' => [
            'name' => 'Bombe',
            'description' => 'Inflige {{value}} dégâts à tous les monstres en jeu.',
        ],
        'Dart' => [
            'name' => 'Fléchette',
            'description' => 'Inflige {{value}} dégâts à une carte ciblée.',
        ],
        'Pills' => [
            'name' => 'Pillules !',
            'description' => 'Effet aléatoire !',
        ],
        'Horsepill' => [
            'name' => 'Pillule de cheval !!!',
            'description' => 'Effet très aléatoire !!!',
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
        'Clotty' => [
            'name' => 'Coagulé',
            'description' => 'Un potit monstre.',
        ],
            'GrilledClotty' => [
                'name' => 'Coagulé grillé',
                'description' => 'Perd 1 PV à la fin de chaque tour.',
            ],
            'Henry' => [
                'name' => 'Henry',
                'description' => 'Est défaussé au début du prochain tour de son propriétaire.',
            ],
            'HoneyBee' => [
                'name' => 'Abeille de miel',
                'description' => 'Quand elle attaque, soigne votre personnage de {{value}} PV.',
            ],
            'RadicalRat' => [
                'name' => 'Rat Radical',
                'description' => 'Lorsqu\'il est joué ou qu\'il meurt, inflige {{value}} dégâts à tous les monstres et personnages adverses.',
            ],
        'ConsolationPrice' => [
            'name' => 'Prix de consolation',
            'description' => 'Chaque mort de monstre accorde {{value}} pièces au joueur.',
        ],
        'consolation_prices' => [
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
            'description' => 'Inflige {{value}} dégâts a un joeueur à chaque carte jouée.',
        ],
        'StackyStackito' => [
            'name' => 'Stacky Stackito',
            'description' => 'Chaque {{value}} tours, fait le nombre de pièces comme dégats.',
        ],
        'RiskyBet' => [
            'name' => 'Pari risqué',
            'description' => 'Lancez un dé à dix faces. Si le résultat est de 9 ou 10, infligez {{value}} dégâts à votre adversaire. Sinon, infligez {{value2}} dégâts à vous-même.',
        ],
        'Necromancian' => [
            'name' => 'Nécromancien',
            'description' => 'Ressuscite une carte aléatoire à la fin du tour.',
        ],
        'Isaac' => [
            'name' => 'Isaac',
            'description' => 'Inflige 5 dégâts à une carte adverse aléatoire à chaque tour.',
        ],
        'Communism' => [
            'name' => 'Communisme',
            'description' => 'Partage equitablement les pièces entre les joueurs.',
        ],
        'BloodSucker' => [
            'name' => 'Suceur de sang',
            'description' => 'Inflige {{value}} à tous les joueurs à chaque début de tour',
        ],
        'TheHand' => [
            'nam' => 'La main',
            'description' => 'Défausse une carte aléatoire de la zone de jeu de votre adversaire.',
        ],
        'Coins' => [
            'name' => 'Pièces',
            'description' => 'Gagne {{value}} pièces.',
        ],
        'Crypto4Noob' => [
            'name' => 'Crypto4Noob',
            'description' => 'Inflige des dégâts égaux au prix actuel du Bitcoin en euros.',
        ],
        'MimeticPrismRuby' => [
            'name' => 'Prisme Mimétique Rubis',
            'description' => 'Copie une carte monstre aléatoire en jeu. Inflige 2x dégâts et a 0.5x PV.',
        ],
        'MimeticPrismAmethyst' => [
            'name' => 'Prisme Mimétique Améthyste',
            'description' => 'Copie une carte monstre aléatoire en jeu. Inflige 4x dégâts et a 1 PV.',
        ],
        'MimeticPrismSaphir' => [
            'name' => 'Prisme Mimétique Saphir',
            'description' => 'Copie une carte monstre aléatoire en jeu. Inflige 0.5x dégâts et a 2x PV.',
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
