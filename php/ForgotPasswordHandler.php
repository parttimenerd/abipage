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
        $arr = explode('/', substr($slug, 1));
        if (count($arr) != 2) {
            tpl_forgot_password();
        } else {
            $user = User::getByID(intval($arr[0]));
            if ($user->getCryptStr() == $arr[1]) {
                tpl_new_password("forgot_password" . $slug);
            }
        }
    }

    public function post($slug = "") {
        global $env;
        $arr = explode('/', substr($slug, 1));
        if (count($arr) != 2) {
            if (isset($_POST["name_or_email"])) {
                $user = User::getByName($_POST["name_or_email"]) || User::getByEMailAdress($_POST["name_or_email"]);
                if ($user != null) {

                    $link = tpl_link("forgot_password/" . $user->getID() . "/" . $user->getCryptStr());
                    $text = "<html><body>
	Hallo " . $user->getName() . ",<br/><br/>
	sie haben das Zurücksetzen ihres Passworts angefordert, wenn das richtig ist, 
	klicken sie bitte auf diesen <a href=\"$link\">Link</a>, wenn nicht, 
	dann ignorieren sie diese E-Mail bitte einfach.<br/><br/>Ihr \"" . $env->title . "\"-Team
	</body></html>";
                    $env->sendMail($user->getMailAdress(), "Passwort zurücksetzen", $text);
                    return;
                }
            }
        } else {
            $user = User::getByID(intval($arr[0]));
            if ($user->getCryptStr() == $arr[1]) {
                if (isset($_POST["pwd"]) && $_POST["pwd"] != "" && isset($_POST["pwd_repeat"]) && $_POST["pwd"] == $_POST["pwd_repeat"]) {
                    $user->setPassword($_POST["pwd"]);
                    Auth::login($user->getName(), $_POST["pwd"]);
                    tpl_main();
                } else {
                    tpl_new_password("forgot_password" . $slug);
                }
            }
        }
        $this->get();
    }

}