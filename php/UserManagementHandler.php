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

class UserManagementHandler extends ToroHandler {

    public function get() {
        global $env;
        tpl_usermanagement($env->getUsers());
    }

    public function post() {
        if (Auth::getUserMode() >= User::MODERATOR_MODE) {
            if (!empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    if (preg_match("/^[0-9]+/", $key)) {
                        $user = User::getByID(intval($key));
                        if (isset($_POST["activate"])) {
                            $user->activate();
                        } else if (isset($_POST["deactivate"])) {
                            $user->deactivate();
                        } else if (isset($_POST["setmode"]) && isset($_POST["mode"]) &&
                                (Auth::getUserMode() == User::ADMIN_MODE || Auth::getUserMode() > intval($_POST["mode"]))) {
                            $user->setMode(intval($_POST["mode"]));
                        } else if (isset($_POST["setpassword"]) && isset($_POST["password"])) {
                            $user->setPassword($_POST["password"], true);
                        }
                        $user->updateDB();
                    }
                }
            }
            $this->get();
        } else {
            tpl_404();
        }
    }

}