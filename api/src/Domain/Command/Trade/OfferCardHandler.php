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
final class OfferCardHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function __invoke(OfferCardCommand $command): void
    {
        $trade = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if (!$trade->involves($user)) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not part of this trade.');
        }

        if (TradeStatusEnum::ACTIVE !== $trade->getStatus()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This trade is no longer active.');
        }

        $cardInventory = $user->getInventory()->findCardByCardId($command->card);

        if (null === $cardInventory || $cardInventory->getQuantity() < 1) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'You do not own this card.');
        }

        if ($trade->getInitiator() === $user) {
            $trade->setInitiatorCard($command->card);
        } else {
            $trade->setRecipientCard($command->card);
        }

        // Changing an offer resets both sides' readiness to prevent a last-second bait-and-switch.
        $trade->setInitiatorConfirmed(false);
        $trade->setRecipientConfirmed(false);

        $this->em->flush();

        $this->hub->publish(
            new Update(
                \sprintf('trade/%s', $trade->getId()),
                json_encode([
                    'type' => 'card_offered',
                    'data' => [
                        'by' => $user->getUsername(),
                        'card' => $command->card,
                    ],
                ], JSON_THROW_ON_ERROR),
                true,
            ),
        );
    }
}
