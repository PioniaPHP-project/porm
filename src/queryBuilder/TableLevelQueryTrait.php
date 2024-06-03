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

use Exception;
use Medoo\Raw;
use PDOStatement;
use Porm\core\Core;
use Porm\core\Database;

trait TableLevelQueryTrait
{
    use AggregateTrait;

    /**
     * This checks if the table has a record that matches the where clause
     * @param string|array $where
     * @return bool
     *
     * @throws Exception
     * @example ```php
     *      $res1 = Table::from('users')->has(1); // with integer where clause that defaults to id = 1
     *      $res2 = Table::from('users')->has(['id' => 1]); // with array where clause
     * ```
     *
     */
    public function has(string|array $where): bool
    {
        $this->checkFilterMode("You cannot check if an item exists at this point in the query, check the usage of the `has()`
         method in the query builder for " . $this->table);
        if ($this->preventHas) {
            throw new Exception('You cannot call `has()` at this point in the query, check the usage of the `has()` method in the query builder for ' . $this->table);
        }
        if (is_string($where)) {
            $where = ['id' => $where];
        }
        return $this->connection->has($this->table, $where);
    }

    /**
     * Fetches random n items from the table, default to 1
     *
     * @example ```php
     *     $res1 = Table::from('users')->random(); // fetches a random user
     *     $res2 = Table::from('users')->random(5); // fetches 5 random users
     *     $res3 = Table::from('users')->random(5, ['last_name' => 'Pionia']); // fetches 5 random users with last name Pionia
     * ```
     * @param ?int $limit
     * @param array|null $where
     * @return array|mixed|object
     * @throws Exception
     */
    public function random(?int $limit = 1, ?array $where = null): mixed
    {
        $this->checkFilterMode("You cannot fetch random items at this point in the query, check the usage of the `random()`
         method in the query builder for " . $this->table);

        if ($where === null) {
            $where = [];
        }

        if (!isset($where['LIMIT'])) {
            $where['LIMIT'] = $limit;
        }

        $result = $this->connection->rand($this->table, $this->columns, $where);
        if ($limit === 1) {
            $this->resultSet = $this->resultSet[0];
            return $this->asObject();
        }
        $this->resultSet = $result;
        return $this->resultSet;
    }

    /**
     * Saves and returns the saved item as an object
     * @param array $data The data to save. Must be an associative array
     * @return object The saved object
     *
     * @throws Exception
     * @example ```php
     *    $res = Table::from('users')->save(['first_name' => 'John', 'last_name' => 'Doe']);
     *    echo $res->id;
     * ```
     *
     */
    public function save(array $data): object
    {
        $this->checkFilterMode("You cannot save at this point in the query, check the usage of the `save()`
         method in the query builder for " . $this->table);

        $this->database()->insert($this->table, $data);
        $id = $this->database()->id();
        return $this->get($id);
    }

    /**
     * This helps to query any type of join.
     * You can call it as many times as you want before calling filter.
     * @throws Exception
     */
    private function join(string $joinType, $joinTable, string|array $joinColumns, ?string $alias): static
    {
        $this->allowFilterOnly = true;

        if (in_array($joinType, [JoinTypes::INNER, JoinTypes::LEFT, JoinTypes::RIGHT, JoinTypes::FULL]) === false) {
            throw new Exception('Invalid join type');
        }

        if ($joinTable === $this->table && $alias === null) {
            throw new Exception('Cannot join a table to itself without an alias, please provide an alias for ' . $joinTable . ' table');
        }

        $result = match ($joinType) {
            JoinTypes::INNER => ["[<>]$joinTable"],
            JoinTypes::LEFT => "[<]$joinTable",
            JoinTypes::RIGHT => "[>]$joinTable",
            JoinTypes::FULL => "[><]$joinTable",
        };
        if ($alias) {
            $result .= "( $alias )";
        }
        $this->join = array_merge($this->join, [$result => $joinColumns]);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function asJson(): bool|string
    {
        $this->checkFilterMode("Resultset cannot be jsonified at this point in the query, check the usage of the `asJson()`
         method in the query builder for " . $this->table);
        return $this->resultSet ? json_encode($this->resultSet) : $this->resultSet;
    }

    /**
     * @throws Exception
     */
    public function asObject(): mixed
    {
        $this->checkFilterMode("Resultset cannot be objectified at this point in the query, check the usage of the `asObject()`
         method in the query builder for " . $this->table);
        if (is_array($this->resultSet)) {
            return (object)$this->resultSet;
        }
        return $this->resultSet;
    }


    /**
     * Fetches a single item from the database
     * @param int|array $where
     * @return object|array|null
     * @throws Exception
     * @example ```php
     *    $res1 = Table::from('users')->get(1); // fetches a user with id 1
     *    $res2 = Table::from('users')->get(['id' => 1]); // fetches a user with id 1
     *    $res3 = Table::from('users')->get(['last_name' => 'Pionia', 'first_name'=>'Framework']); // fetches a user with last name Pionia and first_name as Framework
     * ```
     */
    public function get(int|array $where): object|array|null
    {
        $this->checkFilterMode("You cannot call `get()` at this point in the query, check the usage of the `get()`
         method in the query builder for " . $this->table);

        if (is_int($where)) {
            $where = ['id' => $where];
        }
        $result = $this->connection->get($this->table, $this->columns, $where);
        $this->resultSet = $result;
        if ($this->resultSet) {
            return $this->asObject();
        }
        return $result;
    }

    /**
     * @param string $query The query to run
     * @param array $params The parameters to pass prepare along with the query
     * @throws Exception If we are in any other realm than RAW
     */
    public function raw(string $query, array $params): Raw
    {
        $this->checkFilterMode("You cannot run raw queries at this point in the query, 
        check the usage of the `raw()` method in the query builder for " . $this->table);
        return $this->connection::raw($query, $params);
    }

    /**
     * This switches the query to filter mode. It is useful for conditional querying.
     * @param array|null $where The where clause to use
     * @return Builder
     * @throws Exception
     * @example ```php
     *  $res1 = Table::from('users')->filter(['id' => 1])->get(); // fetches a user with id 1
     *  $res2 = Table::from('users')->filter(['last_name' => 'Pionia', 'first_name'=>'Framework'])->all(); // fetches all users with last name Pionia and first_name as Framework
     *  $res2 = Table::from('users')->filter(['last_name' => 'Pionia'])->limit(1)->startAt(2); // fetches a user with last name Pionia and first_name as Framework
     * ```
     */
    public function filter(?array $where = []): Builder
    {
        $this->allowFilterOnly = true;
        return new Builder($this->table, $this->connection, $this->join, $this->columns, $where);
    }

    /**
     * This defines the table column names to return from the database
     * @param string|array $columns The columns to select defaults to * for all.
     * @return PormObject The current Table object
     * @throws Exception
     *
     * @example ```php
     *   $res1 = Table::from('users')->columns('first_name')->get(1); // fetches the first name of the user with id 1
     *   $res2 = Table::from('users')->columns(['first_name', 'last_name'])->get(1); // fetches the first name and last name of the user with id 1
     *   $res3 = Table::from('users')->columns(['first_name', 'last_name'])->filter(['last_name' => 'Pionia'])->all(); // fetches the first name and last name of all users with last name Pionia
     * ```
     */
    public function columns(string|array $columns = "*"): static
    {
        $this->checkFilterMode("You cannot update the columns at this point in the query, 
        the columns method should be called much earlier. Check the usage of the `columns()` method in the query builder for " . $this->table);
        $this->preventRaw = true;
        $this->preventHas = true;
        $this->columns = $columns;
        return $this;
    }

    /**
     * This sets the connection to the database to use for the current query.
     * It can be used to switch between database connections.
     *
     * @param string $connection The connection to use, defaults to 'db'
     * @throws Exception
     */
    public function using(string $connection = 'db'): static
    {
        $this->checkFilterMode('When cannot change the db connection while at this point of the query, 
        check the usage of `using() method in the query builder of `' . $this->table);
        $this->using = $connection;
        return $this;
    }


    /**
     * This sets up the database connection to use internally. It is called when the Table class is being set up.
     * @throws Exception
     */
    private function database(): Core
    {
        if ($this->using) {
            $this->connection = Database::builder($this->using);
        }
        if ($this->connection) {
            return $this->connection;
        }
        $this->connection = Database::builder();
        return $this->connection;
    }

    /**
     * This deletes a single item from the database
     * @param int|array $where
     * @return PDOStatement|null
     * @throws Exception
     * @example ```php
     *   $res1 = Table::from('users')->delete(1); // deletes a user with id 1
     *   $res2 = Table::from('users')->delete(['name' => 'John']); // deletes a user with name John
     * ```
     *
     */
    public function deleteOne(int|array $where): ?PDOStatement
    {
        $this->checkFilterMode("You cannot delete at this point in the query, check the usage of the `delete()`
         method in the query builder for " . $this->table);

        if (is_int($where)) {
            $where = ['id' => $where];
        }

        $where['LIMIT'] = 1;

        return $this->connection->delete($this->table, $where);
    }

    /**
     * This deletes all items that match the where clause
     * @param array $where
     * @return PDOStatement|null
     * @throws Exception
     * @example ```php
     *  $res1 = Table::from('users')->deleteAll(['name' => 'John']); // deletes all users with name John
     *  $res2 = Table::from('users')->deleteAll(['last_name' => 'Pionia', 'first_name'=>'Framework']); // deletes all users with last name Pionia and first_name as Framework
     * ```
     */
    public function deleteAll(array $where): ?PDOStatement
    {
        $this->checkFilterMode("You cannot delete at this point in the query, check the usage of the `delete()`
         method in the query builder for " . $this->table);
        return $this->connection->delete($this->table, $where);
    }

    /**
     * This prevents the use of non-filtering methods in filter mode.
     *
     * Case here is like calling get() on join() yet join() return no resultset yet.
     * @param string $msg The message to throw
     *
     * This is primarily used internally for the purpose.
     * ```php
     * $this->checkFilterMode("You cannot delete at this point in the query, check the usage of the `delete()` method in the query builder for ".$this->table);
     * ```
     * @throws Exception
     */
    private function checkFilterMode($msg = 'Query is in filter mode, you cannot use this method in filter mode'): void
    {
        if ($this->allowFilterOnly) {
            throw new Exception($msg);
        }
    }
}
