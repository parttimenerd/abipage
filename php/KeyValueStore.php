<?php

class KeyValueStore {

    //ud = values belong to the current user
    private $__dic = array("common" => array(), "ud" => array());
    private $insertarr = array("common" => true, "ud" => true);
    private $updatearr = array("common" => false, "ud" => false);

    public function __construct() {
        global $db;
        $res = $db->query("SELECT `key`, value, userid FROM " . DB_PREFIX . "keyvaluestore WHERE (userid=" . Auth::getUserID() . " AND `key`=" . Auth::getUserID() . ") OR (userid=0 AND `key`='common')") or die($db->error);
        if ($res != null) {
            while ($arr = $res->fetch_array()) {
                if ($arr["key"] == "common") {
                    $this->__dic["common"] = (array) json_decode($arr["value"]);
                    $this->insertarr["common"] = false;
                } else {
                    $this->__dic["ud"] = (array) json_decode($arr["value"]);
                    $this->insertarr["ud"] = false;
                }
            }
        }
    }

    public function __get($var) {
        list($var, $ud) = $this->getRealVarName($var);
        $key = $ud ? "ud" : "common";
        if (array_key_exists($var, $this->__dic[$key])) {
            return $this->__dic[$key][$var];
        }
        return null;
    }

    public function __set($var, $value) {
        list($_var, $ud) = $this->getRealVarName($var);
        $key = !$ud ? "common" : "ud";
        if (isset($this->__dic[$key][$_var])) {
            $this->updatearr[$key] = true;
        } else {
            $this->updatearr[$key] = true;
            $this->insertarr[$key] = true;
        }
        $this->__dic[$key][$_var] = $value;
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
        global $db;
        $uid = Auth::getUserID();
        foreach (array("common", "ud") as $key) {
            if ($this->insertarr[$key]) {
                $db->query("INSERT INTO " . DB_PREFIX . "keyvaluestore(`key`, value, userid) VALUES('" . ($key == "ud" ? $uid : "common") . "', '" . json_encode($this->__dic[$key]) . "', " . ($key == "ud" ? $uid : 0) . ")") or die($db->error);
            } else if ($this->updatearr[$key]) {
                $db->query("UPDATE " . DB_PREFIX . "keyvaluestore SET value='" . json_encode($this->__dic[$key]) . "' WHERE `key`='" . ($key == "ud" ? $uid : "common") . "' AND userid=" . ($key == "ud" ? $uid : 0)) or die($db->error);
            }
        }
    }

}