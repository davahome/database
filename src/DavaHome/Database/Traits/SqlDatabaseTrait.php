<?php

namespace DavaHome\Database\Traits;

trait SqlDatabaseTrait
{
    /**
     * @param string $query
     * @param array  $values
     * @param array  $where
     *
     * @return mixed
     */
    abstract protected function buildQuery($query, array $values = null, array $where = null);

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
