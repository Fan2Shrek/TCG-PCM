<?php

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Game\Card\Interface\ComputedCardInterface;
use App\Game\GameUtils;
use App\Service\Game\Helper\HttpHelper;

final class Crypto4NoobCard extends AbstractMonsterCard implements ComputedCardInterface
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    private const HEALTH_POINTS = 15;
    private const URL = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=eur';

    private int $value = 0;

    public function getId(): string
    {
        return 'Crypto4Noob';
    }

    public function getBaseAttack(): int
    {
        return $this->value;
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }

    public function setComputedValue(mixed $value): void
    {
        $this->value = (int) $value;
    }

    public function computeValue(): mixed
    {
        if ($this->value > 0) {
            return $this->value;
        }

        /** @var HttpHelper $client */
        $client = GameUtils::getService('http');
        $response = $client->get(self::URL);

        $data = $response->toArray();

        return $this->value = (int) $data['bitcoin']['eur'];
    }
}
