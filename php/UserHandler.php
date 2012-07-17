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

class UserHandler extends ToroHandler {

    public function get($slug = "") {
        global $env;
        $arr = explode('/', substr($slug, 1));
        $user = $this->getUserFromSlug($slug);
        if (!$user) {
            tpl_userlist($env->getUserNames());
        } else if (count($arr) == 2 && $arr[1] == "preferences" && ($user->getID() == Auth::getUserID() || Auth::isAdmin())) {
            tpl_user_prefs($user);
        } else {
            if ($env->user_page_open) {
                tpl_user_page($user);
            } else {
                tpl_user($user);
            }
        }
    }

    private function getUserFromSlug($slug) {
        if ($slug != "" && $slug != '/' && $slug != "all") {
            $arr = explode('/', substr($slug, 1));
            $slug = $arr[0];
            if ($slug == "me") {
                return Auth::getUser();
            } else if (is_int($slug)) {
                return User::getByID(intval($slug));
            } else {
                return User::getByName(str_replace("_", " ", $slug));
            }
        }
        return null;
    }

    public function post($slug) {
        $arr = explode('/', substr($slug, 1));
        $user = $this->getUserFromSlug($slug);
        if (count($arr) == 2 && $arr[1] == "preferences" && ($user->getID() == Auth::getUserID() || Auth::isAdmin()) &&
                ($user->getID() == Auth::getUserID() || Auth::isSuperAdmin())) {
            if (isset($_POST["name"]) && $_POST["name"] != "") {
                $user->setName($_POST["name"]);
            }
            if (isset($_POST["mail_adress"]) && $_POST["mail_adress"] != "") {
                $user->setMailAdress($_POST["mail_adress"]);
            }
            if (isset($_POST["math_course"]) && $_POST["math_course"] != "") {
                $user->setMathCourse($_POST["math_course"]);
            }
            if (isset($_POST["math_teacher"]) && $_POST["math_teacher"] != "") {
                $user->setMathTeacher($_POST["math_teacher"]);
            }
            if (isset($_POST["password"]) &&
                    $_POST["password"] != "" &&
                    isset($_POST["password_repeat"]) &&
                    $_POST["password"] == $_POST["password_repeat"] &&
                    isset($_POST["old_password"]) &&
                    Auth::cryptCompare($_POST["old_password"], $user->getCryptStr())) {
                $user->setPassword($_POST["password"], !Auth::isSameUser($user));
            }
            $user->updateDB();
            Auth::login($_POST["name"], $_POST["password"]);
        } else if (isset($_POST["id"]) && $user->getID() == Auth::getUserID()) {
            if ($_POST["action"] == "notify") {
                $user->notifyUserComment($_POST["id"]);
            } else {
                $user->unnotifyUserComment($_POST["id"]);
            }
        } else if (isset($_POST["text"]) && $_POST["text"] != "" &&
                $user->getID() != Auth::getUserID()) {
            $user->postUserComment($_POST["text"], isset($_POST["isanonymous"]));
        }
        $this->get($slug);
    }

}