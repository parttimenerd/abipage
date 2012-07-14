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

class TeacherList {

    public static function getTeacherNameList() {
        global $db;
        $res = $db->query("SELECT namestr FROM " . DB_PREFIX . "teacher ORDER BY last_name ASC") or die($db->error);
        $arr = array();
        if ($res != null) {
            while ($tarr = $res->fetch_array()) {
                $arr[] = $tarr["namestr"];
            }
        }
        return $arr;
    }

    public static function getTeacher() {
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

    public static function getTeacherWithQuoteRatingAndCount() {
        global $db;
        $res = $db->query("SELECT id, first_name, last_name, namestr, ismale, (SELECT count(*) AS quote_count, avg(rating) AS quote_rating FROM " . DB_PREFIX . "quotes q WHERE q.id = t.id) FROM " . DB_PREFIX . "teacher t ORDER BY quote_count DESC");
        $arr = array();
        if ($res != null) {
            while ($tarr = $res->fetch_array()) {
                $arr[] = $tarr;
            }
        }
        return $arr;
    }

    public static function addTeacher($last_name, $is_male, $first_name = "") {
        global $db;
        $last_name = cleanInputText($last_name);
        $is_male = intval($is_male);
        $first_name = cleanInputText($first_name);
        $res = $db->query("SELECT last_name FROM " . DB_PREFIX . "teacher WHERE last_name='" . $last_name . "' AND ismale=" . $is_male . " AND first_name='" . $first_name . "'") or die($db->error);
        if ($res->num_rows == 0) {
            $namestr = ($is_male ? "Herr " : "Frau ") . $last_name;
            $db->query("INSERT INTO " . DB_PREFIX . "teacher(id, first_name, last_name, namestr, ismale) VALUES(NULL, '" . $first_name . "', '" . $last_name . "', '" . $namestr . "', " . $is_male . ")") or die($db->error);
            $id = $db->insert_id;
            $db->query("UPDATE " . DB_PREFIX . "quotes SET teacherid=" . $id . " WHERE person LIKE '" . $namestr . "' AND teacherid=-1");
            return true;
        }
        return false;
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
        $last_name = cleanInputText($last_name);
        $first_name = cleanInputText($first_name);
        $ismale = is_numeric($ismale) ? intval($ismale) : ($ismale == "m" ? 1 : 0);
        global $db;
        $db->query("UPDATE " . DB_PREFIX . "teacher SET last_name='" . $last_name . "', ismale=" . $ismale . ", first_name='" . $first_name . "', namestr='" . ($ismale == 1 ? "Herr " : "Frau ") . $last_name . "' WHERE id=" . intval($id)) or die($db->error);
    }

}