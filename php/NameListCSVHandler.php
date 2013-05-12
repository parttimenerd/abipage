<?php

/*
 * Copyright (C) 2013 Johannes Bechberger
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

class NameListCSVHandler extends ToroHandler {

    public function get($slug = "") {
        $min_ratings = isset($_GET["min_ratings"]) ? intval($_GET["min_ratings"]) : 0;
        $min_items = isset($_GET["min_ratings"]) ? intval($_GET["min_items"]) : 0;
        $min_actions = isset($_GET["min_actions"]) ? intval($_GET["min_actions"]) : -1;
        if (!isset($_GET["html"])) {
            header("Content-Disposition: attachment; filename*=UTF-8''namelist.csv");  
        }
        echo User::getCSVNameSet($min_actions, $min_items, $min_ratings);
    }

}

?>
