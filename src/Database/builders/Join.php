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

namespace Porm\Database\builders;

use Porm\Core\ContractBuilder;
use Porm\Core\Core;
use Porm\Database\utils\FilterTrait;
use Porm\Database\utils\JoinParseTrait;

class Join extends ContractBuilder
{
    private string $table;

    private Core $database;

    private bool $preventLimit = false;

    private array $where;
    /**
     * @var mixed|string
     */
    private string|array $columns;

    private array $joins = [];

    use JoinParseTrait;
    use FilterTrait;

    public function __construct($table, Core $database, $columns = "*", $where = [])
    {
        $this->table = $table;
        $this->database = $database;
        $this->columns = $columns;
        $this->where = $where;
    }

    /**
     * Returns the medoo-like join array that was generated after chaining multiple joins
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    public function build(): Join
    {
        return $this;
    }
}