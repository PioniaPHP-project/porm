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

namespace Porm\database\aggregation;

use Porm\core\Core;

/**
 * Aggregate functions for the PORM library.
 *
 * These methods are used to perform aggregate functions on the database.
 * @link https://medoo.in/api/aggregate
 *
 * They can be used in the following way:
 * @example
 * ```php
 * $dt = Porm::from('qa_criteria')->get(Agg::gt('id', 1)); // get all records where id is greater than 1
 * var_dump($dt);
 *
 * $dt = Porm::from('qa_criteria')->get(Agg::avg('id')); // get the average of the id column
 *  var_dump($dt);
 * ```
 */
class Agg
{

    /**
     * Assign a random value to a column
     * @param string $columnName will be the alias of the result
     * @return array
     */
    public static function random(string $columnName): array
    {
        return [$columnName => Core::raw("RAND()")];
    }

    /**
     * Get the sum of a column and assign it to columnName
     * @param string $columName will be the alias of the result
     * @param string $column will be the column to get the minimum value from
     * @return array
     */
    public static function sum(string $columName, string $column): array
    {
        return [$columName => Core::raw("SUM(<$column>)")];
    }

    /**
     * Get the average value of a column and assing it to columnName
     * @param string $columnName will be the alias of the result
     * @param string $column will be the column to get the minimum value from
     * @return array
     */
    public static function avg(string $columName, $column): array
    {
        return [$columName => Core::raw("AVG(<$column>)")];
    }

    /**
     * Get the maximum value of a column
     * @param string $columnName will be the alias of the result
     * @param string $column will be the column to get the maximum value from
     * @return array
     */
    public static function max(string $columnName, string $column): array
    {
        return [$columnName => Core::raw("MAX(<$column>)")];
    }

    /**
     * Assign current timestamp to a column
     *
     * @param string $columName The column to assign the current timestamp to
     * @return array
     * @example Agg::now('created_at') // create_at = NOW();
     */
    public static function now(string $columName): array
    {
        return [$columName => Core::raw("NOW()")];
    }

    /**
     * Assign a UUID to a column
     *
     * @param string $columnName The column to assign the UUID to
     * @return array
     * @example Agg::uuid('id') // id = UUID();
     */
    public static function uuid(string $columnName): array
    {
        return [$columnName => Core::raw("UUID()")];
    }


    /**
     * Less than.
     * @param string $columnName
     * @param int $value
     * @return array
     */
    public static function lt(string $columnName, int $value): array
    {
        return [$columnName . "[<]" => $value];
    }

    /**
     * Less than or equal to.
     * @param string $columnName
     * @param int $value
     * @return array
     */
    public static function lte(string $columnName, int $value): array
    {
        return [$columnName . "[<=]" => $value];
    }

    /**
     * Greater than.
     * @param string $columnName
     * @param int $value
     * @return array
     */
    public static function gt(string $columnName, int $value): array
    {
        return [$columnName . "[>]" => $value];
    }

    /**
     * Greater than or equal to.
     * @param string $columnName
     * @param int $value
     * @return array
     */
    public static function gte(string $columnName, mixed $value): array
    {
        return [$columnName . "[>=]" => $value];
    }

    /**
     * Equal to.
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function eq(string $columnName, mixed $value): array
    {
        return [$columnName => $value];
    }

    /**
     * Not equal to.
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function neq(string $columnName, mixed $value): array
    {
        return [$columnName . "[!]" => $value];
    }

    /**
     * add to the column value
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function plus(string $columnName, int $value): array
    {
        return [$columnName . "[+]" => $value];
    }

    /**
     * subtract from the column value
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function minus(string $columnName, int $value): array
    {
        return [$columnName . "[-]" => $value];
    }

    /**
     * multiply the column value
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function of(string $columnName, int $value): array
    {
        return [$columnName . "[*]" => $value];
    }

    /**
     * json encode the column value and assign it to the column
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function jsonified(string $columnName, array $value): array
    {
        return [$columnName . "[JSON]" => $value];
    }

    /**
     * divide the column value
     * @param string $columnName
     * @param mixed $value
     * @return array
     */
    public static function div(string $columnName, int $value): array
    {
        return [$columnName . "[/]" => $value];
    }
}
