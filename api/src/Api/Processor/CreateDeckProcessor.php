<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\DTO\CreateDeckInput;
use App\Entity\Deck;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\DeckValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<CreateDeckInput, Deck>
 */
final class CreateDeckProcessor implements ProcessorInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private DeckValidator $deckValidator,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Deck
    {
        if (!$data instanceof CreateDeckInput) {
            throw new \InvalidArgumentException('Invalid payload for deck creation.');
        }

        $name = trim($data->name);
        if ('' === $name) {
            throw new \InvalidArgumentException('Deck name is required.');
        }

        $characterCard = trim($data->characterCard);
        if ('' === $characterCard) {
            throw new \InvalidArgumentException('Character card is required.');
        }

        $cards = array_values(array_filter($data->cards, static fn(mixed $cardId): bool => is_string($cardId) && '' !== trim($cardId)));

        $deck = new Deck(user: $this->currentUserProvider->getCurrentUser(), name: $name, characterCard: $characterCard, cards: $cards);

        $deck->setIsFavorite((bool) ($data->isFavorite ?? false));

        $this->deckValidator->validateDeck($deck);

        $this->entityManager->persist($deck);
        $this->entityManager->flush();

        return $deck;
    }
}
