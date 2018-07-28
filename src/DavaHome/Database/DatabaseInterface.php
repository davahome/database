<?php

namespace DavaHome\Database;

interface DatabaseInterface
{
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
    public function update($table, array $values, array $where, $allowEmptyWhere = false);

    /**
     * Insert a new row
     *
     * @param string $table
     * @param array  $values key=>value
     *
     * @return mixed
     */
    public function insert($table, array $values);

    /**
     * Select from database
     *
     * @param string $table
     * @param array  $where
     *
     * @return mixed
     */
    public function select($table, array $where);

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
    public function delete($table, array $where, $allowEmptyWhere = false);
}
