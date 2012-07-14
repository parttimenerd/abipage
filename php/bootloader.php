<?php

/*
 * Copyright (C) 2012 Johannes Bechberger
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined("PHP_DIR")) {
    define("PHP_DIR", dirname(__FILE__));
}

if (!defined('DB_NAME') && file_exists(dirname(__FILE__) . '/db_config.php')) {
    require(PHP_DIR . '/db_config.php');
}
require_once(PHP_DIR . '/libs/toro.php');

$filearr = array();

if (!function_exists('load')) {


    /**
     * Registriert eine Autoload-Methode, die benötigte Klassen dynamisch nachlÃ¤dt
     */
    function load($classname) {
        global $filearr;
        if (empty($filearr)) {
            $dir = opendir(PHP_DIR);
            while ($filestr = readdir($dir)) {
                $fpath = PHP_DIR . '/' . $filestr;
                $filearr[$filestr] = $fpath;
                if (is_dir($fpath) && $fpath != "." && $fpath != ".." && $filestr) {
                    $dir2 = opendir($fpath);
                    while ($filestr2 = readdir($dir2)) {
                        $filearr[$filestr2] = $fpath . '/' . $filestr2;
                    }
                }
            }
        }
        if (array_key_exists($classname . '.php', $filearr)) {
            require_once $filearr[$classname . '.php'];
        }
    }

    spl_autoload_register("load");
}

ob_start();

if (!function_exists('require_dir')) {
    if (defined('DB_NAME')) {
        global $db, $env;
        require_once('Database.php');
        $db = Database::getConnection();
        require_once('KeyValueStore.php');
        $store = new KeyValueStore();
        register_shutdown_function(function() {
                    if (isset($store)) {
                        $store->updateDB();
                    }
                });
        require_once('Environment.php');
        $env = new Environment();
    }

    function require_dir($dirpath) {
        foreach (scandir($dirpath) as $dir) {
            if ($dir != '.' && $dir != '..' && $dir != "htmlpurifier") {
                $path = $dirpath . '/' . $dir;
                if (is_dir($path)) {
                    require_dir($path);
                } else if ($path != __FILE__ && preg_match('/.*\.php/', $dir)) {
                    require_once $path;
                }
            }
        }
    }

}
require_dir(dirname(__FILE__));