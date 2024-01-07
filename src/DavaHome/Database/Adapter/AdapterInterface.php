<?php
declare(strict_types=1);

namespace DavaHome\Database\Adapter;

use Exception;

interface AdapterInterface
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
     * @throws Exception
     */
    public function update(string $table, array $values, array $where, bool $allowEmptyWhere = false): mixed;

    /**
     * Insert a new row
     *
     * @param string $table
     * @param array  $values key=>value
     *
     * @return mixed
     */
    public function insert(string $table, array $values): mixed;

    /**
     * Select from database
     *
     * @param string $table
     * @param array  $where
     *
     * @return mixed
     */
    public function select(string $table, array $where): mixed;

    /**
     * Delete a from database
     *
     * @param string $table
     * @param array  $where
     * @param bool   $allowEmptyWhere
     *
     * @return mixed
     * @throws Exception
     */
    public function delete(string $table, array $where, bool $allowEmptyWhere = false): mixed;
}
