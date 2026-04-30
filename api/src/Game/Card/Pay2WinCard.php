<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\Card\AbstractPlayableCard;
use App\Game\GameContext;
use App\Game\GameUtils;
use App\Service\Game\Helper\StripeHelper;
use Symfony\Component\Mercure\Update;

final class Pay2WinCard extends AbstractPlayableCard
{
    public function getId(): string
    {
        return 'Pay2Win';
    }

    public function play(GameContext $context, array $data = []): void
    {
        /** @var StripeHelper $stripe */
        $stripe = GameUtils::getService('stripe');

        if (!($amount = $data['amount'] ?? null)) {
            throw new \InvalidArgumentException('Amount is required');
        }

        if (!\is_int($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Invalid amount');
        }

        $url = $stripe->pay($amount, $data['url'] ?? '');

        GameUtils::getService('mercure')->publish(
            new Update(
                \sprintf('/game/%d', $context->getGame()->getId()),
                json_encode([
                    'type' => 'pay2win',
                    'url' => $url,
                ]),
            ),
        );
    }
}
