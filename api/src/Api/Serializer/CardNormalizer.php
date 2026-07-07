<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\DTO\CardDTO;
use App\Game\AbstractCard;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CardNormalizer implements NormalizerInterface
{
    private const string CARD_IMAGE_BASE_URL = 'cards/';

    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AbstractCard || $data instanceof CardDTO;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        /** @var AbstractCard|CardDTO $card */
        $card = $data;

        $path = $card instanceof AbstractCard ? $card->getImage() : $card->image;
        $type = $card instanceof AbstractCard ? $card->getType() : $card->type;
        $rarity = $card instanceof AbstractCard ? $card::$rarity : $card->rarity;
        $serie = $card instanceof AbstractCard ? $card::$serie : $card->set;

        return array_filter([
            'name' => $card instanceof AbstractCard ? $card->getName() : $card->name,
            'description' => $card instanceof AbstractCard ? $card->getDescription() : $card->description,
            'type' => $type?->name,
            'typeLabel' => $type?->label()->trans($this->translator),
            'rarity' => $rarity->name,
            'rarityLabel' => $rarity->label()->trans($this->translator),
            'serie' => $serie->name,
            'serielabel' => $serie,
            'image' => filter_var($path, FILTER_VALIDATE_URL) ? $path : self::CARD_IMAGE_BASE_URL.strtolower($path),
            'cost' => $card instanceof CardDTO ? $card->cost : null,
            'hp' => $card instanceof CardDTO ? $card->hp : null,
            'attack' => $card instanceof CardDTO ? $card->attack : null,
            'instanceId' => $card instanceof CardDTO ? $card->instanceId : null,
            'effects' => $card instanceof CardDTO ? $card->effects : null,
            'isActive' => $card instanceof CardDTO ? $card->isActive : null,
        ]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractCard::class => true,
            CardDTO::class => true,
        ];
    }
}
