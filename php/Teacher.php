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

class Teacher {

    private static $name_list = array();
    private $id;
    private $is_male;
    private $first_name;
    private $last_name;
    private $name_str;

    public function __construct($id, $is_male, $first_name, $last_name, $name_str) {
        $this->id = $id;
        $this->is_male = $is_male;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->name_str = $name_str;
    }

    public static function getNameList() {
        if (empty(self::$name_list)) {
            global $db;
            $res = $db->query("SELECT namestr FROM " . DB_PREFIX . "teacher ORDER BY last_name ASC") or die($db->error);
            $arr = array();
            if ($res != null) {
                while ($tarr = $res->fetch_array()) {
                    $arr[] = $tarr["namestr"];
                }
            }
            self::$name_list = $arr;
        }
        return self::$name_list;
    }

    public static function getAllTeachers() {
        global $db;
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "teacher") or die($db->error);
        $arr = array();
        if ($res != null) {
            while ($tarr = $res->fetch_array()) {
                $arr[] = $tarr;
            }
        }
        return $arr;
    }

    public static function getFromArray($arr) {
        return new Teacher($arr["id"], isset($arr["is_male"]) ? $arr["is_male"] : $arr["ismale"], $arr["first_name"], $arr["last_name"], $arr["name_str"]);
    }

    public static function getFromMySQLResult($result) {
        if ($result == null)
            return null;
        $arr = $result->fetch_array();
        if ($arr == null)
            return null;
        return self::getFromArray($arr);
    }

    public static function getByID($id) {
        global $db;
        return self::getFromMySQLResult($db->query("SELECT * FROM " . DB_PREFIX . "teacher WHERE id=" . intval($id)));
    }

    public static function getByName($namestr) {
        global $db;
        return self::getFromMySQLResult($db->query("SELECT * FROM " . DB_PREFIX . "teacher WHERE namestr=" . sanitizeInputText($namestr)));
    }

    public static function getTeacherWithQuoteRatingAndCount() {
        global $db;
        $res = $db->query("SELECT id, first_name, last_name, namestr, ismale, (SELECT count(*) FROM " . DB_PREFIX . "quotes q WHERE q.teacherid = t.id) AS quote_count, (SELECT avg(q2.rating) FROM " . DB_PREFIX . "quotes q2 WHERE q2.teacherid = t.id) AS quote_rating FROM " . DB_PREFIX . "teacher t ORDER BY quote_count DESC");
        $arr = array();
        $sum = 0;
        if ($res != null) {
            while ($tarr = $res->fetch_array()) {
                $arr[] = $tarr;
                $sum += $tarr["quote_count"];
            }
            $len = count($arr);
            for ($i = 0; $i < $len; $i++)
                $arr[$i]["perc"] = $arr[$i]["quote_count"] * 100.0 / $sum;
        }
        return $arr;
    }

    public static function getTeacherWithRumorRatingAndCount() {
        global $db;
        $res = $db->query("SELECT id, first_name, last_name, namestr, ismale, (SELECT count(*) FROM " . DB_PREFIX . "rumors r WHERE r.text LIKE CONCAT('%', last_name, '%') OR r.text LIKE CONCAT('%', namestr, '%')) AS rumor_count, (SELECT avg(r2.rating) FROM " . DB_PREFIX . "rumors r2 WHERE r2.text LIKE CONCAT('%', last_name, '%') OR r2.text LIKE CONCAT('%', namestr, '%')) AS rumor_rating FROM " . DB_PREFIX . "teacher t ORDER BY rumor_count DESC");
        $arr = array();
        if ($res != null) {
            while ($tarr = $res->fetch_array()) {
                $arr[] = $tarr;
                $sum += $tarr["rumor_count"];
            }
            $len = count($arr);
            for ($i = 0; $i < $len; $i++)
                $arr[$i]["perc"] = $arr[$i]["rumor_count"] * 100.0 / $sum;
        }
        return $arr;
    }

    public static function addTeacher($last_name, $is_male, $first_name = "") {
        global $db;
        $last_name = sanitizeInputText($last_name);
        $is_male = intval($is_male);
        $first_name = sanitizeInputText($first_name);
        $res = $db->query("SELECT last_name FROM " . DB_PREFIX . "teacher WHERE last_name='" . $last_name . "' AND ismale=" . $is_male . " AND first_name='" . $first_name . "'") or die($db->error);
        if ($res->num_rows == 0) {
            $namestr = ($is_male ? "Herr " : "Frau ") . $last_name;
            $db->query("INSERT INTO " . DB_PREFIX . "teacher(id, first_name, last_name, namestr, ismale) VALUES(NULL, '" . $first_name . "', '" . $last_name . "', '" . $namestr . "', " . $is_male . ")") or die($db->error);
            $id = $db->insert_id;
            self::updateQuotes($id);
            return true;
        }
        return false;
    }

    public static function updateQuotes($teacher_id = -1) {
        global $db;
        if ($teacher_id != -1) {
            $db->query("UPDATE " . DB_PREFIX . "quotes q, " . DB_PREFIX . "teacher t SET q.teacherid=" . intval($teacher_id) . " WHERE (q.person LIKE t.namestr OR (q.person LIKE CONCAT('%', first_name, '%', last_name))) AND q.teacherid = t.id");
        } else {
            $db->query("UPDATE " . DB_PREFIX . "quotes q, " . DB_PREFIX . "teacher t SET q.teacherid=t.id WHERE (q.person LIKE t.namestr OR (q.person LIKE CONCAT('%', first_name, '%', last_name))) AND q.teacherid = t.id");
        }
    }

    public static function readTeacherListInput($text) {
        if ($text != "") {
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line == "") {
                    continue;
                }
                $arr = array_map("trim", explode(",", str_replace('  ', '', $line)));
                $last = $arr[count($arr) - 1];
                $first_name = "";
                if ($last == "m" || $last == "w") {
                    $ismale = $last == "m";
                } else if (count($arr) == 1) {
                    $a = explode(" ", $last);
                    $c = count($a);
                    if ($c == 2 || $c == 3) {
                        $ismale = substr($last, 0, 1) == "m";
                        $last_name = $a[$c - 1];
                        if ($c == 3) {
                            $first_name = $a[1];
                        }
                    } else {
                        continue;
                    }
                } else if (count($arr) == 2) {
                    $first_name = $arr[0];
                }
                if (preg_match('/^(Frau).+/', $line)) {
                    $ismale = 0;
                } else if (preg_match('/^(Herr).+/', $line)) {
                    $ismale = 1;
                }
                self::addTeacher($last_name, $ismale, $first_name);
            }
        }
    }

    public static function delete($id) {
        global $db;
        $db->query("DELETE FROM " . DB_PREFIX . "teacher WHERE id=" . intval($id));
    }

    public static function edit($id, $last_name, $ismale, $first_name = "") {
        $last_name = sanitizeInputText($last_name);
        $first_name = sanitizeInputText($first_name);
        $ismale = is_numeric($ismale) ? intval($ismale) : ($ismale == "m" ? 1 : 0);
        global $db;
        $db->query("UPDATE " . DB_PREFIX . "teacher SET last_name='" . $last_name . "', ismale=" . $ismale . ", first_name='" . $first_name . "', namestr='" . ($ismale == 1 ? "Herr " : "Frau ") . $last_name . "' WHERE id=" . intval($id)) or die($db->error);
    }

    public function getID() {
        return $this->id;
    }

    public function isMale() {
        return $this->is_male;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function getNameStr() {
        return $this->name_str;
    }

}