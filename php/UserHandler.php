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
        $user = self::getUserFromSlug($slug, true);
        if ($user === false) {
            $arr = explode('/', substr($slug, 1));
            $slug = $arr[0];
            $str = str_replace("_", " ", $slug);
            $suggestions = User::getNameSuggestions($str);
            if (count($suggestions) > 1) {
                tpl_user_not_found($str, $suggestions);
                return;
            } else if (count($suggestions) == 1) {
                tpl_user(User::getByName($suggestions[0]));
                return;
            } else {
                $user = null;
            }
        }
        if (!$user) {
            if (!isset($_GET["ajax"])) {
                tpl_userlist($env->getUserNames(false, isset($arr[1]) ? str_replace('_', ' ', $arr[1]) : "", false));
            } else {
                jsonAjaxResponseStart();
                tpl_userlist(User::getNameSuggestions($_GET["phrase"], false, true), $_GET["phrase"], true);
                jsonAjaxResponseEndSend();
            }
        } else if (count($arr) == 2 && $arr[1] == "preferences" && Auth::canEditUser($user) && !$user->isBlocked()) {
            tpl_user_prefs($user);
        } else {
            tpl_user($user);
        }
    }

    public function get_result($slug = "") {
        global $env;
        if ($slug == "all_pages" || $slug == "/all_pages") {
            $userarr = User::getAll(true, false)->toArray();
            $arr = array();
            foreach ($userarr as $user) {
                $arr[] = array("user" => $user, "user_characteristics" => UserCharacteristicsItem::getAll($user));
            }
            tpl_user_pages($arr);
        } else {
            $user = self::getUserFromSlug($slug);
            if (Auth::isSameUser($user)) {
                $this->get($slug);
            } else if (!$user) {
                $this->get($slug);
            } else {
                tpl_user_page($user, UserCharacteristicsItem::getAll($user));
            }
        }
    }

    public static function getUserFromSlug($slug, $disallow_namelike = false) {
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
                if ($user == null) {
                    if (!$disallow_namelike) {
                        $user = User::getByNameLike($str);
                    } else {
                        return false;
                    }
                }
                return $user;
            }
        }
        return null;
    }

    public function post($slug) {
        global $env;
        $arr = explode('/', substr($slug, 1));
        $user = self::getUserFromSlug($slug);
        if ($user == null) {
            return;
        }
        if (count($arr) == 2 && $arr[1] == "preferences" && Auth::canEditUser($user) && !$user->isBlocked()) {
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
            $user->setSendEmailWhenBeingCommented(isset($_POST["send_email_when_being_commented"]));
            $user->updateDB();
            Auth::login($_POST["name"], $_POST["password"]);
            $this->get($slug);
        } else if (isset($_POST["id"]) && $user->getID() == Auth::getUserID() && !$user->isBlocked()) {
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
                $user->getID() != Auth::getUserID() && $env->user_comments_editable) {
            $comment = $user->postUserComment($_POST["text"], isset($_POST["send_anonymous"]));
            jsonAjaxResponseStart();
            $data = array();
            if ($comment) {
                if ($comment["reviewed"] === 1) {
                    tpl_user_comment($user, $comment);
                    $data = array("reviewed" => true);
                } else {
                    tpl_user_comment_not_reviewed_info();
                    $data = array("reviewed" => false);
                }
            }
            if (isset($_POST["send_anonymous"]))
                PiwikHelper::addTrackGoalJS("Anonymous contribution");
            PiwikHelper::addTrackGoalJS("User commented", $_POST["text"]);
            jsonAjaxResponseEndSend($data);
        } else if (isset($_POST["deleteComment"]) && Auth::canDeleteUserComment()) {
            User::deleteUserComment(intval($_POST["deleteComment"]));
            jsonAjaxResponse(array("id" => intval($_POST["deleteComment"])));
        }
    }

}