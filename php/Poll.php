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

class Poll {

    const USER_TYPE = 1;
    const TEACHER_TYPE = 2;
    const NUMBER_TYPE = 3;

    private $id;
    private $type;
    private $question;
    private $position;
    private $data;
    private $user_answer;
    private static $table;
    private static $answer_table;

    public function __construct($id, $type, $question, $position, $data = array(), $user_answer = "") {
        $this->id = intval($id);
        $this->type = intval($type);
        $this->question = $question;
        $this->position = $position;
        $this->data = $data;
        $this->user_answer = $user_answer;
        self::$table = DB_PREFIX . "polls";
        self::$answer_table = DB_PREFIX . "poll_answers";
    }

    public static function getFromArray($array) {
        if ($array == null || !isset($array["id"]) || $array["id"] == "") {
            return null;
        }
        return new Poll($array["id"], $array["type"], $array["question"], $array["position"], isset($array["data"]) ? $array["data"] : array(), isset($array["user_answer"]) ? $array["user_answer"] : "");
    }

    public static function getFromMySQLResult($mysql_result) {
        if ($mysql_result == null) {
            return null;
        }
        $res = $mysql_result->fetch_array();
        if ($res == null) {
            return null;
        }
        $res["data"] = json_decode($res["data"], false);
        return self::getFromArray($res);
    }

    public static function getByID($id) {
        global $db;
        return Poll::getFromMySQLResult($db->query("SELECT * , (SELECT answer FROM " . self::$answer_table . " WHERE pollid=id AND userid=" . Auth::getUserID() . ") AS user_answer FROM " . self::$table . " WHERE id=" . intval($id)));
    }

    public static function getByType($type) {
        global $db;
        $res = $db->query("SELECT *, (SELECT answer FROM " . self::$answer_table . " WHERE pollid=id AND userid=" . Auth::getUserID() . ") AS user_answer FROM " . self::$table . " WHERE type=" . intval($type) . " ORDER BY position ASC");
        $retarr = array();
        while ($poll = Poll::getFromMySQLResult($res)) {
            $retarr[] = $poll;
        }
        return User::getFromMySQLResult($res);
    }

    public static function getAll() {
        $arr = array();
        $arr[self::TEACHER_TYPE] = self::getByType(self::TEACHER_TYPE);
        $arr[self::USER_TYPE] = self::getByType(self::USER_TYPE);
        $arr[self::NUMBER_TYPE] = self::getByType(self::NUMBER_TYPE);
        return $arr;
    }

    public static function create($type, $question, $position, $data = array()) {
        global $db;
        $type = intval($type);
        $question = cleanInputText($question);
        $position = intval($position);
        $data = json_encode($data);
        $db->query("INSERT INTO " . self::$table . "(id, type, question, position, data) VALUES(NULL, $type, '$question', $position, '$data')");
        $id = $db->insert_id;
        return new Poll($id, $type, $question, $position, $data);
    }

    public function delete() {
        global $db;
        $db->query("DELETE FROM " . self::$table . " WHERE id=" . $this->id);
        $db->query("DELETE FROM " . self::$answer_table . " WHERE pollid=" . $this->id);
    }

    public function updateData() {
        global $db, $env;
        $data = mysqliResultToArr($db->query("SELECT count(*) as count, answer FROM " . self::$answer_table . " WHERE pollid=" . $this->id . " GROUP BY answer ORDER BY count DESC LIMIT 0," . $env->userpolls_result_length));
        $this->updateDataArr($data);
    }

    private function updateDataArr($data) {
        $sum = 0;
        for ($index = 0; $index < count($data); $index++)
            $sum += $data[$index]["count"];
        for ($index = 0; $index < count($data); $index++) {
            $arr &= $data[$index];
            $arr["perc"] = round($arr["count"] / $sum * 100, 3);
            $arr["answer"] = intval($arr["answer"]);
        }
        $this->data = array("answers" => $arr, "avg" => round($sum / count($data), 3));
    }

    public function updateDB() {
        global $db;
        $db->query("UPDATE " . self::$table . " SET type=" . intval($this->type) . ", question='" . cleanInputText($this->question) . "', position=" . intval($this->position) . ", date='" . json_encode($data) . "') WHERE id=" . $this->id);
    }

    public function submitAnswer($answer) {
        $canswer = "";
        switch ($this->type) {
            case self::TEACHER_TYPE:
                $canswer = is_numeric($answer) ? intval($answer) : Teacher::getByName($answer)->getID();
                break;
            case self::USER_TYPE:
                $canswer = is_numeric($answer) ? intval($answer) : User::getByName($answer)->getID();
                break;
            case self::NUMBER_TYPE:
                $canswer = intval($answer);
                break;
        }
        if ($canswer !== "") {
            global $db;
            $res = $db->query("SELECT rating FROM " . self::$answer_table . " WHERE pollid=" . $this->id . " AND userid=" . Auth::getUserID());
            if ($res && $res->fetch_array()) {
                $db->query("UPDATE " . self::$answer_table . " SET answer='" . $canswer . "' WHERE pollid=" . $this->id . " AND userid=" . Auth::getUserID());
            } else {
                $db->query("INSERT INTO " . self::$answer_table . "(userid, pollid, type, answer) VALUES(" . Auth::getUserID() . ", " . $this->id . ", " . $this->type . ", '" . $canswer . "')");
            }
            $this->updateData();
            $this->updateDB();
        }
    }

    public function getID() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function setQuestion($question) {
        $this->question = $question;
        return $this;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function getUserAnswer() {
        return $this->user_answer;
    }

    public static function getStringRepOfType($type) {
        if (is_int($type)) {
            switch ($type) {
                case self::TEACHER_TYPE:
                    return "Lehrer";
                case self::USER_TYPE:
                    return "Schüler";
                default:
                    return "Weitere";
            }
        }
        return $type;
    }

}

?>
