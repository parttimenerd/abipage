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

define('DEBUG', isset($_REQUEST["debug"]));
//define('DEBUG', true);
//define("UNMINIFIED_SOURCE", true);
define('SHOW_LOGS_TO_ADMIN', false);
define("BASE_DIR", __DIR__);
define("BEGIN_TIME", microtime(true));

require_once dirname(__FILE__) . '/php/bootloader.php';

if (!defined('DB_NAME')) {
    $site = new ToroApplication(array(
                array(".*", "DBSetupHandler")
            ));
    $site->serve();
} else {

    define("TITLE", $env->title);

    if (!Auth::canVisitSiteWhenUnderConstruction() && $env->is_under_construction) {
        if (isset($_POST["login"])) {
            $handler = new LoginHandler();
            $handler->get();
        } else {
            tpl_under_construction();
        }
        exit;
    }

    if (!Auth::canSeeDebugOutput() && !Auth::canViewLogs() && (!defined("DEBUG") || !DEBUG)) {
        error_reporting(0);
    }
    PageManager::serve();

    Auth::updateLastVisitTime();

    $store->updateDB();
}