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

namespace Porm\Database\Builders;

use Exception;
use Porm\Core\Core;
use Porm\Core\Database;
use Porm\Database\Aggregation\AggregateTrait;
use Porm\Database\Utils\TableLevelQueryTrait;
use Porm\Exceptions\BaseDatabaseException;
use Porm\Porm;

/**
 * This should not be worked with directly, use the Porm class instead.
 */
class PormObject extends Database
{
    public static string $pormVersion = "1.0.6";

    /**
     * The Database table to use
     * @var mixed
     */
    private ?string $table;
    /**
     * @var string|null The alias to use, will defualt to the table name provided.
     */
    private ?string $alias;
    /**
     * @var bool Lock out the use of filter
     */
    private bool $preventHas = false;
    /**
     * @var bool Lock out the use of any other method other than filter
     */
    private bool $allowFilterOnly = false;
    /**
     * @var mixed The result set to call asObject and asJson on.
     */
    private mixed $resultSet;

    /**
     * @var string|array|null The columns to select
     */
    private string|array|null $columns = '*';
    /**
     * @var true Lock out the use of raw queries
     */
    private bool $preventRaw = false;
    private array $where = [];

    use TableLevelQueryTrait;
    use AggregateTrait;

    public function __construct($table, ?string $alias = null, ?string $using = null)
    {
        $this->using = $using;

        parent::__construct($this->using);

        $this->alias = $alias;

        $this->table = $this->alias ? $table . ' (' . $this->alias . ')' : $table;

        // attempt to reconnect if the connection is lost
        if (!$this->database) {
            $this->reboot();
        }

    }

    public function logs()
    {
        return $this->database->log();
    }

    /**
     * This sets the table to use
     *
     * @param string $table The table to use
     * @param string|null $alias The alias to use
     * @param string|null $using The connection to use
     *
     * @throws BaseDatabaseException
     * @example ```php
     *     Table::from('user') // notice this here
     *       ->get(['last_name' => 'Pionia']);
     * ```
     *
     */
    public static function from(string $table, ?string $alias = null, ?string $using = null): Porm
    {
        try {
            return new Porm($table, $alias, $using);
        } catch (Exception $e) {
            throw new BaseDatabaseException($e->getMessage());
        }
    }

    /**
     * This is for running queries. Should be called first
     *
     * @param string $table
     * @param string|null $alias
     * @param string|null $using
     * @return Porm
     * @throws BaseDatabaseException
     * @since v1.0.2 You can this method instead of from(), but this is more readable
     * @see from()
     */
    public static function table(string $table, ?string $alias = null, ?string $using = null): Porm
    {
        return self::from($table, $alias, $using);
    }


    public function getDatabase(): ?Core
    {
        return $this->database;
    }

    /**
     * This assists to perform raw sql queries
     * @throws Exception
     */
    public static function rawQuery(string $query, ?array $params = [], ?string $using = 'db'): mixed
    {
        $instance = self::from('', '', $using);
        $queryable = $instance->raw($query, $params);
        $results = $instance->database->query($queryable->value, $queryable->map)->fetchAll();

        if (count($results) === 1) {
            $instance->resultSet = $results[0];
            return $instance->asObject();
        }
        $instance->resultSet = $results;
        return $instance->resultSet;
    }


    /**
     * Using transactions. This is a wrapper for the action method in the Core class.
     *
     * To access data outside the transaction, Create a result variable and refer to the transaction callback with the keyword `use`, and you can get data back after when you assign it from inside.
     * @example ```php
     *      $row = null;
     *      Table::from('qa_criteria')
     *            ->inTransaction(function (Table $instance) use (&$row) {
     *                  $row = $instance->save([
     *                      'name' => 'Status 3',
     *                      'description' => 'Must be single 4',
     *                      'best_of_total' => 6,
     *                  ]);
     *              });
     *
     *      var_dump($row);
     * ```
     * @param callable $callback The callback to run. It should return a void.
     * @throws Exception
     */
    public function inTransaction(callable $callback): void
    {
        $this->database->action(function ($database) use ($callback) {
            $this->database = $database;
            return $callback($this);
        });
    }

    /**
     * Returns the details of the current db connection
     * @return array
     */
    public function info(): array
    {
        return $this->database->info();
    }
}
