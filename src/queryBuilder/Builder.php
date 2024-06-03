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

use Porm\core\Core;

class Builder
{
    private string $table;
    private Core $connection;
    private ?array $join;

    private bool $preventLimit = false;

    private array $where = [];
    /**
     * @var mixed|string
     */
    private string|array $columns = "*";

    use AggregateTrait;

    public function __construct($table, Core $database, ?array $join = null, $columns = "*", $where = [])
    {
        $this->table = $table;
        $this->connection = $database;
        $this->join = $join;
        $this->columns = $columns;
        $this->where = $where;
    }

    /**
     * Returns one item from the database
     * @param array|int|null $where
     * @return object
     *
     * @example ```php
     * $row = Table::from('user')
     *      ->filter(['last_name' => 'Ezra'])
     *      ->get();
     * ```
     */
    public function get(array|int|null $where = null): ?object
    {
        if (is_array($where)) {
            $this->where = array_merge($this->where, ['AND' => $where]);
        } elseif (is_int($where)) {
            $this->where = array_merge($this->where, ['LIMIT' => [$where, 1]]);
        }
        $result = $this->connection->get($this->table, $this->join, $this->columns, $this->where);
        return (object)$result;
    }

    /**
     * Same as get() but returns the first item of the resultset
     */
    public function first(): ?object
    {
        return $this->get(0);
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
        if ($callback) {
            return $this->connection->select($this->table, $this->join, $this->columns, $this->where, $callback);
        }
        return $this->connection->select($this->table, $this->join, $this->columns, $this->where);
    }

    public function where(array $where): static
    {
        $this->where = array_merge($this->where, $where);
        return $this;
    }

    public function match($columns, $keyword, $mode = 'natural'): static
    {
        $this->where['MATCH'] = ['columns' => $columns, 'keyword' => $keyword, 'mode' => $mode];
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function limit(int $limit): static
    {
        if ($this->preventLimit) {
            throw new \Exception('You cannot use limit more than once in the same query. You may think about using arrays for LIMITS instead');
        }
        $this->preventLimit = true;

        // we probably have a limit already set the limit from the offset
        if (isset($this->where['LIMIT'])) {
            // if the limit is an array, we will just set the limit part to the above
            if (is_array($this->where['LIMIT'])) {
                $limit_value = $this->where['LIMIT'][1];
                $offset_value = $this->where['LIMIT'][0];
                $this->where['LIMIT'] = [$offset_value, $limit_value];
                return $this;
            }
        }

        $this->where['LIMIT'] = $limit;
        return $this;
    }

    public function startAt(int $startPoint = 0): static
    {
        if (isset($this->where['LIMIT'])) {
            if (is_array($this->where['LIMIT'])) {
                $limit_value = $this->where['LIMIT'][1];
                $this->where['LIMIT'] = [$startPoint, $limit_value];
            } else {
                // if we have a limit set, we will set the limit to the start point
                $this->where['LIMIT'] = [$startPoint, $this->where['LIMIT']];
            }
        } else {
            // if we did not set a limit, we will set a limit of 100000000, which is a very large number to ensure that we get all the records
            $this->where['LIMIT'] = [$startPoint, 100000000];
        }
        return $this;
    }

    public function group(string|array $group)
    {
        $this->where['GROUP'] = $group;
        return $this;
    }

    public function having(array $having)
    {
        $this->where['HAVING'] = $having;
        return $this;
    }
}
