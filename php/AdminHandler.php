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

class AdminHandler extends ToroHandler {

    public function get() {
        global $env;
        if (Auth::isModerator()) {
            tpl_admin($env->getNotActivatedUsers(), $env->getNotReviewedUserComments());
        } else {
            tpl_404();
        }
    }

    public function post() {
        if (Auth::isModerator()) {
            if (!empty($_POST)) {
                if (isset($_POST["usermanagement"])) {
                    $handler = new UserManagementHandler();
                    $handler->post();
                    ob_clean();
                } else {
                    foreach ($_POST as $key => $value) {
                        if (preg_match("/[0-9]+/", $key)) {
                            if (isset($_POST["review"])) {
                                User::reviewUserComment($key);
                            } else if (isset($_POST["delete"])) {
                                User::deleteUserComment($key);
                            }
                        }
                    }
                }
            }
            $this->get();
        } else {
            tpl_404();
        }
    }

}