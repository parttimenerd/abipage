<?php

class KeyValueStore {

    private $__dic = array();
    private $__dic_ud = array(); //values belong to the current user
    private $__dic_set = array("norm" => array(), "ud" => array());
    private $changed = false;

    public function __construct() {
        global $db;
        $res = $db->query("SELECT `key`, value, userid FROM " . DB_PREFIX . "keyvaluestore WHERE userid=" . Auth::getUserID() . " OR userid=0") or die($db->error);
        if ($res != null) {
            while ($arr = $res->fetch_array()) {
                $value = $arr["value"];
                if (is_bool($value))
                    $value = $value == "true";
                if ($arr["userid"] != 0) {
                    $this->__dic_ud[$arr["key"]] = $value;
                } else {
                    $this->__dic[$arr["key"]] = $value;
                }
            }
        }
    }

    public function __get($var) {
        list($var, $ud) = $this->getRealVarName($var);
        $arr = $ud ? $this->__dic_ud : $this->__dic;
        if (array_key_exists($var, $arr)) {
            return $arr[$var];
        }
        return null;
    }

    public function __set($var, $value) {
        list($_var, $ud) = $this->getRealVarName($var);
        $set_arr = $this->__dic_set[$var == $_var ? "norm" : "ud"];
        if (isset($set_arr[$_var])) {
            $this->__dic_set[$var == $_var ? "norm" : "ud"][$_var]["action"] = "update";
        } else {
            $this->__dic_set[$var == $_var ? "norm" : "ud"][$_var] = array();
            $this->__dic_set[$var == $_var ? "norm" : "ud"][$_var]["action"] = "insert";
        }
        $this->changed = true;
        if ($ud){
            $this->__dic_ud[$_var] = cleanInputText($value);
        } else {
            $this->__dic[$_var] = cleanInputText($value);
        }
    }

    private function getRealVarName($var) {
        if (substr($var, strlen($var) - 3) == "_ud") {
            $ud = true;
            $var = substr($var, 0, strlen($var) - 3);
        } else {
            $ud = false;
        }
        return array($var, $ud);
    }

    public function updateDB() {
        if ($this->changed) {
            $uid = Auth::getUserID();
            $arr = $this->__dic_set["norm"];
            foreach ($this->__dic as $key => $value) {
                if (isset($arr[$key])){
                    $this->writeValueIntoDB($key, $value, 0, $arr[$key]["action"] == "insert");
                }
            }
            $arr = $this->__dic_set["ud"];
            foreach ($this->__dic_ud as $key => $value) {
                if (isset($arr[$key])){
                    $this->writeValueIntoDB($key, $value, $uid, $arr[$key] == "insert");
                }
            }
        }
    }

    private function writeValueIntoDB($key, $value, $userid = 0, $insert = true) {
        global $db;
        //$res = $db->query("SELECT * FROM " . DB_PREFIX . "keyvaluestore WHERE `key`='" . $key . "' AND userid=" . intval($userid));
        if (!$insert) {
            $db->query("UPDATE " . DB_PREFIX . "keyvaluestore SET value='" . $db->real_escape_string($value["default"]) . "' WHERE `key`='" . $key . "' AND userid=" . intval($userid)) or die($db->error);
        } else {
            $db->query("INSERT INTO " . DB_PREFIX . "keyvaluestore(`key`, value, userid) VALUES('$key', '" . $db->real_escape_string($value["default"]) . "', " . intval($userid) . ")") or die($db->error);
        }
    }

}