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
            tpl_userlist($env->getUserNames(false, isset($arr[1]) ? str_replace('_', ' ', $arr[1]) : ""));
        } else if (count($arr) == 2 && $arr[1] == "preferences" && Auth::canEditUser($user)) {
            tpl_user_prefs($user);
        } else {
            tpl_user($user);
        }
    }

    public function get_result($slug = "") {
        global $env;
        $user = $this->getUserFromSlug($slug);
        if (!$user) {
            tpl_userlist($env->getUserNames());
        } else {
            tpl_user_page($user);
        }
    }

    private function getUserFromSlug($slug) {
        if ($slug != "" && $slug != '/' && $slug != "all") {
            $arr = explode('/', substr($slug, 1));
            $slug = $arr[0];
            if ($slug == "me") {
                return Auth::getUser();
            } else if ($slug == "all") {
                return null;
            } else if (is_int($slug)) {
                return User::getByID(intval($slug));
            } else {
                $str = str_replace("_", " ", $slug);
                $user = User::getByName($str);
                if ($user == null)
                    $user = User::getByNameLike($str);
                return $user;
            }
        }
        return null;
    }

    public function post($slug) {
        $arr = explode('/', substr($slug, 1));
        $user = $this->getUserFromSlug($slug);
        if (count($arr) == 2 && $arr[1] == "preferences" && Auth::canEditUser($user)) {
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
            $user->sendEmailWhenBeingCommented(isset($_POST["send_email_when_being_commented"]));
            $user->updateDB();
            Auth::login($_POST["name"], $_POST["password"]);
            $this->get($slug);
        } else if (isset($_POST["id"]) && $user->getID() == Auth::getUserID()) {
            $data = array("id" => intval($_POST["id"]));
            if ($_POST["action"] == "notify") {
                $user->notifyUserComment(intval($_POST["id"]));
                $data["msg"] = "unnotified";
            } else {
                $user->unnotifyUserComment(intval($_POST["id"]));
                $data["msg"] = "notified";
            }
            jsonAjaxResponse($data);
        } else if (isset($_POST["text"]) && strlen($_POST["text"]) >= 5 &&
                $user->getID() != Auth::getUserID()) {
            $comment = $user->postUserComment($_POST["text"], isset($_POST["send_anonymous"]));
            jsonAjaxResponseStart();
            if ($comment)
                tpl_user_comment($user, $comment);
            if (isset($_POST["send_anonymous"]))
                PiwikHelper::addTrackGoalJS("Anonymous contribution");
            PiwikHelper::addTrackGoalJS("User commented", $_POST["text"]);
            jsonAjaxResponseEndSend();
        } else if (isset($_POST["deleteItem"]) && Auth::canDeleteUserComment()) {
            User::deleteUserComment(intval($_POST["deleteItem"]));
            jsonAjaxResponse(array("id" => intval($_POST["deleteItem"])));
        }
    }

}