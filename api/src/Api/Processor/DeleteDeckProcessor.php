<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Deck;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements ProcessorInterface<Deck, mixed>
 */
final class DeleteDeckProcessor implements ProcessorInterface
{
    public function __construct(
        private DeckRepository $deckRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Deck) {
            return null;
        }

        if ($data->isDeleted()) {
            return null;
        }

        if ($this->deckRepository->countActiveByUser($data->getUser()) <= 1) {
            throw HttpException::fromStatusCode(
                Response::HTTP_CONFLICT,
                'Impossible de supprimer votre dernier deck.',
            );
        }

        $data->setIsDeleted(true);
        $this->entityManager->flush();

        return null;
    }
}
