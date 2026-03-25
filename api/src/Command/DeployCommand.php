<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command calls all DeployAwareInterface service
 * This, for example allows to reset reaplays, that relies on source code,
 * and that should be reset when a new version of the code is deployed.
 *
 * NB: I was half drunk when I wrote this, so I hope it works as expected, and that I didn't forget anything important.
 * Times kinda hard...
 */
#[AsCommand(name: 'app:deploy', description: 'Call all deploy aware services, and reset them.')]
final class DeployCommand
{
    /**
     * @param \Traversable<string, object> $services
     * @param array<string, string[]> $methods
     */
    public function __construct(
        private \Traversable $services,
        private array $methods = [],
    ) {}

    public function __invoke(InputInterface $input, OutputInterface $ouput): int
    {
        $io = new SymfonyStyle($input, $ouput);

        if (!$io->confirm('This command will call all deploy aware services, and reset them. Do you want to continue?', !$input->isInteractive())) {
            return Command::SUCCESS;
        }

        foreach ($this->services as $id => $service) {
            foreach ($this->methods[$id] as $deployMethod) {
                if (!method_exists($service, $deployMethod)) {
                    continue;
                }

                $service->$deployMethod();
            }
        }

        return Command::SUCCESS;
    }
}
