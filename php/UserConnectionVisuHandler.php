<?php

/*
 * Copyright (C) 2013 Parttimenerd
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

/**
 * Description of UserConnectionVisuHandler
 *
 * @author Parttimenerd
 */
class UserConnectionVisuHandler extends ToroHandler {

    public function get($slug = "") {
        if (strlen($slug) >= 2) {
            $slug_arr = explode("/", substr($slug, 1));
            $ajax = false;
            $type = UserConnectionVisu::ALL;
            switch ($slug_arr[0]) {
                case "received":
                    $type = UserConnectionVisu::RECEIVED_COMMENTS;
                    break;
                case "sent":
                    $type = UserConnectionVisu::SENT_COMMENTS;
                    break;
                case "all":
                    $type = UserConnectionVisu::ALL;
                    break;
                case "ajax":
                    $ajax = true;
                    break;
            }
            if (!$ajax && count($slug_arr) == 2 && $slug_arr[1] == "ajax"){
                $ajax = true;
            }
            if ($ajax) {
                header("Content-Type: application/json");
                $cons = UserConnectionVisu::getConnectionArray($type);
                echo json_encode($cons);
            } else {
                tpl_user_connection_visu_page();
            }
        } else {
            tpl_user_connection_visu_page();
        }
    }

    public function post($slug) {
        $this->get($slug);
    }

}

?>
