<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\CollectionCardDTO;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Monster\AbstractMonsterCard;
use App\Service\Booster\BoosterRegistry;
use App\Service\Booster\Types\BoosterInterface;
use App\Service\Game\CardRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProviderInterface<CollectionCardDTO>
 */
final class BoosterObtainableCardsProvider implements ProviderInterface
{
    public function __construct(
        private BoosterRegistry $boosterRegistry,
        private CardRegistryInterface $cardRegistry,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $type = $request?->query->get('type', 'default') ?? 'default';
        $offset = max(0, (int) ($request?->query->get('offset', 0) ?? 0));
        $stepRaw = $request?->query->get('step');
        $step = null;
        if (null !== $stepRaw && '' !== $stepRaw) {
            $step = max(1, (int) $stepRaw);
        }

        $boosterClass = $this->boosterRegistry->getBoosterType($type);
        if (!class_exists($boosterClass) || interface_exists($boosterClass) || !is_subclass_of($boosterClass, BoosterInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf('Booster type "%s" must implement BoosterInterface.', $type));
        }

        /** @var BoosterInterface $booster */
        $booster = new $boosterClass();
        $criteria = $booster->getCardsCriteria();

        $cardIds = $this->cardRegistry->getAllBy($criteria);
        sort($cardIds);

        $total = count($cardIds);
        $paginatedIds = null === $step ? array_slice($cardIds, $offset) : array_slice($cardIds, $offset, $step);

        $cards = array_map(function (string $cardId): CollectionCardDTO {
            $card = $this->cardRegistry->getCardTemplateById($cardId);

            $cost = null;
            $hp = null;
            $attack = null;

            if (!$card instanceof AbstractCharacterCard) {
                $cost = $card->getCost();
            }

            if ($card instanceof AbstractMonsterCard) {
                $hp = $card->getHealPoints();
                $attack = $card->getAttack();
            }

            return new CollectionCardDTO(
                name: $card->getName(),
                description: $card->getDescription(),
                image: $card->getImage(),
                rarity: $card::$rarity,
                set: $card::$serie,
                instanceId: $card->getId(),
                type: $card->getType(),
                cost: $cost,
                hp: $hp,
                attack: $attack,
                isNewToCollection: false,
            );
        }, $paginatedIds);

        return [
            'type' => $type,
            'offset' => $offset,
            'step' => $step,
            'total' => $total,
            'cards' => $cards,
        ];
    }
}
