<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Model\CardEffect;
use App\Enum\CardEffectEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<CardEffect>
 */
final class CardEffectProvider implements ProviderInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        foreach (CardEffectEnum::cases() as $cardEffect) {
            yield new CardEffect(
                $this->translator->trans(\sprintf('effects.%s.description', $cardEffect->value), [], 'game'),
                $this->translator->trans(\sprintf('effects.%s.description', $cardEffect->value), [], 'game'),
            );
        }
    }
}
