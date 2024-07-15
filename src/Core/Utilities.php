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

class Utilities
{
    public static function getSettings(): array
    {
        if (!defined('SETTINGS')) {
            return [];
        }

        return parse_ini_file(SETTINGS, true);
    }

    public static function getAllSettingsUnderSection($section)
    {
        $config = self::getSettings();

        if (!isset($config[$section])) {
            return [];
        }
        return $config[$section];
    }

    public static function getSetting($section, $key)
    {
        $sector = self::getAllSettingsUnderSection($section);
        if (!isset($sector[$key])) {
            return null;
        }

        return $sector[$key];
    }

    public static function canLog(): bool
    {
        $sector = self::getAllSettingsUnderSection('SERVER');
        if ($sector) {
            if (isset($sector['DEBUG']) && $sector['DEBUG']) {
                return true;
            } elseif (isset($sector['LOG_REQUESTS']) && $sector['LOG_REQUESTS']) {
                return true;
            } else {
                $new_sector = self::getAllSettingsUnderSection('DB') ?? self::getAllSettingsUnderSection('db');
                return $new_sector['LOGGING'] ?? $new_sector['logging'] ?? false;
            }
        }
        return true;
    }


}
