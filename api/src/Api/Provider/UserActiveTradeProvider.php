<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Trade;
use App\Repository\TradeRepository;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<Trade>
 */
final class UserActiveTradeProvider implements ProviderInterface
{
    public function __construct(
        private TradeRepository $tradeRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $trade = $this->tradeRepository->findActiveForUser($this->currentUserProvider->getCurrentUser());

        return null === $trade ? [] : [$trade];
    }
}
