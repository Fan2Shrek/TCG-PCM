<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\Deck;
use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserWallet;
use App\Enum\CardRarityEnum;
use App\Enum\CardTypeEnum;
use App\Repository\UserRepository;
use App\Service\Game\CardRegistryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrar
{
    private const STARTER_DECK_SIZE = 50;
    private const STARTER_MAX_COPIES_PER_CARD = 5;

    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private CardRegistryInterface $cardRegistry,
        private PasswordPolicy $passwordPolicy,
    ) {}

    public function register(string $username, string $email, #[\SensitiveParameter] string $password): User
    {
        if (mb_strlen($username) < 3) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Username must be at least 3 characters.');
        }

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Email must be valid.');
        }

        $this->passwordPolicy->assertValid($password);

        if (null !== $this->userRepository->findOneBy(['username' => $username])) {
            throw HttpException::fromStatusCode(Response::HTTP_CONFLICT, 'Username already taken.');
        }

        if (null !== $this->userRepository->findOneBy(['email' => $email])) {
            throw HttpException::fromStatusCode(Response::HTTP_CONFLICT, 'Email already taken.');
        }

        $user = new User($username, $email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $inventory = new Inventory($user);
        $starterDistribution = $this->buildStarterCardDistribution();

        foreach ($starterDistribution as $cardId => $quantity) {
            $starterCard = new CardInventory($inventory, $cardId)->setQuantity($quantity);
            $inventory->addCard($starterCard);
            $this->em->persist($starterCard);
        }

        $starterCharacterCardId = $this->getStarterCharacterCardId();
        $starterCharacterCard = new CardInventory($inventory, $starterCharacterCardId)->setQuantity(1);
        $inventory->addCard($starterCharacterCard);
        $this->em->persist($starterCharacterCard);

        $starterDeckCards = [];
        foreach ($starterDistribution as $cardId => $quantity) {
            for ($i = 0; $i < $quantity; ++$i) {
                $starterDeckCards[] = $cardId;
            }
        }

        $starterDeck = new Deck(user: $user, name: 'Starter Deck', characterCard: $starterCharacterCardId, cards: $starterDeckCards);
        $starterDeck->setIsFavorite(true);

        $this->em->persist(new UserWallet($user));
        $this->em->persist(new UserInfo($user));
        $this->em->persist($inventory);
        $this->em->persist($starterDeck);
        $this->em->flush();

        return $user;
    }

    /**
     * @return array<string, int>
     */
    private function buildStarterCardDistribution(): array
    {
        $commonCards = $this->cardRegistry->getAllBy([
            'rarity' => CardRarityEnum::COMMON,
        ]);

        $eligibleCards = array_values(array_filter(
            $commonCards,
            fn(string $cardId): bool => CardTypeEnum::CHARACTER !== $this->cardRegistry->getCardTemplateById($cardId)->getType(),
        ));

        sort($eligibleCards);

        if ((count($eligibleCards) * self::STARTER_MAX_COPIES_PER_CARD) < self::STARTER_DECK_SIZE) {
            throw HttpException::fromStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, 'Not enough common cards available to build starter inventory.');
        }

        $distribution = [];
        $remaining = self::STARTER_DECK_SIZE;

        foreach ($eligibleCards as $cardId) {
            if (0 === $remaining) {
                break;
            }

            $copies = min(self::STARTER_MAX_COPIES_PER_CARD, $remaining);
            $distribution[$cardId] = $copies;
            $remaining -= $copies;
        }

        if ($remaining > 0) {
            throw HttpException::fromStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unable to distribute enough starter common cards.');
        }

        return $distribution;
    }

    private function getStarterCharacterCardId(): string
    {
        $allCards = $this->cardRegistry->getAllBy([]);

        $characterCards = array_values(array_filter(
            $allCards,
            fn(string $cardId): bool => CardTypeEnum::CHARACTER === $this->cardRegistry->getCardTemplateById($cardId)->getType(),
        ));

        sort($characterCards);

        if ([] === $characterCards) {
            throw HttpException::fromStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, 'No character card available for starter inventory.');
        }

        return $characterCards[0];
    }
}
