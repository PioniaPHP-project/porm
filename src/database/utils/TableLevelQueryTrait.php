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

namespace Porm\database\utils;

use Exception;
use Medoo\Raw;
use PDOStatement;
use Porm\core\Core;
use Porm\core\Database;
use Porm\database\aggregation\AggregateTrait;
use Porm\database\builders\Builder;
use Porm\database\builders\PormObject;

trait TableLevelQueryTrait
{
    use AggregateTrait;
    use ParseTrait;

    /**
     * This checks if the table has a record that matches the where clause
     * @param string|array $where
     * @return bool
     *
     * @throws Exception
     * @example ```php
     *      $res1 = Porm::from('users')->has(1); // with integer where clause that defaults to id = 1
     *      $res2 = Porm::from('users')->has(['id' => 1]); // with array where clause
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
        return $this->database->has($this->table, $where);
    }

    /**
     * Fetches random n items from the table, default to 1
     *
     * @example ```php
     *     $res1 = Porm::from('users')->random(); // fetches a random user
     *     $res2 = Porm::from('users')->random(5); // fetches 5 random users
     *     $res3 = Porm::from('users')->random(5, ['last_name' => 'Pionia']); // fetches 5 random users with last name Pionia
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

        $this->where = array_merge($this->where, $where);
        var_dump($this->where);
        $result = $this->database->rand($this->table, $this->columns, $this->where);
        if ($result) {
            $this->resultSet = $result;
            if ($limit === 1 || !$limit) {
                $this->resultSet = $this->resultSet[0];
                return $this->asObject();
            }
            $this->resultSet = $result;
        }

        return $result;
    }

    /**
     * Saves and returns the saved item as an object
     * @param array $data The data to save. Must be an associative array
     * @return object The saved object
     *
     * @throws Exception
     * @example ```php
     *    $res = Porm::from('users')->save(['first_name' => 'John', 'last_name' => 'Doe']);
     *    echo $res->id;
     * ```
     *
     */
    public function save(array $data): object
    {
        $this->checkFilterMode("You cannot save at this point in the query, check the usage of the `save()`
         method in the query builder for " . $this->table);

        $this->reboot()->insert($this->table, $data);
        $id = $this->reboot()->id();
        return $this->get($id);
    }

    public function update(array $data, array|int|string $where, ?string $idField = 'id')
    {
        $this->checkFilterMode("You cannot update at this point in the query, check the usage of the `update()`
         method in the query builder for " . $this->table);
        if (is_int($where) || is_string($where)) {
            $where = [$idField => $where];
        }
        $this->where['AND'] = $where;
        return $this->database->update($this->table, $data, $this->where);
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
     *
     *
     *
     * @param int|array $where
     * @param string|null $idField defaults to id, pass this if you want to use a different field as the id other than id
     * @return object|array|null
     * @throws Exception
     * @example ```php
     *    $res1 = Porm::from('users')->get(1); // fetches a user with id 1
     *    $res2 = Porm::from('users')->get(['id' => 1]); // fetches a user with id 1
     *    $res3 = Porm::from('users')->get(['last_name' => 'Pionia', 'first_name'=>'Framework']); // fetches a user with last name Pionia and first_name as Framework
     * ```
     */
    public function get(int|array|string $where = null, ?string $idField = 'id'): object|array|null
    {
        $this->checkFilterMode("You cannot call `get()` at this point in the query, check the usage of the `get()`
         method in the query builder for " . $this->table);

        if (is_int($where) || is_string($where)) {
            $where = [$idField => $where];
        }
        $this->where = array_merge($this->where, ['AND' => $where]);
        $result = $this->runGet();
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
        return $this->database::raw($query, $params);
    }

    /**
     * This switches the query to filter mode. It is useful for conditional querying.
     * @param array|null $where The where clause to use
     * @return Builder
     * @throws Exception
     * @example ```php
     *  $res1 = Porm::from('users')->filter(['id' => 1])->get(); // fetches a user with id 1
     *  $res2 = Porm::from('users')->filter(['last_name' => 'Pionia', 'first_name'=>'Framework'])->all(); // fetches all users with last name Pionia and first_name as Framework
     *  $res2 = Porm::from('users')->filter(['last_name' => 'Pionia'])->limit(1)->startAt(2); // fetches a user with last name Pionia and first_name as Framework
     * ```
     */
    public function filter(?array $where = []): Builder
    {
        $this->allowFilterOnly = true;
        if ($where) {
            $this->where = array_merge($this->where, $where);
        }
        return Builder::builder($this->table, $this->database, $this->columns, $this->where)
            ->build();
    }

    /**
     * This defines the table column names to return from the database
     * @param string|array $columns The columns to select defaults to * for all.
     * @return PormObject The current Porm object
     * @throws Exception
     *
     * @example ```php
     *   $res1 = Porm::from('users')->columns('first_name')->get(1); // fetches the first name of the user with id 1
     *   $res2 = Porm::from('users')->columns(['first_name', 'last_name'])->get(1); // fetches the first name and last name of the user with id 1
     *   $res3 = Porm::from('users')->columns(['first_name', 'last_name'])->filter(['last_name' => 'Pionia'])->all(); // fetches the first name and last name of all users with last name Pionia
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
     * This sets up the database connection to use internally. It is called when the Porm class is being set up.
     * @throws Exception
     */
    private function reboot(): Core
    {
        if ($this->database) {
            return $this->database;
        }
        if ($this->using) {
            $this->database = Database::builder($this->using);
        }
        $this->database = Database::builder();
        return $this->database;
    }

    /**
     * This deletes all items that match the where clause
     * @param array $where
     * @return PDOStatement|null
     * @throws Exception
     * @example ```php
     *  $res1 = Porm::from('users')->deleteAll(['name' => 'John']); // deletes all users with name John
     *  $res2 = Porm::from('users')->deleteAll(['last_name' => 'Pionia', 'first_name'=>'Framework']); // deletes all users with last name Pionia and first_name as Framework
     * ```
     */
    public function deleteAll(array $where): ?PDOStatement
    {
        $this->checkFilterMode("You cannot delete at this point in the query, check the usage of the `delete()`
         method in the query builder for " . $this->table);
        return $this->delete($where);
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

    /**
     * This is under the hood similar to deleteOne but it is more explicit
     * @param string|int $id
     * @param string|null $idField
     * @return PDOStatement|null
     * @throws Exception
     */
    public function deleteById(string|int $id, ?string $idField = 'id'): ?PDOStatement
    {
        return $this->delete($id, $idField);
    }
}
