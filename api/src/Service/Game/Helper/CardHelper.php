<?php

declare(strict_types=1);

namespace App\Service\Game\Helper;

use App\Game\AbstractCard;
use App\Service\Game\CardIdGeneratorInterface;
use App\Service\Game\CardRegistryInterface;

final class CardHelper
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private CardIdGeneratorInterface $cardIdGenerator,
    ) {}

    public function getCardTemplate(string $templateId): AbstractCard
    {
        return $this->cardRegistry->getCardTemplateById($templateId);
    }

    public function generateCardId(string $templateId): string
    {
        return $this->cardIdGenerator->generateCardId($templateId);
    }
}
