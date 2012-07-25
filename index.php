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

require_once dirname(__FILE__) . '/php/bootloader.php';

if (!defined('DB_NAME')) {

    $site = new ToroApplication(array(
                array(".*", "DBSetupHandler")
            ));
    $site->serve();
} else {
    if (!Auth::isAdmin() && $env->is_under_construction) {
        tpl_under_construction();
        exit;
    }
    $admin_pages = array(
        array("preferences", "PreferencesHandler")
    );
    $moderator_pages = array(
        array("usermanagement", "UserManagementHandler"),
        array('admin', 'AdminHandler'),
        array('teacherlist', 'TeacherListHandler'),
        array('uc_management', 'UserCharacteristicsManagementHandler'),
        array('up_management', 'UserPollsManagementHandler')
    );
    $normal_pages = array(
        array('(/|(login)|(register))?', 'MainHandler'),
        array('impress', 'ImpressHandler'),
        array('user(/.*)?', 'UserHandler'),
        array('images(/.*)?', 'ImagesHandler'),
        array('quotes(/.*)?', 'QuotesHandler'),
        array('rumors(/.*)?', 'RumorsHandler'),
        array('impress', 'ImpressHandler'),
        array('logout', 'LogoutHandler'),
        array('ajax(/.*)?', 'AjaxHandler'),
        array('terms_of_use', 'TermsOfUseHandler'),
    );
    if ($env->user_characteristics_editable) {
        $pages = array_unshift($normal_pages, array('user_characteristics', 'UserCharacteristicsHandler'));
    }
    if ($env->user_polls_open) {
        $pages = array_unshift($normal_pages, array('userpolls', 'UserPollsHandler'));
    }
    if ($env->stats_open || Auth::isModerator()) {
        $pages = array_unshift($normal_pages, array('stats', 'StatsHandler'));
    }
    //array_merge($env->stats_open ? $normal_pages : $admin_pages, array('stats(\/.*)?', 'StatsHandler'));
    $no_pages = array(
        array('register', 'RegisterHandler'),
        array('forgot_password(/.*)?', 'ForgotPasswordHandler'),
        array('impress', 'ImpressHandler'),
        array('terms_of_use', 'TermsOfUseHandler'),
        array('.*', 'LoginHandler'),
    );
    $pages = $normal_pages;
    switch (Auth::getUserMode()) {
        case User::ADMIN_MODE:
            $pages = array_merge($pages, $admin_pages);
        case User::MODERATOR_MODE:
            $pages = array_merge($pages, $moderator_pages);
    }
    if (Auth::getUserMode() != User::NO_MODE) {
        $site = new ToroApplication($pages);
    } else if (Auth::isNotActivated()) {
        $site = new ToroApplication('.*', 'WaitForActivationHandler');
    } else {
        $site = new ToroApplication($no_pages);
    }
    $site->serve();
    
    if ($store) {
        $store->updateDB();
    }
}
