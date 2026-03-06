<?php

declare(strict_types=1);

namespace App\Service\Redis;

class RedisClient
{
    public function __construct(
        private RedisConnection $connection,
    ) {}

    /**
     * @template T of object
     *
     * @param string $key
     * @param class-string<T> $type
     *
     * @return ?T
     */
    public function get(string $key, string $type): ?object
    {
        $data = $this->connection->get($key);

        if (!\is_string($data)) {
            return null;
        }

        $value = unserialize($data);

        if ($value !== null && !$value instanceof $type) {
            throw new \UnexpectedValueException(sprintf('Expected value of type "%s", got "%s".', $type, get_debug_type($value)));
        }

        return $value;
    }

    public function set(string $key, object $value): void
    {
        $data = serialize($value);

        $this->connection->set($key, $data);
    }
}
