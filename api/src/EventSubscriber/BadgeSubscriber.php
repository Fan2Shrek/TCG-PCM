<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Badge\BadgeEventInterface;
use App\Event\Badge\BoosterOpenedEvent;
use App\Service\BadgeManager;
use Symfony\Component\DependencyInjection\Attribute\WhenNot;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[WhenNot('test')]
final class BadgeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BadgeManager $badgeManager,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BoosterOpenedEvent::class => 'onEvent',
            GamePlayedEvent::class => 'onEvent',
        ];
    }

    public function onEvent(BadgeEventInterface $event): void
    {
        $this->badgeManager->handleFromEvent($event);
    }
}
