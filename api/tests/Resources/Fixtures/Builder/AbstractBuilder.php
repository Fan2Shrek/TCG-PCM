<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * @template T of object
 */
abstract class AbstractBuilder
{
    /**
     * @var T
     */
    protected object $entity;

    public function __construct(
        protected ContainerInterface $container,
    ) {}

    /**
     * @return T
     */
    public function build(): object
    {
        $this->doBuild();

        $this->getEm()->persist($this->entity);
        $this->getEm()->flush();

        return $this->entity;
    }

    abstract protected function doBuild(): void;

    protected function getEm(): EntityManagerInterface
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }
}
