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
use Porm\core\Core;
use Porm\Porm;

/**
 * This should not be worked with directly, use the Porm class instead.
 */
class PormObject
{
    /**
     * The database table to use
     * @var mixed
     */
    private ?string $table;
    /**
     * @var string|null The alias to use, will defualt to the table name provided.
     */
    private ?string $alias;
    /**
     * @var Core|null The database connection to use
     */
    private ?Core $connection = null;

    /**
     * @var array|null The join clause Builder
     */
    private ?array $join = null;
    /**
     * @var mixed The where clause Builder
     */
    private ?string $using;

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
        $this->alias = $alias ?? null;

        $this->table = $this->alias ? $table . ' (' . $this->alias . ')' : $table;

        if (!$this->connection) {
            $this->boot();
        }

    }

    /**
     * This sets the table to use
     *
     * @param string $table The table to use
     * @param string|null $alias The alias to use
     * @param string|null $using The connection to use
     *
     * @throws Exception
     * @example ```php
     *     Table::from('user') // notice this here
     *       ->get(['last_name' => 'Pionia']);
     * ```
     *
     */
    public static function from($table, ?string $alias = null, ?string $using = null): Porm
    {
        return new Porm($table, $alias, $using);
    }


    public function getConnection(): ?Core
    {
        return $this->connection;
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
        $this->connection->action(function ($database) use ($callback) {
            $this->connection = $database;
            return $callback($this);
        });
    }

    /**
     * Returns the details of the current db connection
     * @return array
     */
    public function info(): array
    {
        return $this->connection->info();
    }
}
