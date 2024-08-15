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

declare(strict_types=1);

namespace Porm;

use Porm\Database\Builders\BaseBuilder;

/**
 * This should the preferred way to interact with the Database in the Pionia Framework. It is a wrapper around the Medoo library.
 *
 * @see BaseBuilder for the methods available to you.
 * @see Db for the underlying implementation.
 * @package Porm
 */
class Porm extends BaseBuilder
{
}
