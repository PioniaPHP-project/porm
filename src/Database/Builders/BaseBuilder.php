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
use Porm\Core\Database;
use Porm\Database\Aggregation\AggregateTrait;
use Porm\Database\Utils\TableLevelQueryTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Provides a basis for other query builders to base on.
 *
 * @property Database $database The Database object to use
 */
class BaseBuilder
{
    /**
     * The version of the Porm package
     * @var string
     */
    public const PORM_VERSION = "1.0.9";

    private static ?BaseBuilder $instance = null;

    /**
     * @var string|null The database file to use
     */
    private ?string $dbFile = 'settings.ini';

    /**
     * The Database object to use
     * @var mixed
     */
    public Database $database;

    /**
     * The Database table to use. This is for interoperability with other versions of Porm
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

    /**
     * The container to use
     */
    private ContainerInterface|Database|null|string $connection = null;

    /**
     * @var string|null The container key to look for. This is for containerised environments
     */
    private ?string $containerkey;


    public function __construct(ContainerInterface|Database|string|array|null $connection = 'db', ?string $containDbKey = 'database', ?string $dbFile = null)
    {
        $this->connection = $connection;
        $this->containerkey = $containDbKey;
        if ($dbFile) {
            $this->dbFile = $dbFile;
        }
        $this->setup($connection);
    }

    /**
     * Sets up the database connection
     * @param mixed $connection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    protected function setup(mixed $connection): void
    {
        // if the connection came as a string, we will use that as the connection,
        // probably its a section in the settings.ini file
        if (is_string($connection)) {
            $this->database = Database::builder($connection, null, null, $this->dbFile);
        } else if ($connection instanceof \PDO) {
            // if the connection is a PDO object, we will use that as the connection
            $this->database = Database::builder(null, null, $connection, $this->dbFile);
        } else {
            // if the connection is an array, we will pass them along as options to the base Db object
            if (is_array($connection)) {
                $this->database = Database::builder(null, $connection, null, $this->dbFile);
            } else {
                // if the connection is a Database object, we will use that as the connection,
                if ($connection instanceof Database) {
                    $this->database = $connection;
                } else if ($connection instanceof ContainerInterface && $this->containerkey) {
                    // if the connection is a container, we will read the connection from the container
                    $this->database = $connection->get($this->containerkey);
                } else {
                    // if the connection is null, we will use the default connection
                    $this->database = Database::builder('db', null, null, $this->dbFile);
                }
            }
        }
    }

    /**
     * Return all executed queries.
     *
     * This function will only return all records if set `logging => true` on initialization, otherwise it will only return one last record by default.
     * @return array The columns to select
     */
    public function logs(): array
    {
        return $this->database->log();
    }

    /**
     * Logs the last query that was run
     *
     * @return string|null The last query that was run
     */
    public function logLastQuery(): ?string
    {
        return $this->database->last();
    }

    /**
     * This sets the table to use
     *
     * @param string $table The table to use
     * @param string|null $alias The alias to use
     * @param string|Database|array|null $using The connection to use, if null, it will use the default connection
     *
     * @return BaseBuilder
     * @example ```php
     *     Table::from('user') // notice this here
     *       ->get(['last_name' => 'Pionia']);
     * ```
     */
    public static function from(string $table, ?string $alias = null, string|null|Database|array $using = null): BaseBuilder
    {
        $obj = new static($using);
        $obj->alias = $alias;
        $obj->table = $obj->alias ? $table . ' (' . $obj->alias . ')' : $table;
        return $obj;
    }

    /**
     * This is for running queries in a containerised environment.
     * This should be the preferred in frameworks like Pionia
     *
     * @param string $table
     * @param string|null $alias
     * @param string|null $using
     * @return BaseBuilder
     * @since v1.0.9 This method was updated to handle containerised environments only and should be used in frameworks like Pionia
     * @see from() if you are not using a container
     */
    public function table(string $table, ?string $alias = null, ?string $using = null): BaseBuilder
    {
        $this->alias = $alias;
        $this->table = $this->alias ? $table . ' (' . $this->alias . ')' : $table;
        if ($using) {
            $this->setup($using);
        } else {
            $this->setup($this->connection);
        }
        return $this;
    }


    public function getDatabase(): ?Database
    {
        return $this->database;
    }

    /**
     * This assists to perform raw sql queries
     * @throws Exception
     */
    public static function rawQuery(string $query, ?array $params = [], ?string $using = 'db'): mixed
    {
        $instance = (new BaseBuilder)->table('dummy', 'dummy', $using);
        $queryable = $instance->raw($query, $params);
        $results = $instance->database->query($queryable->value, $queryable->map)->fetchAll();
        if (count($results) === 1) {
            $instance->resultSet = $results[0];
            return $instance->asObject();
        }
        return $results;
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
