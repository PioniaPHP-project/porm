<?php

/**
 * PORM - Database querying tool for pionia framework.
 *
 * This package can be used as is or with the Pionia Framework. Anyone can reproduce and update this as they see fit.
 *
 * @copyright 2024,  Pionia Project - Jet Ezra
 *
 * @author Jet Ezra
 * @version 1.0.0
 * @link https://pionia.netlify.app/
 * @license https://opensource.org/licenses/MIT
 *
 **/

namespace Porm\queryBuilder;

trait ParseTrait
{

    private function runSelect(?callable $callback): ?array
    {
        if (!$this->join) {
            $queryset = $this->connection->select($this->table, $this->columns, $this->where, $callback);
        } elseif (empty($this->where) && !$this->join) {
            $queryset = $this->connection->select($this->table, $this->columns, $callback);
        } else {
            $queryset = $this->connection->select($this->table, $this->join, $this->columns, $this->where, $callback);
        }
        return $queryset;
    }

    private function runGet(): ?array
    {
        if ($this->join) {
            return $this->connection->get($this->table, $this->join, $this->columns, $this->where);
        }

        return $this->connection->get($this->table, $this->columns, $this->where);
    }

    /**
     * Returns all items from the database. If a callback is passed, it will be called on each item in the resultset
     *
     * @example ```php
     * // Assignment method
     * $row = Table::from('user')
     *     ->filter(['last_name' => 'Ezra'])
     *    ->all();
     *
     * // Callback method- this is little bit faster than the assignment method
     * Table::from('user')
     *    ->filter(['last_name' => 'Ezra'])
     *   ->all(function($row) {
     *      echo $row->first_name;
     *  });
     * ```
     * @param callable|null $callback This is the receiver for the current resultset
     * @return array|null
     */
    public function all(?callable $callback = null): ?array
    {
        return $this->runSelect($callback);
    }
}
