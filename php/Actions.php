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

class Actions {

    /**
     * 
     * @global mysqli $db
     * @global Enviroment $env
     * @param int $last_action_id
     * @return \ActionArray
     */
    public static function getLastActions($last_action_id = -1) {
        global $db, $env;
        $actions = array();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "actions WHERE id > " . intval($last_action_id) . " ORDER BY time DESC LIMIT 0, " . intval($env->showed_actions)) or die($db->error);
        if ($res != null) {
            while ($action = $res->fetch_array()) {
                $actions[] = $action;
            }
        }
        return new ActionArray($actions);
    }

    /**
     * 
     * @global mysqli $db
     * @global Enviroment $env
     * @param type $last_time
     * @return \ActionArray
     */
    public static function getLastActionsSince($last_time) {
        global $db, $env;
        $actions = array();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "actions WHERE time > " . intval($last_time) . " ORDER BY time DESC LIMIT 0, " . intval($env->showed_actions)) or die($db->error);
        if ($res != null) {
            while ($action = $res->fetch_array()) {
                $actions[] = $action;
            }
        }
        return new ActionArray($actions);
    }
    
    /**
     * 
     * @global mysqli $db
     * @param int $id
     * @return array
     */
    public static function getActionByID($id) {
        global $db;
        $action = null;
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "actions WHERE id = " . intval($id)) or die($db->error);
        if ($res != null)
            $action = $res->fetch_array();
        return $action;
    }

    /**
     * 
     * @global mysqli $db
     * @global KeyValueStore $store
     * @param int $itemid
     * @param string $person
     * @param int $type
     * @param int $time
     * @param null|User $user
     */
    public static function addAction($itemid, $person, $type, $time = -1, $user = null) {
        global $db, $store;
        if ($user == null) {
            $user = Auth::getUser();
            if ($user == null){
                $userid = -1;
            } else {
                $userid = $user->getID();
            }
        }
        if ($time == -1) {
            $time = time();
        }
        $person = $db->real_escape_string($person);
        $type = $db->real_escape_string($type);
        $db->query("INSERT INTO " . DB_PREFIX . "actions(id, userid, itemid, person, type, time) VALUES(NULL, " . $userid . ", " . intval($itemid) . ", '" . $person . "', '" . $type . "', " . $time . ")") or die($db->error);
        $store->last_action_id = $db->insert_id;
        $store->updateDB();
    }

}

?>
