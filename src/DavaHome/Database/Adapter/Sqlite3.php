<?php
declare(strict_types=1);

namespace DavaHome\Database\Adapter;

use DavaHome\Database\DatabaseException;
use SQLite3Result;

class Sqlite3 extends \SQLite3 implements AdapterInterface
{
    /**
     * @param string $query
     * @param array  $params
     *
     * @return SQLite3Result
     */
    public function execute(string $query, array $params = []): SQLite3Result
    {
        $stmt = $this->prepare($query);
        foreach ($params as $name => $value) {
            $stmt->bindParam($name, $value);
        }

        return $stmt->execute();
    }

    /**
     * @param string $table
     * @param array  $values
     * @param array  $where
     * @param bool   $allowEmptyWhere
     *
     * @return mixed|SQLite3Result
     * @throws DatabaseException
     */
    public function update(string $table, array $values, array $where, bool $allowEmptyWhere = false): SQLite3Result
    {
        $v = 0;
        $params = [];

        if (!$allowEmptyWhere && empty($where)) {
            throw new DatabaseException('Empty where statements are not allowed!');
        }

        // SET
        $valuesQuery = [];
        foreach ($values as $key => $value) {
            $valuesQuery[] = sprintf('`%s` = :value_' . $v, $key);
            $params['value_' . $v] = $value;
            $v++;
        }

        // WHERE
        $whereQuery = [];
        foreach ($where as $key => $value) {
            $whereQuery[] = sprintf('`%s` = :value_' . $v, $key);
            $params['value_' . $v] = $value;
            $v++;
        }

        $query = 'UPDATE `%s` SET ' . implode(', ', $valuesQuery);
        if (!empty($whereQuery)) {
            $query .= ' WHERE ' . implode(' AND ', $whereQuery);
        }

        return $this->execute($query);
    }

    public function insert(string $table, array $values)
    {
        throw new DatabaseException(__METHOD__ . ' is not implemented yet');
    }

    public function select(string $table, array $where)
    {
        throw new DatabaseException(__METHOD__ . ' is not implemented yet');
    }

    public function delete(string $table, array $where, bool $allowEmptyWhere = false)
    {
        throw new DatabaseException(__METHOD__ . ' is not implemented yet');
    }
}
