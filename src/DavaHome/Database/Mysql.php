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
    public function prepare($statement, $driver_options = [])
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

    /**
     * @param string $query
     * @param array  $values
     * @param array  $where
     *
     * @return \PDOStatement
     */
    protected function buildQuery($query, array $values, array $where = null)
    {
        $i = 0;
        $queryData = [];

        // Create SET statement
        $columns = [];
        foreach ($values as $field => $value) {
            if ($value instanceof DirectValue) {
                $columns[] = sprintf('`%s` = %s', $field, $value->getValue());
            } else {
                $key = 'value_' . $i++;
                $columns[] = sprintf('`%s` = :%s', $field, $key);
                $queryData[$key] = $value;
            }
        }
        $query .= ' SET ' . implode(', ', $columns);

        // Create WHERE statement
        if ($where !== null) {
            $columns = [];
            foreach ($where as $field => $value) {
                if ($value instanceof DirectValue) {
                    $columns[] = sprintf('`%s` = %s', $field, $value->getValue());
                } else {
                    $key = 'where_' . $i++;
                    $columns[] = sprintf('`%s` = :%s', $field, $key);
                    $queryData[$key] = $value;
                }
            }
            $query .= ' WHERE ' . implode(' AND ', $columns);
        }

        return $this->execute($query, $queryData);
    }

    /**
     * Update a row
     *
     * @param string $table
     * @param array  $values key=>value
     * @param array  $where  key=>value where condition (will be combined using AND)
     * @param bool   $allowEmptyWhere
     *
     * @return \PDOStatement
     * @throws \Exception
     */
    public function update($table, array $values, array $where, $allowEmptyWhere = false)
    {
        if (!$allowEmptyWhere && empty($where)) {
            throw new \Exception('Empty where statements are not allowed!');
        }

        return $this->buildQuery(sprintf('UPDATE `%s`', $table), $values, $where);
    }

    /**
     * Insert a new row
     *
     * @param string $table
     * @param array  $values key=>value
     *
     * @return \PDOStatement
     */
    public function insert($table, array $values)
    {
        return $this->buildQuery(sprintf('INSERT INTO `%s`', $table), $values);
    }
}
