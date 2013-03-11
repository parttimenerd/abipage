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

class MainHandler extends ToroHandler {

    public function get() {
        global $env;
        if (!isset($_REQUEST["ajax"]) || !$_REQUEST["ajax"]) {
            if (Auth::getUserMode() != User::NO_MODE) {
                tpl_home(NewsList::getNews($env->number_of_news_shown_at_the_home_page), Actions::getLastActions());
            } else {
                tpl_home_no_user();
            }
        } else {
            ActionsHandler::_get();
        }
    }

}