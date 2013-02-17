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
 * Description of UserConnectionVisu
 *
 * @author Parttimenerd
 */
class UserConnectionVisu {

    const SENT_COMMENTS = 1;
    const RECEIVED_COMMENTS = 2;
    const ALL = 3;

    /**
     * 
     * @global mysqli $db
     * @param int $id
     */
    public static function getConnectionsArrForUserID(User $user, $type = self::ALL) {
        global $db;
        $id = $user->getID();
        $connections = array();
        $userid_comp = " ";
        switch ($type) {
            case self::SENT_COMMENTS:
                $userid_comp = " commenting_userid=$id AND commented_userid=u.id ";
                break;
            case self::RECEIVED_COMMENTS:
                $userid_comp = " commenting_userid=u.id AND commented_userid=$id ";
                break;
            case self::ALL;
            default:
                $userid_comp = "(commenting_userid=u.id AND commented_userid=$id) OR (commenting_userid=$id AND commented_userid=u.id) ";
                break;
        }
        if (Auth::canSeeNameWhenSentAnonymous()) {
            $query = "SELECT u.id AS 'id', " .
                    " (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp ) AS 'count', " .
                    " (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp  AND notified_as_bad=0 AND isanonymous=0)" .
                    "- (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp  AND notified_as_bad=1 AND isanonymous=0) " .
                    " AS 'weight_unano'," .
                    " (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp  AND notified_as_bad=0 AND isanonymous=1)" .
                    "- (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp  AND notified_as_bad=1 AND isanonymous=1) AS " .
                    "'weight_ano'" .
                    " FROM " . DB_PREFIX . "user `u` WHERE (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp ) > 0";
            $res = $db->query($query);
            if ($res != null) {
                $arr = mysqliResultToArr($res);
//            var_dump($arr);
                foreach ($arr as $value) {
                    $connections[] = array($value["id"], $value["weight_unano"] + ($value["weight_ano"] * 0.5));
                }
            }
//            var_dump($query);
        } else {
            $query = "SELECT u.id AS 'id', " .
                    " (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp ) AS 'count', " .
                    " (SELECT count(*) FROM " . DB_PREFIX . "user_comments WHERE $userid_comp  AND notified_as_bad=0 AND isanonymous=0)" .
                    " AS 'weight' " .
                    " FROM " . DB_PREFIX . "user `u`";
            $res = $db->query($query);
            if ($res != null) {
                $arr = mysqliResultToArr($res);
//            var_dump($arr);
                foreach ($arr as $value) {
                    $connections[] = array($value["id"], $value["weight_unano"] + ($value["weight_ano"] * 0.5));
                }
                $res = $db->query("SELECT count(*) as 'weight' FROM " . DB_PREFIX . "user_comments WHERE (commenting_userid=$id OR commented_userid=$id) AND isanonymous=0 AND notified_as_bad=0");
                if ($res != null) {
                    $arr = mysqliResultToArr($res);
                    if ($arr["weight"] > 0) {
                        $connections[] = array("-1", $arr["weight"]);
                    }
                }
            }
        }
        return $connections;
    }

    public static function getConnectionArray($type = self::ALL) {
        $users = User::getAll();
        $arr = array();
        foreach ($users->toArray() as $user) {
            $cons = self::getConnectionsArrForUserID($user, $type);
            $arr[] = array("id" => $user->getID(), "text" => $user->getName(), "connections" => $cons);
        }
        if (!Auth::canSeeNameWhenSentAnonymous()) {
            $arr[] = array("id" => "-1", "text" => "Anonym", "connections" => array());
        }
        $min = 0;
        $max = 0;
        foreach ($arr as $value) {
            $cons = $value["connections"];
            foreach ($cons as $connection) {
                if ($connection[1] < $min) {
                    $min = $connection[1];
                }
                if ($connection[1] > $max) {
                    $max = $connection[1];
                }
            }
        }
        $factor = 255 / ($max - $min);
        $c = count($arr);
        for ($i = 0; $i < $c; $i++) {
            $cons = $arr[$i]["connections"];
            $c2 = count($cons);
            for ($j = 0; $j < $c2; $j++) {
                $arr[$i]["connections"][$j][1] = ($arr[$i]["connections"][$j][1] - $min) * $factor;
            }
        }
        return $arr;
    }

}

?>
