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

    /**
     *
     * @var int 
     */
    private $id;

    /**
     *
     * @var int
     */
    private $type;

    /**
     *
     * @var String
     */
    private $question;

    /**
     *
     * @var position 
     */
    private $position;

    /**
     *
     * @var array
     */
    private $data;

    /**
     * @var int|String
     */
    private $user_answer;

    /**
     * 
     * @param int $id
     * @param int $type
     * @param String $question
     * @param int $position
     * @param array $data
     * @param int|String $user_answer
     */
    public function __construct($id, $type, $question, $position, $data = array(), $user_answer = "") {
        $this->id = intval($id);
        $this->type = intval($type);
        $this->question = $question;
        $this->position = $position;
        $this->data = $data;
        $this->user_answer = $user_answer;
    }

    /**
     * 
     * @param array $array
     * @return null|\Poll
     */
    public static function getFromArray($array) {
        if ($array == null || !isset($array["id"]) || $array["id"] == "") {
            return null;
        }
        return new Poll($array["id"], $array["type"], $array["question"], $array["position"], isset($array["data"]) ? $array["data"] : array(), isset($array["user_answer"]) ? $array["user_answer"] : "");
    }

    /**
     * 
     * @param mysqli_result $mysql_result
     * @return null|\Poll
     */
    public static function getFromMySQLResult($mysql_result) {
        if ($mysql_result == null) {
            return null;
        }
        $res = $mysql_result->fetch_array();
        if ($res == null) {
            return null;
        }
        $res["data"] = json_decode($res["data"], true);
        if (!isset($res["user_answer"])) {
            $res["user_answer"] = "";
        }
        return self::getFromArray($res);
    }

    /**
     * 
     * @global mysqli $db
     * @param int $id
     * @return null|\Poll
     */
    public static function getByID($id) {
        global $db;
        return Poll::getFromMySQLResult($db->query("SELECT * , (SELECT answer FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=id AND userid=" . Auth::getUserID() . ") AS user_answer FROM " . POLLS_TABLE . " WHERE id=" . intval($id)));
    }

    /**
     * 
     * @global mysqli $db
     * @param int $type
     * @return \Poll[]
     */
    public static function getByType($type) {
        global $db;
        $res = $db->query("SELECT * FROM " . POLLS_TABLE . " WHERE type=" . intval($type) . " ORDER BY position ASC");
        $retarr = array();
        while ($poll = Poll::getFromMySQLResult($res)) {
            $retarr[] = $poll;
            $res2 = mysqliResultToArr($db->query("SELECT answer FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=" . $poll->id . " AND userid=" . Auth::getUserID()), true);
            $poll->user_answer = $res2["answer"];
        }
        return $retarr;
    }

    /**
     * 
     * @return array
     */
    public static function getAll() {
        $arr = array();
        $arr[self::USER_TYPE] = self::getByType(self::USER_TYPE);
        $arr[self::TEACHER_TYPE] = self::getByType(self::TEACHER_TYPE);
        $arr[self::NUMBER_TYPE] = self::getByType(self::NUMBER_TYPE);
        return $arr;
    }

    public static function updateAll() {
        global $db;
        $res = $db->query("SELECT * FROM " . POLLS_TABLE);
        while ($poll = Poll::getFromMySQLResult($res)) {
            $poll->updateData();
            $poll->updateDB();
        }
    }

    /**
     * 
     * @global mysqli $db
     * @param int $type
     * @param String $question
     * @param int $position
     * @param array $data
     * @return \Poll
     */
    public static function create($type, $question, $position = -1, $data = array()) {
        global $db;
        $type = intval($type);
        $question = sanitizeInputText($question);
        $position = intval($position);
        $data = json_encode($data);
        if ($position == -1) {
            $arr = mysqliResultToArr($db->query("SELECT MAX(position) as 'max' FROM " . POLLS_TABLE . " WHERE type=" . $type . " GROUP BY type"), true);
            if (count($arr) == 0) {
                $position = 0;
            } else {
                $position = $arr["max"] + 1;
            }
        }
        $db->query("INSERT INTO " . POLLS_TABLE . "(id, type, question, position, data) VALUES(NULL, $type, '$question', " . $position . ", '$data')");
        $id = $db->insert_id;
        return new Poll($id, $type, $question, $position, $data);
    }

    public static function createFromText($type, $text) {
        $arr = explode("\n", str_replace("\r\n", "\n", $text));
        foreach ($arr as $line) {
            $line = trim($line);
            if ($line != "") {
                self::create($type, trim($line));
            }
        }
    }

    public function delete() {
        global $db;
        $db->query("DELETE FROM " . POLLS_TABLE . " WHERE id=" . $this->id);
        $db->query("DELETE FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=" . $this->id);
    }

    /**
     * 
     * @global mysqli $db
     * @global Environment $env
     */
    public function updateData() {
        global $db, $env;
        $data = mysqliResultToArr($db->query("SELECT count(*) as count, answer FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=" . $this->id . " GROUP BY answer ORDER BY count DESC LIMIT 0," . $env->userpolls_result_length));
        $countArr = mysqliResultToArr($db->query("SELECT count(*) as count FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=" . $this->id), true);
        $this->updateDataArr($data, intval($countArr["count"]));
    }

    /**
     * 
     * @param array $data
     */
    private function updateDataArr($data, $numberOfEntries = 0) {
        $sum = 0;
        $numberOfListedEntries = 0;
        if ($this->type != self::NUMBER_TYPE && $numberOfEntries == 0) {
            for ($index = 0; $index < count($data); $index++) {
                $numberOfEntries += $data[$index]["count"];
            }
        } else if ($this->type == self::NUMBER_TYPE) {
            foreach ($data as $arr) {
                $sum += $arr["answer"] * $arr["count"];
                $numberOfListedEntries += $arr["count"];
            }
        }
        for ($index = 0; $index < count($data); $index++) {
            $arr = $data[$index];
            $arr["perc"] = round($arr["count"] / $numberOfEntries * 100, 3);
            $arr["answer"] = intval($arr["answer"]);
            $data[$index] = $arr;
        }
        $this->data = array("answers" => $data, "avg" => round($sum / $numberOfListedEntries, 3), "number_of_answers" => $numberOfEntries);
    }

    /**
     * 
     * @global mysqli $db
     */
    public function updateDB() {
        global $db;
        $db->query("UPDATE " . POLLS_TABLE . " SET type=" . intval($this->type) . ", question='" . sanitizeInputText($this->question) . "', position=" . intval($this->position) . ", data='" . json_encode($this->data) . "' WHERE id=" . $this->id);
    }

    /**
     * 
     * @global mysqli $db
     * @param int|String $answer
     * @return boolean
     */
    public function submitAnswer($answer) {
        if ($answer == "") {
            return;
        }
        $canswer = "";
        switch ($this->type) {
            case self::TEACHER_TYPE:
                if (is_numeric($answer)) {
                    $canswer = intval($answer);
                } else {
                    $teacher = Teacher::getByName($answer);
                    if ($teacher != null) {
                        $canswer = $teacher->getID();
                    } else {
                        return false;
                    }
                }
                break;
            case self::USER_TYPE:
                if (is_numeric($answer)) {
                    $canswer = intval($answer);
                } else {
                    $user = User::getByName($answer);
                    if ($user != null) {
                        $canswer = $user->getID();
                    } else {
                        return false;
                    }
                }
                break;
            case self::NUMBER_TYPE:
                $canswer = floatval($answer);
                break;
        }
        if ($canswer !== "") {
            global $db;
            $res = $db->query("SELECT * FROM " . POLL_ANSWERS_TABLE . " WHERE pollid=" . $this->id . " AND userid=" . Auth::getUserID());
            if ($res && $res->fetch_array()) {
                $db->query("UPDATE " . POLL_ANSWERS_TABLE . " SET answer='" . $canswer . "' WHERE pollid=" . $this->id . " AND userid=" . Auth::getUserID());
            } else {
                $db->query("INSERT INTO " . POLL_ANSWERS_TABLE . "(userid, pollid, type, answer) VALUES(" . Auth::getUserID() . ", " . $this->id . ", " . $this->type . ", '" . $canswer . "')");
            }
            $this->updateData();
            $this->updateDB();
        }
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
    public function getType() {
        return $this->type;
    }

    /**
     * 
     * @return String
     */
    public function getTypeString() {
        return self::getStringRepOfType($this->type);
    }

    /**
     * 
     * @return String
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * 
     * @param String $question
     * @return \Poll
     */
    public function setQuestion($question) {
        $this->question = $question;
        return $this;
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
     * @return \Poll
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * 
     * @return array
     */
    public function getUserAnswerArr() {
        return $this->user_answer;
    }

    /**
     * 
     * @return array
     */
    public function getUserAnswer() {
        return $this->user_answer;
    }

    /**
     * 
     * @param int $type
     * @return string
     */
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

    /**
     * 
     * @return string
     */
    public function getUserAnswerString() {
        return self::answerToString($this->user_answer);
    }

    /**
     * 
     * @param int $number
     * @return string
     */
    public function answerToString($number) {
        switch ($this->type) {
            case Poll::TEACHER_TYPE:
                $teacher = Teacher::getByID($number);
                if ($teacher instanceof Teacher) {
                    return $teacher->getName();
                }
            case Poll::USER_TYPE:
                $user = User::getByID($number);
                if ($user instanceof User) {
                    return $user->getName();
                }
            case Poll::NUMBER_TYPE:
                return $number;
        }
        return "";
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return "ID: " + $this->id + "; Type: " + $this->getTypeString() + "; Text: '" + $this->getQuestion() + "'";
    }

}

?>
