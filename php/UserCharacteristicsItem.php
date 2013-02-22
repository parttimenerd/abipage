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
 * Model of a user characteristic
 *
 * @author Johannes Bechberger
 */
class UserCharacteristicsItem {

    /**
     * ID
     * @var int
     */
    private $id;

    /**
     *
     * @var User
     */
    private $user;

    /**
     *
     * @var UserCharacteristicsTopic
     */
    private $topic;

    /**
     * Type of the topic
     * @var int
     */
    private $type;

    /**
     * Answer of the user
     * @var string|int
     */
    private $answer;

    /**
     * Time the answer had been sent
     * @var int
     */
    private $time;

    /**
     * 
     * @param int $id
     * @param int|User $user
     * @param UserCharacteristicsTopic|int $topic
     * @param string|int $answer
     */
    public function __construct($id, $user, $topic, $answer, $time) {
        $this->id = $id;
        $this->user = is_a($user, "User") ? $user : User::getByID($user);
        $this->topic = is_object($topic) ? $topic : UserCharacteristicsTopic::getByID($topic);
        $this->type = $this->topic->getType();
        $this->time = $time;
        if ($this->type == UserCharacteristicsTopic::NUMBER_TYPE) {
            $this->answer = intval($answer);
        } else {
            $this->answer = $answer;
        }
    }

    /**
     * 
     * @param array $array
     * @return null|\UserCharacteristicsItem
     */
    public static function getFromArray($array) {
        if ($array == null || !isset($array["id"]) || $array["id"] == "") {
            return null;
        }
        return new UserCharacteristicsItem($array["id"], $array["userid"], $array["topic"], $array["text"], is_string($array["time"]) ? strtotime($array["time"]) : $array["time"]);
    }

    /**
     * 
     * @param mysqli_result $mysql_result
     * @return null|\UserCharacteristicsItem
     */
    public static function getFromMySQLResult($mysql_result) {
        if ($mysql_result == null) {
            return null;
        }
        $res = $mysql_result->fetch_array();
        if ($res == null) {
            return null;
        }
        if (!isset($res["text"])) {
            $res["text"] = "";
        }
        return self::getFromArray($res);
    }

    /**
     * 
     * @global mysqli $db
     * @param int $id
     * @return null|\UserCharacteristicsItem
     */
    public static function getByID($id) {
        global $db;
        return self::getFromMySQLResult($db->query("SELECT * FROM " . USERCHARACTERISTIC_ITEMS_TABLE . " WHERE id=" . intval($id)));
    }

    /**
     * 
     * @global mysqli $db
     * @param int|UserCharacteristicsTopic $topic
     * @param int|User|null $user filter with userid, null = don't filter
     * @return \UserCharacteristicsItem[]
     */
    public static function getByTopic($topic, $user = null) {
        global $db;
        if ($user != null) {
            $cuserid = is_object($user) ? $user->getID() : intval($user);
        } else {
            $cuserid = -1;
        }
        $ctopicID = is_numeric($topic) ? intval($topic) : $topic->getID();
        $res = $db->query("SELECT * FROM " . USERCHARACTERISTIC_ITEMS_TABLE . " WHERE topic=" . $ctopicID . ($cuserid != -1 ? (" AND userid=" . $cuserid) : ""));
        $retarr = array();
        while ($poll = self::getFromMySQLResult($res)) {
            $retarr[] = $poll;
        }
        return $retarr;
    }

    /**
     * Queries all items of a a user or all users (then in an array with the userid as as key), in an array sorted by the position
     * 
     * @param int|User|null $user filter with userid, null = don't filter and returns an array with the items for all users
     * @return array
     */
    public static function getAll($user) {
        global $db;
        $cuserid = is_object($user) ? $user->getID() : intval($user);
        $arr = array();
        if ($user === null) {
            $userarr = User::getAll();
            foreach ($userarr as $user) {
                $arr[$user->getID()] = self::getAll($user);
            }
        } else {
            $res = $db->query("SELECT *, (SELECT position FROM " . USERCHARACTERISTIC_TOPIC_TABLE . " WHERE id=topic) as position FROM " . USERCHARACTERISTIC_ITEMS_TABLE . " WHERE userid=" . $cuserid . " ORDER BY position ASC");
            while ($item = self::getFromMySQLResult($res)) {
                $arr[] = $item;
            }
        }
        return $arr;
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return "ID: " + $this->id + "; Type: " + $this->getTypeString() + "; Text: '" + $this->getQuestion() + "'";
    }

    public function getAnswer() {
        return $this->answer;
    }

    public function getID() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function getTopic() {
        return $this->topic;
    }

    public function getTime(){
        return $this->time;
    }
    
    /**
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

}

?>
