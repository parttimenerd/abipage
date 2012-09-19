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

class ActionsHandler extends ToroHandler {

    public function get($slug = "") {
        $slug = $slug != "" ? substr($slug, 1) : "";
        if ($slug != "" && is_numeric($slug)) {
            $action = Actions::getActionByID($slug);
            if ($action != null)
                tpl_actions_page(new ActionArray(array($action)));
        } else {
            tpl_actions_page(Actions::getLastActions());
        }
    }

}