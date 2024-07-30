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

namespace Porm\Database\Aggregation;

trait AggregateTrait
{
    /**
     * @param string|null $column
     * @param array|null $where
     * @return int|null
     * @see Core::count()
     */
    public function count(?string $column = null, ?array $where = null): ?int
    {
        if (is_array($where)) {
            $this->where = array_merge($this->where, $where);
        }
        return $this->database->count($this->table, $column, $this->where);
    }

    /**
     * @param string $column
     * @param array|null $where
     * @return string|null
     * @see Core::sum()
     */
    public function sum(string $column, ?array $where): ?string
    {
        $this->where = array_merge($this->where, $where);
        return $this->database->sum($this->table, $column, $this->where);
    }

    /**
     * @param string $column
     * @param array|null $where
     * @return string|null
     * @see Core::avg()
     */
    public function avg(string $column, ?array $where): ?string
    {
        $this->where = array_merge($this->where, $where);
        return $this->database->avg($this->table, $column, $this->where);
    }

    /**
     * @param string $column
     * @param array|null $where
     * @return string|null
     * @see Core::max()
     */
    public function max(string $column, ?array $where): ?string
    {
        $this->where = array_merge($this->where, $where);
        return $this->database->max($this->table, $column, $this->where);
    }

    /**
     * @param string $column
     * @param array|null $where
     * @return string|null
     * @see Core::min()
     */
    public function min(string $column, ?array $where): ?string
    {
        $this->where = array_merge($this->where, $where);
        return $this->database->min($this->table, $column, $this->where);
    }
}
