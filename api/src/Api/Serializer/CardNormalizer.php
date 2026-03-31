<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Game\AbstractCard;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CardNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AbstractCard;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        /** @var AbstractCard $card */
        $card = $data;

        return [
            'name' => $card->getName(),
            'description' => $card->getDescription(),
            'rarity' => $card::$rarity,
            'serie' => $card::$serie,
            'image' => $card->getImage(),
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractCard::class => false,
        ];
    }
}
