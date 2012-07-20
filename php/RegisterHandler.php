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

class RegisterHandler extends ToroHandler {

    public function get() {
        if (Auth::getUser() == null) {
            tpl_register();
        } else {
            $handler = new MainHandler();
            $handler->get();
        }
    }

    public function post() {
        global $env;
        if (isset($_POST["name"]) &&
                $_POST["name"] != "" &&
                isset($_POST["mail_adress"]) &&
                $_POST["mail_adress"] != "" &&
                isset($_POST["math_course"]) &&
                $_POST["math_course"] != "" &&
                isset($_POST["math_teacher"]) &&
                $_POST["math_teacher"] != "" &&
                isset($_POST["password"]) &&
                $_POST["password"] != "" &&
                isset($_POST["password_repeat"]) &&
                $_POST["password"] == $_POST["password_repeat"]) {
            $user = User::create($_POST["name"], $_POST["math_course"], $_POST["math_teacher"], $_POST["mail_adress"], $_POST["password"]);
            PiwikHelper::addTrackGoalJS("New user registrated");
            //Auth::login($_POST["first_name"] . " " . $_POST["last_name"], $_POST["password"]);
            if ($env->has_forum && isset($_POST["reg_in_forum"])) {
                register_user_in_forum($user, $_POST["password"]);
            }
            if ($env->has_wiki && isset($_POST["reg_in_wiki"])) {
                register_user_in_wiki($user, $_POST["password"]);
            }
            tpl_welcome_wait_for_activation();
        } else {
            $this->get();
        }
    }

}