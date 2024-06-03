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

namespace Porm\core;

use Exception;
use PDO;
use Porm\exceptions\BaseDatabaseException;

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
    public ?PDO $pdo = null;

    /**
     * This is the database connection to use, default is db
     * @var string
     */
    public string $using = 'db';

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
     * Resolves the database connection to use. If the connection is passed, it will use that connection. If the options are passed, it will use those options. If the pdo is passed, it will use that pdo.
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
    public static function builder(?string $databaseConnection = null, ?array $options = null, ?PDO $pdo = null): Core
    {
        $current = new Database(null, $options, $pdo);
        $current->database = new Core($current->options);
        return $current->database;
    }

    /**
     * This is a static method to use a database connection. It will return a new Database instance with the connection passed.
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
