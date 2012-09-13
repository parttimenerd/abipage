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

class LoginHandler extends ToroHandler {

    public function get($slug = "") {
        tpl_login();
    }

    public function post() {
        global $env;
        if (isset($_POST['name']) &&
                isset($_POST['password']) &&
                isset($_POST['login']) &&
                Auth::login($_POST['name'], $_POST['password']) &&
                (Auth::canVisitSiteWhenUnderConstruction() || !$env->is_under_construction)) {
            $handler = new MainHandler();
            $handler->get();
        } else {
            $this->get("");
        }
    }

}