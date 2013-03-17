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

class ForgotPasswordHandler extends ToroHandler {

    public function get($slug = "") {
        if (count($_GET) != 2) {
            tpl_forgot_password();
        } else {
            $user = User::getByID(intval($_GET["id"]));
            if ($user->getCryptStr() == $_GET["key"]) {
                tpl_new_password("forgot_password?id=" . intval($_GET["id"]) . "&key=" . $_GET["key"]);
            }
        }
    }

    public function post($slug = "") {
        global $env;
        if (isset($_POST["name_or_email"])) {
            if (isset($_POST["name_or_email"])) {
                $user = User::getByName($_POST["name_or_email"]);
                if (!$user)
                    $user = User::getByEMailAdress($_POST["name_or_email"]);
                if ($user != null) {
                    $link = tpl_url("forgot_password?id=" . $user->getID() . "&key=" . urlencode($user->getCryptStr()));
                    $text = "
	Sie haben das Zurücksetzen ihres Passworts angefordert. Wenn dies richtig ist, 
	klicken Sie bitte auf diesen <a href=\"$link\">Link</a>, wenn nicht, 
	dann ignorieren Sie einfach diese E-Mail.";
                    $env->sendMail($user->getMailAdress(), "Passwort zurücksetzen", $text);
                    tpl_forgot_password_mail_send();
                    return;
                }
            }
        } else {
            $user = User::getByID(intval($_GET["id"]));
            if ($user->getCryptStr() == $_GET["key"]) {
                if (isset($_POST["pwd"]) && $_POST["pwd"] != "" && isset($_POST["pwd_repeat"]) && $_POST["pwd"] == $_POST["pwd_repeat"]) {
                    $user->setPassword($_POST["pwd"]);
                    Auth::login($user->getName(), $_POST["pwd"]);
                    tpl_home();
                } else {
                    tpl_new_password("forgot_password?id=" . intval($_GET["id"]) . "&key=" . $_GET["key"]);
                }
            }
        }
        $this->get();
    }

}
