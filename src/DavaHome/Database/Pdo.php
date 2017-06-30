<?php
namespace DavaHome\Database;

class Pdo extends \PDO
{
    const DRIVER_MYSQL = 'mysql';
    const DRIVER_SQLITE = 'sqlite';

    /**
     * @param string $driver
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param array  $options
     *
     * @return static
     */
    public static function create($driver, $host, $user, $password, $database, $options = [])
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
