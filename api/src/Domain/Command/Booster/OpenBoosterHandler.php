<?php

declare(strict_types=1);

namespace App\Domain\Command\Booster;

use App\Domain\Exception\NotEnoughTokenException;
use App\Domain\Model\Booster;
use App\Event\Badge\BoosterOpenedEvent;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Booster\BoosterGenerator;
use App\Service\InventoryUpdater;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final class OpenBoosterHandler
{
    public function __construct(
        private BoosterGenerator $boosterGenerator,
        private EventDispatcherInterface $eventDispatcher,
        private InventoryUpdater $inventoryUpdater,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(OpenBoosterCommand $command): Booster
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $wallet = $user->getUserWallet();

        if ($wallet->getBoosterTokens() <= 0) {
            throw new NotEnoughTokenException('Not enough booster tokens to open a booster.');
        }
        $wallet->removeBoosterToken(1);

        $this->eventDispatcher->dispatch(new BoosterOpenedEvent());

        $booster = $this->boosterGenerator->generateBooster($command->type);

        $this->inventoryUpdater->addCards($booster->getCards());

        return $booster;
    }
}
