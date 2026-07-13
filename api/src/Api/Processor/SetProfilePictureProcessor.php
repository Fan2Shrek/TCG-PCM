<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Command\User\SetProfilePictureCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @implements ProcessorInterface<mixed, array>
 */
final class SetProfilePictureProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private MessageBusInterface $messageBus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $file = $this->requestStack->getCurrentRequest()?->files->get('profilePicture');
        $file = $file instanceof UploadedFile ? $file : null;

        try {
            $envelope = $this->messageBus->dispatch(new SetProfilePictureCommand($file));
        } catch (HandlerFailedException $e) {
            // unwrap the exception thrown in the handler, same as API Platform's own Messenger processor
            $previous = $e->getPrevious();
            throw $previous ?? $e;
        }

        $handledStamp = $envelope->last(HandledStamp::class);
        if (!$handledStamp instanceof HandledStamp) {
            throw new \LogicException('Missing handled stamp for profile picture command.');
        }

        $result = $handledStamp->getResult();
        if (!\is_array($result)) {
            throw new \LogicException('Expected profile picture command result to be an array.');
        }

        return $result;
    }
}
