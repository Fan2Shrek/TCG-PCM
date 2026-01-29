<?php

declare(strict_types=1);

namespace App\Api\Serializer;

/**
* @template T of object
*/
interface CurrentResourceAwareInterface
{
    /**
    * @param T $resource
    */
    public function setCurrentResource(object $resource): void;

    /**
    * @return T
    */
    public function getCurrentResource(): object;
}
