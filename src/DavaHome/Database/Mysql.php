<?php

namespace DavaHome\Database;

class Mysql extends Pdo
{
    /** @var array|\PDOStatement[] */
    protected $stmtCache = [];

    /**
     * @inheritDoc
     */
    public static function create($driver, $host, $user, $password, $database, $options = [])
    {
        $options = array_merge([
            static::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"',
        ], $options);

        return parent::create($driver, $host, $user, $password, $database, $options);
    }

    /**
     * @param string $statement
     * @param string $driverOptions
     *
     * @return string
     */
    protected function calculateStatementHash($statement, $driverOptions)
    {
        if (is_array($driverOptions)) {
            ksort($driverOptions);
        }

        return md5(json_encode([$statement, $driverOptions]));
    }

    /**
     * @inheritDoc
     */
    public function prepare($statement, array $driver_options = [])
    {
        $hash = $this->calculateStatementHash($statement, $driver_options);
        if (isset($this->stmtCache[$hash])) {
            return $this->stmtCache[$hash];
        }

        return $this->stmtCache[$hash] = parent::prepare($statement, $driver_options);
    }

    /**
     * Create and execute a prepared statement immediately
     *
     * @param string $statement
     * @param array  $inputParameters
     * @param array  $driverOptions
     *
     * @return mixed|\PDOStatement
     */
    public function execute($statement, array $inputParameters = [], array $driverOptions = [])
    {
        $stmt = $this->prepare($statement, $driverOptions);
        $stmt->execute($inputParameters);

        return $stmt;
    }
}
