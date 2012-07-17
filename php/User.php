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

class User {

    const ADMIN_MODE = 3;
    const MODERATOR_MODE = 2;
    const EDITOR_MODE = 1;
    const NORMAL_MODE = 0;
    const NO_MODE = -1;

    private $id;
    private $name;
    private $math_course;
    private $math_teacher;
    private $mail_adress;
    private $mode;
    private $activated;
    private $crypt_str;
    private $visible;
    private $db;

    public function __construct($id, $name, $math_course, $math_teacher, $mail_adress, $mode, $activated, $crypt_str, $visible = true) {
        $db = Database::getConnection();
        $this->id = intval($id);
        $this->name = $db->real_escape_string($name);
        $this->math_course = $db->real_escape_string($math_course);
        $this->math_teacher = $db->real_escape_string($math_teacher);
        $this->mail_adress = $db->real_escape_string($mail_adress);
        $this->mode = !$activated ? self::NO_MODE : intval($mode);
        $this->activated = $activated;
        $this->crypt_str = $db->real_escape_string($crypt_str);
        $this->visible = $visible;
        $this->db = Database::getConnection();
    }

    public static function getFromArray($array) {
        if ($array == null || !isset($array["id"]) || $array["id"] == "") {
            return null;
        }
        if (isset($array["first_name"]) && isset($array["last_name"])) {
            $array["name"] = $array["first_name"] . ' ' . $array["last_name"];
        }
        return new User($array["id"], $array["name"], $array["math_course"], $array["math_teacher"], $array["mail_adress"], $array["mode"], $array["activated"], $array["crypt_str"]);
    }

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

    public static function getByName($name) {
        global $db;
        $arr = explode(" ", $db->real_escape_string($name));
        $str = $arr[0];
        for ($i = 1; $i < count($arr) - 1; $i++) {
            $str .= " " . $arr[$i];
        }
        return User::getFromMySQLResult($db->query("SELECT * FROM " . DB_PREFIX . "user WHERE first_name='" . $str . "' AND last_name='" . $arr[count($arr) - 1] . "'"));
    }

    public static function getByID($id) {
        global $db;
        return User::getFromMySQLResult($db->query("SELECT * FROM " . DB_PREFIX . "user WHERE id=" . intval($id)));
    }

    public static function getByEMailAdress($mail_adress) {
        global $db;
        return User::getFromMySQLResult($db->query("SELECT * FROM " . DB_PREFIX . "user WHERE mail_adress=" . cleanInputText($mail_adress)));
    }
    
