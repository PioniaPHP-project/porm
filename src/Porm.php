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

namespace Porm;

use Medoo\Medoo;
use Porm\queryBuilder\PormObject;

/**
 * This should the preferred way to interact with the database in the Pionia Framework. It is a wrapper around the Medoo library.
 *
 * @see PormObject for the methods available to you.
 * @see Medoo for the underlying implementation.
 * @package Porm
 */
class Porm extends PormObject
{
}
