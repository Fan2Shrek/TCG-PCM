<?php

declare(strict_types=1);

namespace App\Service\Redis;

final class RedisConnection
{
    private \Redis $connection;

    public function __construct(string $redisDsn)
    {
        $infos = parse_url($redisDsn);
        $this->connection = new \Redis();

        if (!$infos || !($infos['host'] ?? null)) {
            throw new \InvalidArgumentException('Invalid Redis DSN');
        }

        $this->connection->connect($infos['host'], $infos['port'] ?? 6379);
    }

    public function get(string $key): mixed
    {
        return $this->connection->get($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->connection->set($key, $value);
    }

    public function del(string $key): void
    {
        $this->connection->del($key);
    }

    public function flushAll(): void
    {
        $this->connection->flushAll();
    }
}
