<?

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

class ActionArray {

    private $last_action_id = -1;
    private $actions = array();

    public function __construct($actions) {
        $this->actions = $actions;
        foreach ($this->actions as $action)
            if ($this->last_action_id < $action["id"])
                $this->last_action_id = $action["id"];
    }

    public function each($func) {
        if (is_callable($func)) {
            foreach ($this->actions as $action)
                $func($action);
        }
        return $this;
    }

    public function getActionArray() {
        return $this->actions;
    }

    public function getLastActionID() {
        return $this->last_action_id;
    }

}

?>
