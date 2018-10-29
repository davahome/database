<?php

namespace DavaHome\Database;

class Sqlite3 extends \SQLite3 implements DatabaseInterface
{
    /**
     * @param string $query
     * @param array  $params
     *
     * @return \SQLite3Result
     */
    public function execute($query, array $params = [])
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
     * @return mixed|\SQLite3Result
     * @throws \Exception
     */
    public function update($table, array $values, array $where, $allowEmptyWhere = false)
    {
        $v = 0;
        $params = [];

        if (!$allowEmptyWhere && empty($where)) {
            throw new \Exception('Empty where statements are not allowed!');
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

    public function insert($table, array $values)
    {
        throw new \Exception(__METHOD__ . ' is not implemented yet');
    }

    public function select($table, array $where)
    {
        throw new \Exception(__METHOD__ . ' is not implemented yet');
    }

    public function delete($table, array $where, $allowEmptyWhere = false)
    {
        throw new \Exception(__METHOD__ . ' is not implemented yet');
    }
}
