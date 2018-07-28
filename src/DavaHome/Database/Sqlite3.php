<?php

namespace DavaHome\Database;

/**
 * @method \SQLite3Result update($table, array $values, array $where, $allowEmptyWhere = false)
 * @method \SQLite3Result insert($table, array $values)
 * @method \SQLite3Result select($table, array $where)
 * @method \SQLite3Result delete($table, array $where, $allowEmptyWhere = false)
 */
class Sqlite3 extends \SQLite3 implements DatabaseInterface
{
    /**
     * @param string $query
     * @param array  $values
     * @param array  $where
     *
     * @return \SQLite3Result
     */
    protected function buildQuery($query, array $values = null, array $where = null)
    {
        // TODO: implement

        return $this->query($query);
    }
}
