<?php
declare(strict_types=1);

namespace DavaHome\Database\Adapter;

use DavaHome\Database\DatabaseException;
use DavaHome\Database\Extension\CustomOperator;
use DavaHome\Database\Extension\DirectValue;
use PDOStatement;

class Mysql extends Pdo implements AdapterInterface
{
    public const ISOLATION_LEVEL_READ_UNCOMITTED = 'READ UNCOMMITED';
    public const ISOLATION_LEVEL_READ_COMMITTED = 'READ COMMITTED';
    public const ISOLATION_LEVEL_REPEATABLE_READ = 'REPEATABLE READ';
    public const ISOLATION_LEVEL_SERIALIZABLE = 'SERIALIZABLE';

    /** @var array<PDOStatement> */
    protected array $stmtCache = [];

    public static function create(string $driver, string $host, string $user, string $password, string $database, array $options = []): static
    {
        $db = parent::create($driver, $host, $user, $password, $database, $options);
        $db->exec('SET NAMES "utf8mb4"');
        $db->exec('SET CHARACTER SET utf8mb4');

        return $db;
    }

    protected function calculateStatementHash(string $statement, $driverOptions): string
    {
        if (is_array($driverOptions)) {
            ksort($driverOptions);
        }

        return md5(json_encode([$statement, $driverOptions]));
    }

    public function prepare(string $query, array $options = []): false|PDOStatement
    {
        $hash = $this->calculateStatementHash($query, $options);
        if (isset($this->stmtCache[$hash])) {
            return $this->stmtCache[$hash];
        }

        return $this->stmtCache[$hash] = parent::prepare($query, $options);
    }

    /**
     * Create and execute a prepared statement immediately
     */
    public function execute(string $statement, array $inputParameters = [], array $driverOptions = []): PDOStatement
    {
        $stmt = $this->prepare($statement, $driverOptions);
        $stmt->execute($inputParameters);

        return $stmt;
    }

    /**
     * Set the isolation level
     */
    public function setIsolationLevel(string $isolationLevel): bool
    {
        if (!in_array($isolationLevel, [
            self::ISOLATION_LEVEL_READ_UNCOMITTED,
            self::ISOLATION_LEVEL_READ_COMMITTED,
            self::ISOLATION_LEVEL_REPEATABLE_READ,
            self::ISOLATION_LEVEL_SERIALIZABLE,
        ])) {
            return false;
        }

        return $this->exec('SET TRANSACTION ISOLATION LEVEL ' . $isolationLevel) !== false;
    }

    protected function buildQuery(string $query, ?array $values = null, ?array $where = null): PDOStatement
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

                if ($field instanceof DirectValue) {
                    $field = $field->getValue();
                } else {
                    $field = sprintf('`%s`', $field);
                }

                if ($value instanceof DirectValue) {
                    $columns[] = sprintf('%s %s %s', $field, $operator, $value->getValue());
                } else {
                    $key = 'value_' . $i++;
                    $columns[] = sprintf('%s %s :%s', $field, $operator, $key);
                    $queryData[$key] = $value;
                }
            }
            $query .= ' SET ' . implode(', ', $columns);
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
                    $key = 'where_' . $i++;
                    $columns[] = sprintf('`%s` %s :%s', $field, $operator, $key);
                    $queryData[$key] = $value;
                }
            }
            $query .= ' WHERE ' . implode(' AND ', $columns);
        }

        return $this->execute($query, $queryData);
    }

    /**
     * Let the database create a UUID
     */
    public function createUuid(): string
    {
        $stmt = $this->execute('SELECT UUID()');
        [$uuid] = $stmt->fetch(Mysql::FETCH_NUM);

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
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function update(string $table, array $values, array $where, bool $allowEmptyWhere = false): PDOStatement
    {
        if (!$allowEmptyWhere && empty($where)) {
            throw new DatabaseException('Empty where statements are not allowed!');
        }

        return $this->buildQuery(sprintf('UPDATE `%s`', $table), $values, $where);
    }

    /**
     * Insert a new row
     *
     * @param string $table
     * @param array  $values key=>value
     *
     * @return PDOStatement
     */
    public function insert(string $table, array $values): PDOStatement
    {
        return $this->buildQuery(sprintf('INSERT INTO `%s`', $table), $values);
    }

    /**
     * Select from database
     *
     * @param string $table
     * @param array  $where
     *
     * @return PDOStatement
     */
    public function select(string $table, array $where): PDOStatement
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
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function delete(string $table, array $where, bool $allowEmptyWhere = false): PDOStatement
    {
        if (!$allowEmptyWhere && empty($where)) {
            throw new DatabaseException('Empty where statements are not allowed!');
        }

        return $this->buildQuery(sprintf('DELETE FROM `%s`', $table), null, $where);
    }
}
