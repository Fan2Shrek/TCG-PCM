<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Game\Card\CardState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CardListType extends Type
{
    public const NAME = 'card_list';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!\is_array($value)) {
            throw new \InvalidArgumentException('Expected array.');
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, CardState>|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return $value;
        }

        /** @var array<string, array{instanceId: string, templateId: string, ownerId: string}> $data */
        $data = json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
        $cards = [];

        foreach ($data as $cardId => $cardData) {
            $cards[$cardId] = new CardState($cardData['instanceId'], $cardData['templateId'], $cardData['ownerId']);
        }

        return $cards;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
