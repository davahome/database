<?php

namespace DavaHome\Database;

class Mysql extends Pdo implements DatabaseInterface
{
    const ISOLATION_LEVEL_READ_UNCOMITTED = 'READ UNCOMMITED';
    const ISOLATION_LEVEL_READ_COMMITTED = 'READ COMMITTED';
    const ISOLATION_LEVEL_REPEATABLE_READ = 'REPEATABLE READ';
    const ISOLATION_LEVEL_SERIALIZABLE = 'SERIALIZABLE';

    /** @var array|\PDOStatement[] */
    protected $stmtCache = [];

    /**
     * @inheritDoc
     */
    public static function create($driver, $host, $user, $password, $database, $options = [])
    {
        $db = parent::create($driver, $host, $user, $password, $database, $options);
        $db->exec('SET NAMES "UTF8"');
        $db->exec('SET CHARACTER SET utf8');

        return $db;
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
     * Set the isolation level
     *
     * @param string $isolationLevel
     *
     * @return bool
     */
    public function setIsolationLevel($isolationLevel)
    {
        if (!in_array($isolationLevel, [
            self::ISOLATION_LEVEL_READ_UNCOMITTED,
            self::ISOLATION_LEVEL_READ_COMMITTED,
            self::ISOLATION_LEVEL_REPEATABLE_READ,
            self::ISOLATION_LEVEL_SERIALIZABLE
        ])) {
            return false;
        }

        return $this->exec('SET TRANSACTION ISOLATION LEVEL '.$isolationLevel) !== false;
    }

    /**
     * @param string $query
     * @param array  $values
     * @param array  $where
     *
     * @return \PDOStatement
     */
    protected function buildQuery($query, array $values = null, array $where = null)
    {
        $i = 0;
        $queryData = [];

        // Create SET statement
        if ($values !== null) {
            $columns = [];
            foreach ($values as $field => $value) {
                $operator = '=';
                if ($value instanceof CustomOperator) {
                    $operator = $value->getOperator();
                    $value = $value->getValue();
                }

                if ($value instanceof DirectValue) {
                    $columns[] = sprintf('`%s` %s %s', $field, $operator, $value->getValue());
                } else {
                    $key = 'value_'.$i++;
                    $columns[] = sprintf('`%s` %s :%s', $field, $operator, $key);
                    $queryData[$key] = $value;
                }
            }
            $query .= ' SET '.implode(', ', $columns);
        }

        // Create WHERE statement
        if (!empty($where)) {
            $columns = [];
            foreach ($where as $field => $value) {
                $operator = '=';
                if ($value instanceof CustomOperator) {
                    $operator = $value->getOperator();
                    $value = $value->getValue();
                }

                if ($value instanceof DirectValue) {
                    $columns[] = sprintf('`%s` %s %s', $field, $operator, $value->getValue());
                } else {
                    $key = 'where_'.$i++;
                    $columns[] = sprintf('`%s` %s :%s', $field, $operator, $key);
                    $queryData[$key] = $value;
                }
            }
            $query .= ' WHERE '.implode(' AND ', $columns);
        }

        return $this->execute($query, $queryData);
    }

    /**
     * Let the database create a UUID
     *
     * @return string
     */
    public function createUuid()
    {
        $stmt = $this->execute('SELECT UUID()');
        list($uuid) = $stmt->fetch(Mysql::FETCH_NUM);

        return $uuid;
    }

    /**
     * Update a row
     *
     * @param string $table
     * @param array  $values key=>value
     * @param array  $where  key=>value where condition (will be combined using AND)
     * @param bool   $allowEmptyWhere
     *
     * @return mixed
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
     * @return mixed
     */
    public function insert($table, array $values)
    {
        return $this->buildQuery(sprintf('INSERT INTO `%s`', $table), $values);
    }

    /**
     * Select from database
     *
     * @param string $table
     * @param array  $where
     *
     * @return mixed
     */
    public function select($table, array $where)
    {
        return $this->buildQuery(sprintf('SELECT * FROM `%s`', $table), null, $where);
    }

    /**
     * Delete a from database
     *
     * @param string $table
     * @param array  $where
     * @param bool   $allowEmptyWhere
     *
     * @return mixed
     * @throws \Exception
     */
    public function delete($table, array $where, $allowEmptyWhere = false)
    {
        if (!$allowEmptyWhere && empty($where)) {
            throw new \Exception('Empty where statements are not allowed!');
        }

        return $this->buildQuery(sprintf('DELETE FROM `%s`', $table), null, $where);
    }
}
