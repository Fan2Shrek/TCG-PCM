<?php

declare(strict_types=1);

namespace App\Domain\Command\Trade;

use App\Entity\Trade;
use App\Enum\TradeStatusEnum;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\TradeExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ConfirmTradeHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private TradeExecutor $tradeExecutor,
        private HubInterface $hub,
    ) {}

    public function __invoke(ConfirmTradeCommand $command): Trade
    {
        $trade = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if (!$trade->involves($user)) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not part of this trade.');
        }

        if (TradeStatusEnum::ACTIVE !== $trade->getStatus()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This trade is no longer active.');
        }

        $isInitiator = $trade->getInitiator() === $user;
        $ownCard = $isInitiator ? $trade->getInitiatorCard() : $trade->getRecipientCard();

        if (null === $ownCard) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'You must offer a card before confirming.');
        }

        if ($isInitiator) {
            $trade->setInitiatorConfirmed(true);
        } else {
            $trade->setRecipientConfirmed(true);
        }

        if (!$trade->isInitiatorConfirmed() || !$trade->isRecipientConfirmed()) {
            $this->em->flush();

            $this->hub->publish(
                new Update(
                    \sprintf('trade/%s', $trade->getId()),
                    json_encode(['type' => 'trade_confirm_updated', 'data' => ['by' => $user->getUsername()]], JSON_THROW_ON_ERROR),
                    true,
                ),
            );

            return $trade;
        }

        $this->tradeExecutor->execute($trade);

        $this->hub->publish(
            new Update(
                \sprintf('trade/%s', $trade->getId()),
                json_encode([
                    'type' => 'trade_completed',
                    'data' => [
                        'initiatorCard' => $trade->getInitiatorCard(),
                        'recipientCard' => $trade->getRecipientCard(),
                    ],
                ], JSON_THROW_ON_ERROR),
                true,
            ),
        );

        return $trade;
    }
}
