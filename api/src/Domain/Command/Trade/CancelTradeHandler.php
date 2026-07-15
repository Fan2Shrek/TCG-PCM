<?php

declare(strict_types=1);

namespace App\Domain\Command\Trade;

use App\Enum\TradeStatusEnum;
use App\Service\Auth\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CancelTradeHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function __invoke(CancelTradeCommand $command): void
    {
        $trade = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if (!$trade->involves($user)) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not part of this trade.');
        }

        if (TradeStatusEnum::ACTIVE !== $trade->getStatus()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This trade is no longer active.');
        }

        $trade->setStatus(TradeStatusEnum::CANCELLED);

        $this->em->flush();

        $this->hub->publish(
            new Update(
                \sprintf('trade/%s', $trade->getId()),
                json_encode(['type' => 'trade_cancelled', 'data' => ['by' => $user->getUsername()]], JSON_THROW_ON_ERROR),
                true,
            ),
        );
    }
}
