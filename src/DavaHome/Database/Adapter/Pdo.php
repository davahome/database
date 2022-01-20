<?php
declare(strict_types=1);

namespace DavaHome\Database\Adapter;

class Pdo extends \PDO
{
    public const DRIVER_MYSQL = 'mysql';
    public const DRIVER_SQLITE = 'sqlite';

    public static function create(string $driver, string $host, string $user, string $password, string $database, array $options = [])
    {
        $dsn = sprintf('%s:dbname=%s;host=%s', $driver, $database, $host);

        $options = array_replace([
            static::ATTR_PERSISTENT => false,
            static::ATTR_TIMEOUT    => 60,
            static::ATTR_ERRMODE    => static::ERRMODE_EXCEPTION,
        ], $options);

        return new static($dsn, $user, $password, $options);
    }
}
