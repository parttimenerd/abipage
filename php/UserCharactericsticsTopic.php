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
 * Model of a user characteristics topic
 *
 * @author Johannes Bechberger
 */
class UserCharacteristicsTopic {
    /**
     * Answer is a on line text
     */

    const SHORTTEXT_TYPE = 1;
    /**
     * Answer is a block of text
     */
    const LONGTEXT_TYPE = 2;
    /**
     * Answer is a number
     */
    const NUMBER_TYPE = 3;
    /**
     * Answer is a picture
     */
    const PICTURE_TYPE = 4;

    /**
     *
     * @var UserCharacteristicsTopic[]
     */
    private static $instance_cache = array();

    /**
     * ID
     * @var int
     */
    private $id;

    /**
     *
     * @var int
     */
    private $type;

    /**
     * Question text
     * 
     * @var string
     */
    private $text;

    /**
     *
     * @var int
     */
    private $position;

    /**
     * 
     * @param int $id
     * @param int $type
     * @param string $text
     * @param int $position
     */
    public function __construct($id, $type, $text, $position) {
        $this->id = $id;
        $this->type = $type;
        $this->text = $text;
        $this->position = $position;
    }

    /**
     * 
     * @param array $array
     * @return null|UserCharacteristicsTopic
     */
    public static function getFromArray($array) {
        if ($array == null || !isset($array["id"]) || $array["id"] == "") {
            return null;
        }
        return new UserCharacteristicsTopic($array["id"], $array["type"], $array["text"], $array["position"]);
    }

    /**
     * 
     * @param mysqli_result $mysql_result
     * @return null|UserCharacteristicsTopic
     */
    public static function getFromMySQLResult($mysql_result) {
        if ($mysql_result == null) {
            return null;
        }
        $res = $mysql_result->fetch_array();
        if ($res == null) {
            return null;
        }
        return self::getFromArray($res);
    }

    /**
     * 
     * @global mysqli $db
     * @param int $id
     * @param boolean $force_query
     * @return null|UserCharacteristicsTopic
     */
    public static function getByID($id, $force_query = false) {
        global $db;
        $cid = intval($id);
        if ($force_query || !isset(self::$instance_cache[$cid]) || self::$instance_cache[$cid] === null) {
            self::$instance_cache[$cid] = self::getFromMySQLResult($db->query("SELECT * FROM " . USERCHARACTERISTIC_TOPIC_TABLE . " WHERE id=" . intval($id)));
        }
        return self::$instance_cache[$cid];
    }

    /**
     * Ordered by position ascending
     * 
     * @global mysqli $db
     * @return UserCharacteristicsTopic[]
     */
    public static function getAll() {
        global $db;
        $arr = array();
        $res = $db->query("SELECT * FROM " . USERCHARACTERISTIC_TOPIC_TABLE . " ORDER BY position ASC");
        while ($topic = self::getFromMySQLResult($res)) {
            $arr[] = $topic;
            self::$instance_cache[$topic->getID()] = $topic;
        }
        return $arr;
    }

    /**
     * 
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * 
     * @param int $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * 
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * 
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * 
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * 
     * @return int
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * 
     * @param int $position
     * @return UserCharacteristicsTopic
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * @global mysqli $db
     * @param int $type
     * @param string $text Question
     * @param int $position
     * @return UserCharacteristicsTopic
     */
    public static function create($type, $text, $position) {
        global $db;
        $type = intval($type);
        $text = sanitizeInputText($text);
        $position = intval($position);
        $db->query("INSERT INTO " . USERCHARACTERISTIC_TOPIC_TABLE . "(id, type, text, position) VALUES(NULL, $type, '$text', $position)");
        $id = $db->insert_id;
        return new UserCharacteristicsTopic($id, $type, $text, $position);
    }

    /**
     * 
     * @global mysqli $db
     */
    public function delete() {
        global $db;
        $db->query("DELETE FROM " . USERCHARACTERISTIC_TOPIC_TABLE . " WHERE id=" . $this->id);
        $db->query("DELETE FROM " . USERCHARACTERISTIC_ITEMS_TABLE . " WHERE topic=" . $this->id);
    }

    /**
     * 
     * @global mysqli $db
     */
    public function updateDB() {
        global $db;
        $db->query("UPDATE " . USERCHARACTERISTIC_TOPIC_TABLE . " SET type=" . intval($this->type) . ", text='" . sanitizeInputText($this->text) . "', position=" . intval($this->position) . " WHERE id=" . $this->id);
    }

    /**
     * 
     * @global Environment $env
     * @param string $upload_name
     */
    public function submitPicture($upload_name) {
        global $env;
        $user = Auth::getUser();
        $file_path = $env->upload_path . '/' . $user->getPictureDirPart();
        $exif = $env->uploadImage($this->id, $upload_name, $file_path, false);
        $this->_submit($user->getPictureDirPart() . '/' . $exif["FileName"], true);
    }

    public function submit($answer) {
        if ($this->type == self::PICTURE_TYPE) {
            $this->submitPicture($answer);
        } else {
            $this->_submit($answer);
        }
    }

    private function _submit($answer, $dont_sanitize = false) {
        global $db;
        $userid = Auth::getUserID();
        if (!$dont_sanitize) {
            switch ($this->type) {
                case self::NUMBER_TYPE:
                    $canswer = intval($answer);
                    break;
                default:
                    $canswer = sanitizeInputText($answer);
            }
        } else {
            $canswer = $answer;
        }
        if ($canswer != "") {
            $res = $db->query("SELECT * FROM " . USERCHARACTERISTIC_ITEMS_TABLE . " WHERE topic=" . $this->id . " AND userid=" . $userid);
            if ($res && $res->fetch_array()) {
                $db->query("UPDATE " . USERCHARACTERISTIC_ITEMS_TABLE . " SET text='" . $canswer . "' WHERE topic=" . $this->id . " AND userid=" . $userid);
            } else {
                $db->query("INSERT INTO " . USERCHARACTERISTIC_ITEMS_TABLE . "(userid, topic, type, text) VALUES(" . $userid . ", " . $this->id . ", " . $this->type . ", '" . $canswer . "')");
            }
        }
    }

    /**
     * 
     * @param int $type
     * @return string
     */
    public static function getStringRepOfType($type) {
        if (is_int($type)) {
            switch ($type) {
                case self::NUMBER_TYPE:
                    return "Zahl";
                case self::LONGTEXT_TYPE:
                    return "Langer Text";
                case self::SHORTTEXT_TYPE:
                    return "Kurzer Text";
                case self::PICTURE_TYPE:
                    return "Bild";
                default:
                    return "Weitere";
            }
        }
        return $type;
    }

    /**
     * @param null|User $user null if current user
     * @return UserCharacteristicsItem
     */
    public function getUserAnswerItem($user = null) {
        $arr = UserCharacteristicsItem::getByTopic($this, $user == null ? Auth::getUser() : $user);
        return count($arr) == 0 ? null : $arr[0];
    }

}

?>
