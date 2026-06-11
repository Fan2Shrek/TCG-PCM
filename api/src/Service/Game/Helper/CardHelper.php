<?php

declare(strict_types=1);

namespace App\Service\Game\Helper;

use App\Game\AbstractCard;
use App\Service\Game\CardRegistryInterface;

final class CardHelper
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {}

    public function getCardTemplate(string $templateId): AbstractCard
    {
        return $this->cardRegistry->getCardTemplateById($templateId);
    }
}
