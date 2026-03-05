<?php

declare(strict_types=1);

namespace App\Domain\Command\Booster;

use App\Domain\Model\Booster;
use App\Event\Badge\BoosterOpenedEvent;
use App\Service\BoosterGenerator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final class OpenBoosterHandler
{
    public function __construct(
        private BoosterGenerator $boosterGenerator,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(OpenBoosterCommand $command): Booster
    {
        $this->eventDispatcher->dispatch(new BoosterOpenedEvent());

        return $this->boosterGenerator->generateBooster();
    }
}