    public static function getByMode($mode) {
        global $db;
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "user WHERE mode=" . intval($mode));
        $retarr = array();
        while ($user = User::getFromMySQLResult($res)){
            $retarr[] = $user;
        }
        return new UserArray($retarr);
    }

    /**
      don't forget:
      if ($activated == 1){
      Auth::login($name, $pwd);
      }
     */
    public static function create($name, $math_course, $math_teacher, $mail_adress, $pwd, $mode = self::NORMAL_MODE, $activated = 0, $visible = 1) {
        global $db;
        $name_arr = explode(' ', $db->real_escape_string($name));
        $str = $name_arr[0];
        for ($i = 1; $i < count($name_arr) - 1; $i++) {
            $str .= " " . $name_arr[$i];
        }
        $name_arr[0] = $str;
        $name_arr[1] = $name_arr[count($name_arr) - 1];
        $math_course = intval(is_numeric($math_course) ? $math_course : substr($math_course, 1));
        $math_teacher = $db->real_escape_string($math_teacher);
        $mail_adress = $db->real_escape_string($mail_adress);
        $crypt_str = Auth::crypt($pwd);
        $mode = intval($mode);
        $activated = intval($activated);
        $visible = intval($visible);
        if (self::getByName($name) == null) {
            $db->query('INSERT INTO ' . DB_PREFIX . "user(id, first_name, last_name, math_course, math_teacher, mail_adress, mode, activated, visible, crypt_str) VALUES(NULL, '$name_arr[0]', '$name_arr[1]', $math_course, '$math_teacher', '$mail_adress', $mode, $activated, $visible, '$crypt_str')");
        } else {
            return false;
        }
    }

    public function updateDB() {
        $this->db->query("UPDATE " . DB_PREFIX . "user SET
		first_name='" . $this->db->real_escape_string($this->getFirstName()) . "' 
		last_name='" . $this->db->real_escape_string($this->getLastName()) . "' 
		math_course='" . $this->db->real_escape_string($this->getMathCourse()) . "' 
		math_teacher='" . $this->db->real_escape_string($this->getMathTeacher()) . "'
		mail_adress='" . $this->db->real_escape_string($this->getMailAdress()) . "' 
		crypt_str='" . $this->db->real_escape_string($this->getCryptStr()) . "' 
		mode=" . intval($this->getMode()) . " 
		activated=" . ($this->isActivated() ? 1 : 0) . " 
		visible=" . ($this->isVisible() ? 1 : 0) . "
		WHERE id=" . intval($this->id));
    }

    public function getUserComments($with_notified_as_bad = false) {
        $arr = array();
        $db = Database::getConnection();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "user_comments 
		WHERE commented_userid=" . $this->id . (!$with_notified_as_bad ? " AND notified_as_bad=0" : "") . " AND reviewed=1
		ORDER BY time DESC");
        while ($comment = $res->fetch_array()) {
            $arr[] = $comment;
        }
        return $arr;
    }

    public function notifyUserComment($id) {
        $db = Database::getConnection();
        $db->query("UPDATE " . DB_PREFIX . "user_comments SET notified_as_bad=1 WHERE id=" . intval($id));
    }

    public function unnotifyUserComment($id) {
        $db = Database::getConnection();
        $db->query("UPDATE " . DB_PREFIX . "user_comments SET notified_as_bad=0 WHERE id=" . intval($id));
    }

    public function postUserComment($text, $anonymous, $senduserid = null, $time = -1) {
        global $env;
        if ($senduserid == null) {
            $senduserid = Auth::getUserID();
        }
        if ($time == -1) {
            $time = time();
        }
        $db = Database::getConnection();
        $db->query("INSERT INTO " . DB_PREFIX . "user_comments(id, commented_userid, commenting_userid, text, time, notified_as_bad, reviewed, isanonymous)
					VALUES(NULL, " . $this->id . ", " . intval($senduserid) . ", '" . cleanInputText($text) . "', " . intval($time) . ", 0, 0, " . intval($anonymous) . ")");
        $env->addAction($this->db->insert_id, $this->getName(), "add_user_comment");
    }

    public static function reviewUserComment($id) {
        $db = Database::getConnection();
        $db->query("UPDATE " . DB_PREFIX . "user_comments SET reviewed=1 WHERE id=" . intval($id));
    }

    public static function deleteUserComment($id) {
        $db = Database::getConnection();
        $db->query("DELETE FROM" . DB_PREFIX . "user_comments WHERE id=" . intval($id) . " AND reviewed=0");
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getFirstName() {
        $arr = explode(" ", $this->name);
        $str = $arr[0];
        for ($i = 1; $i < count($arr) - 1; $i++) {
            $str .= " " . $arr[$i];
        }
        return $str;
    }

    public function getLastName() {
        $arr = explode(" ", $this->name);
        return $arr[count($arr) - 1];
    }

    public function getMathCourse() {
        return $this->math_course;
    }

    public function getMathTeacher() {
        return $this->math_teacher;
    }

    public function getMailAdress() {
        return $this->mail_adress;
    }

    public function getMode() {
        return $this->mode;
    }

    public function isActivated() {
        return $this->activated;
    }

    public function getCryptStr() {
        return $this->crypt_str;
    }

    public function setName($first_name, $last_name = "") {
        $this->name = $first_name;
        if ($last_name != "") {
            $this->name .= " " . $last_name;
        }
    }

    public function setMathCourse($course) {
        $this->math_course = $course;
    }

    public function setMathTeacher($teacher) {
        $this->math_teacher = $teacher;
    }

    public function setMailAdress($adress) {
        $this->mail_adress = $adress;
    }

    public function setMode($mode) {
        $mode = intval($mode);
        $this->mode = $mode > User::ADMIN_MODE ? User::NORMAL_MODE : $mode;
    }

    public function setActivated($activated) {
        $this->activated = $activated;
    }

    public function activate() {
        $this->activated = true;
        $this->updateDB();
    }

    public function deactivate() {
        $this->activated = false;
        $this->updateDB();
    }

    public function setPassword($pwd, $mail_user = false) {
        $this->crypt_str = Auth::crypt($pwd);
        if ($mail_user) {
            sendMail($this->mail_adress, "Passwort verändert", "Benutzername: " . $this->name . "\nPasswort: " . $pwd);
        }
        $this->updateDB();
    }

    public function isVisible() {
        return $this->visible();
    }

    public function setVisible($visible) {
        $this->visible = $visible;
    }

}