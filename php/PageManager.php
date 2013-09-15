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

/**
 * Description of PageManager
 *
 * @author Johannes Bechberger
 */
class PageManager {

    const NOT_REACHABLE_UMODE = 121243;
    const DEFAULT_MIN_UMODE = User::NORMAL_MODE;
    const DEFAULT_MIN_OPEN_FOR_UMODE = User::NORMAL_MODE;
    const FOUR_O_THREE_CLASS = "FourothreeHandler";
    const FOUR_O_FOUR_CLASS = "FourofourHandler";

    private static $page_props = array(); //regexp, class, min_umode, open, open_for

    private static function load() {
        global $env;
        if (!empty(self::$page_props))
            return;
        self::$page_props = array(//regexp, class, [min_umode,] is_open, open_for
            self::NOT_REACHABLE_UMODE => array(
                "user_characteristics(/.*)?" => array("class" => "UserCharacteristicsHandler", "is_open" => $env->user_characteristics_editable),
                "polls(/.*)?" => array("class" => "PollsHandler", "is_open" => $env->user_polls_open)
            ),
            User::ADMIN_MODE => array(
                "preferences" => array("class" => "PreferencesHandler"),
                "user_connection_visu(/.*)?" => array("class" => "UserConnectionVisuHandler"),
                "namelist_csv" => array("class" => "NameListCSVHandler")
            ),
            User::MODERATOR_MODE => array(
                "usermanagement" => array("class" => "UserManagementHandler"),
                "admin" => array("class" => "AdminHandler"),
                "teacherlist" => array("class" => "TeacherListHandler"),
//                "uc_management" => array("class" => "UserCharacteristicsManagementHandler"),
//                "up_management" => array("class" => "UserPollsManagementHandler"),
                "stats" => array("class" => "StatsHandler", "is_open" => $env->stats_open),
                "deleted_items_list" => array("class" => "DeletedItemsListHandler")
            ),
            self::DEFAULT_MIN_UMODE => array(
                "user(/.*)?" => array("class" => "UserHandler"),
                "images(/.*)?" => array("class" => "ImagesHandler"),
                "quotes(/.*)?" => array("class" => "QuotesHandler"),
                "rumors(/.*)?" => array("class" => "RumorsHandler"),
                "logout" => array("class" => "LogoutHandler"),
                "ajax(/.*)?" => array("class" => "AjaxHandler"),
                "news(/.*)?" => array("class" => "NewsListHandler"),
                "actions(/.*)?" => array("class" => "ActionsHandler"),
            ),
            User::NO_MODE => array(
                "impress" => array("class" => "ImpressHandler"),
                "terms_of_use" => array("class" => "TermsOfUseHandler"),
                "privacy" => array("class" => "PrivacyPolicyHandler"),
                "register" => array("class" => "RegisterHandler", "is_open" => $env->registration_enabled),
                "forgot_password(/.*)?" => array("class" => "ForgotPasswordHandler"),
                "login" => array("class" => "LoginHandler"),
                "" => array("class" => "MainHandler")
            )
        );
    }

    public static function serve() {
        $arr = array();
        if (defined("DB_NAME")) {
            $user_mode = Auth::getUserMode();
            self::load();
            foreach (self::$page_props as $min_umode => $umode_arr) {
                foreach ($umode_arr as $regexp => $page_arr) {
                    $open_for = isset($page_arr["open_for"]) ? $page_arr["open_for"] : self::DEFAULT_MIN_OPEN_FOR_UMODE;
                    if ($min_umode > $user_mode && !(isset($page_arr["is_open"]) && $page_arr["is_open"] && $open_for <= $user_mode)) {
                        $arr[] = array($regexp, self::FOUR_O_THREE_CLASS);
                    } else {
                        $arr[] = array($regexp, $page_arr["class"]);
                    }
                }
            }
            if (Auth::isNotActivated()) {
                $arr[] = array('.*', 'WaitForActivationHandler');
            } else {
                $arr[] = array(".*", self::FOUR_O_FOUR_CLASS);
            }
        } else {
            $arr = array(".*", "DBSetupHandler");
        }
        $toro = new ToroApplication($arr);
        $toro->serve();
    }

}

?>
