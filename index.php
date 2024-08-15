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

if (!defined("BASEPATH")) {
    define('BASEPATH', __DIR__);
}

if (!defined('SETTINGS')) {
    define('SETTINGS', BASEPATH . '/settings.ini');
}

require_once './vendor/autoload.php';
