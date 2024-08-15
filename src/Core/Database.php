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
use Porm\Exceptions\BaseDatabaseException;

/**
 */
class Database extends Db
{
    /**
     * These are the Medoo Object options to pass along
     * @var array
     */
    public array $options = [];

//    /**
//     * This is the PDO object you can pass along
//     * @var ?PDO
//     */
//    private ?PDO $pdo = null;

//    /**
//     * This is the Database connection to use, default is db
//     * @var ?string
//     */
//    public ?string $using = 'db';

//    /**
//     * This is the Core object to use
//     * @var ?Database
//     */
//    public ?Database $database = null;

//    /**
//     * This is the logging option to use
//     * @var bool
//     */
//    public bool $logging = false;

//    /**
//     * @throws Exception
//     */
//    public function __construct(?string $databaseConnection = null, ?array $options = null, ?PDO $pdo = null)
//    {
//        $this->_resolve($databaseConnection, $options, $pdo);
//    }

    /**
     * Resolves the Database connection to use. If the connection is passed, it will use that connection. If the options are passed, it will use those options. If the pdo is passed, it will use that pdo.
     * If all are passed, it will use the pdo connection and ignore the rest.
     * If none are passed, it will use the default connection which is 'db'.
     *
     * If extra options are passed, they will take precedence over the settings.ini file.
     * @param mixed $connection
     * @param mixed $options
     * @param mixed $pdo
     * @throws Exception
     */
    private static function configure(?string $connection = null, ?array $options = null, ?PDO $pdo = null): Database
    {
        $options = $options ?? [];
        // here we have a pdo object
        if ($pdo) {
            $options['pdo'] = $pdo;
            return $options;
        }

        if ($connection) {
            try {
                $options = array_merge($options, Utilities::getAllSettingsUnderSection($connection));
            } catch (Exception $e) {
                throw new BaseDatabaseException("Database connection not found in settings.ini");
            }
        }
        // check if we can log and start logging
        if (!isset($options['logging'])) {
            $options['logging'] = Utilities::canLog();
        }
        return new static($options);
    }

    /**
     * @param string|null $databaseConnection
     * @param mixed $options
     * @param PDO|null $pdo
     * @return Database
     * @throws Exception
     */
    public static function builder(?string $databaseConnection = null, ?array $options = null, ?PDO $pdo = null): Database
    {
        return self::configure($databaseConnection, $options, $pdo);
    }


    /**
     * Returns the id of the last inserted row
     * @return string|null
     */
    public function lastId(): ?string
    {
        return $this->id();
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
        return $this->pdo;
    }

    /**
     * This is a static method to use a Database connection. It will return a new Database instance with the connection passed.
     * @param string|null $databaseConnection
     * @return Database
     * @throws Exception
     */
    public static function use(?string $databaseConnection = 'db'): Database
    {
        return self::builder($databaseConnection);
    }

    public function __destruct()
    {
        $this->pdo = null;
        $this->options = [];
    }
}
