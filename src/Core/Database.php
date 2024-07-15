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

namespace Porm\Core;

use Exception;
use PDO;
use PDOStatement;
use Porm\Exceptions\BaseDatabaseException;

/**
 */
class Database
{
    /**
     * This is the Medoo Object to pass along
     * @var array
     */
    public array $options = [];

    /**
     * This is the PDO object you can pass along
     * @var ?PDO
     */
    private ?PDO $pdo = null;

    /**
     * This is the Database connection to use, default is db
     * @var ?string
     */
    public ?string $using = 'db';

    /**
     * This is the Core object to use
     * @var ?Core
     */
    public ?Core $database = null;

    /**
     * This is the logging option to use
     * @var bool
     */
    public bool $logging = false;

    /**
     * @throws Exception
     */
    public function __construct(?string $databaseConnection = null, ?array $options = null, ?PDO $pdo = null)
    {
        $this->_resolve($databaseConnection, $options, $pdo);
    }

    /**
     * Resolves the Database connection to use. If the connection is passed, it will use that connection. If the options are passed, it will use those options. If the pdo is passed, it will use that pdo.
     * If all are passed, it will use the pdo connection and ignore the rest.
     * If none are passed, it will use the default connection which is 'db'.
     *
     * If extra options are passed, they will take precedence over the settings.ini file.
     * @param mixed $connection
     * @param mixed $options
     * @param mixed $pdo
     * @return void
     * @throws Exception
     */
    private function _resolve(?string $connection = null, ?array $options = null, ?PDO $pdo = null): void
    {
        if ($connection) {
            $this->using = $connection;
        }

        if ($pdo) {
            $this->pdo = $pdo;
            $this->options['pdo'] = $this->pdo;
            return;
        }

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
        if ($this->using) {
            try {
                $this->options = array_merge(Utilities::getAllSettingsUnderSection($this->using), $this->options);
            } catch (Exception $e) {
                throw new BaseDatabaseException("Database connection not found in settings.ini");
            }
        }
        // check if we can log and start logging
        if (!isset($this->options['logging'])) {
            $this->options['logging'] = Utilities::canLog();
        }
    }

    /**
     * @param string|null $databaseConnection
     * @param mixed $options
     * @param PDO|null $pdo
     * @return Core
     * @throws Exception
     */
    protected static function builder(?string $databaseConnection = null, ?array $options = null, ?PDO $pdo = null): Core
    {
        $current = new Database(null, $options, $pdo);
        $current->database = new Core($current->options);
        return $current->database;
    }

    /**
     * @see https://medoo.in/api/create for more information on the creation of tables
     * @param string $table_name
     * @param array $columns
     * @param array $options
     * @return PDOStatement
     */
    public function createTable(string $table_name, array $columns, array $options): PDOStatement
    {
        return $this->database->create($table_name, $columns, $options);
    }

    /**
     * @see https://medoo.in/api/drop for more information on the dropping of tables
     * @param string $table_name
     * @return PDOStatement|null
     */
    public function drop(string $table_name): ?PDOStatement
    {
        return $this->database->drop($table_name);
    }

    /**
     * Gives you access to the underlying medoo object and pdo object
     * @example ```php
     * $data = $Database->query("SELECT email FROM account")->fetchAll();
     * var_dump($data);
     * ```
     * @param string $query
     * @param array $map
     * @return PDOStatement
     */
    public function query(string $query, array $map = []): PDOStatement
    {
        return $this->database->query($query, $map);
    }

    /**
     * @see https://medoo.in/api/quote for more information on quoting values
     * @param string $value
     * @return string
     */
    public function clean(string $value): string
    {
        return $this->database->quote($value);
    }

    /**
     * Returns the id of the last inserted row
     * @return string|null
     */
    public function lastId(): ?string
    {
        return $this->database->id();
    }

    /**
     * Returns the underlying pdo object
     * From this, you can do anything you want with the pdo object
     * @example ```php
     * $start = $this->pdo()->beginTransaction();
     * ```
     * @see https://medoo.in/api/pdo for more information on the pdo object
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->database->pdo;
    }

    /**
     * This is a static method to use a Database connection. It will return a new Database instance with the connection passed.
     * @param string|null $databaseConnection
     * @return Database
     * @throws Exception
     */
    public static function use(?string $databaseConnection = 'db'): Database
    {
        return new Database($databaseConnection);
    }

    public function __destruct()
    {
        $this->pdo = null;
        $this->database = null;
        $this->using = 'db';
        $this->options = [];
    }
}
