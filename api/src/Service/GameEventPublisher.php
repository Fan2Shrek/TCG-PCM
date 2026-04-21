<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class GameEventPublisher
{
    private const PRIVATE_EVENTS = [
        GameEventTypeEnum::CARD_DRAWN,
    ];

    public function __construct(
        private HubInterface $hub,
        private GameEventPresenter $presenter,
    ) {}

    /**
     * @param GameEvent[] $events
     */
    public function publish(array $events, GameState $state, string $room): void
    {
        $topic = $this->getTopic($room);

        $payload = [
            'type' => 'game_events',
            'events' => array_map(fn(GameEvent $event) => $this->presenter->present($event, $state, false, null), $events),
        ];

        $this->doPublish($topic, $payload);

        // private events
        foreach ($this->groupPrivateEvents($events) as $playerId => $playerEvents) {
            $this->doPublish($topic.'-'.((string) $playerId === $state->player1->player->id ? '1' : '2'), [
                'type' => 'game_events',
                'events' => array_map(fn(GameEvent $event) => $this->presenter->present($event, $state, true, (string) $playerId), $playerEvents),
            ]);
        }
    }

    private function doPublish(string $topci, array $data): void
    {
        $update = new Update($topci, json_encode($data, JSON_THROW_ON_ERROR), true);

        $this->hub->publish($update);
    }

    private function getTopic(string $room): string
    {
        return \sprintf('game/%s', $room);
    }

    /**
     * @param GameEvent[] $events
     *
     * @return array<string, GameEvent[]>
     */
    private function groupPrivateEvents(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            if (!$this->isPrivate($event)) {
                continue;
            }

            /** @var string $playerId */
            $playerId = $event->data['playerId'];

            if (!$playerId) {
                continue;
            }

            $grouped[$playerId][] = $event;
        }

        return $grouped;
    }

    private function isPrivate(GameEvent $event): bool
    {
        return \in_array($event->type, self::PRIVATE_EVENTS, true);
    }
}
