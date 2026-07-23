<?php

namespace App\Game\Card;

use App\Game\Card\Interface\ComputedCardInterface;
use App\Game\GameContext;
use App\Game\GameUtils;
use App\Service\Game\Helper\HttpHelper;

final class ScratchGameAddictCard extends AbstractPlayableCard implements ComputedCardInterface
{
    private const API_URL = 'https://query1.finance.yahoo.com/v8/finance/chart/FDJU.PA';
    private const int DAMAGE_MULTIPLIER = 1;

    private int $dmg = 0;

    public function getId(): string
    {
        return 'ScratchGameAddict';
    }

    public function getImage(): string
    {
        return 'https://media.discordapp.net/attachments/529955910752141322/1529858468490514482/AHRPTWno38OJ35Jt1wksz2vvzh3uyC2Ei0CRNFRh3FYjyRC2jtl6JIw5CfV1RsRI3CpjdixhiQNmyP5fhUBc4PwuNo_90Xl_0ZFA7sI32HvksUm0d0aYtfaUPcErrWyWJWRU5GmBNTGds680-w680-h510.png?ex=6a63773a&is=6a6225ba&hm=64364e2aab5e65075ec08a9fc8aad05e5f10a5ba40c55ac752928fb1c88dabc5&=&format=webp&quality=lossless';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => self::DAMAGE_MULTIPLIER,
        ]);
    }

    public function play(GameContext $context, array $data = []): void
    {
        $value = fn() => (int) $this->getValue(self::DAMAGE_MULTIPLIER, true) * $this->computeValue();

        $value = $context->runtimeValueEffect($value);

        if (!\is_int($value)) {
            throw new \RuntimeException('Expected an integer value for GitmanCard damage calculation.');
        }
        $context->attack($value);
    }

    public function computeValue(): mixed
    {
        if ($this->dmg > 0) {
            return $this->dmg;
        }

        /** @var HttpHelper $client */
        $client = GameUtils::getService('http');
        $response = $client->get(self::API_URL);

        $data = $response->toArray();
        $close = array_reverse($data['chart']['result'][0]['indicators']['quote'][0]['close']) ?? [0];

        while ($close[0] === null) {
            array_shift($close);
        }

        return $this->dmg = (int) round($close[0]);
    }

    public function setComputedValue(mixed $value): void
    {
        $this->dmg = (int) $value;
    }
}
