<?php

class KeyValueStore {
	
	private $__dic = array();
	private $__dic_ud = array(); //values belong to the current user
	private $changed = false;
	
	public function __construct(){
		global $db;
		$res = $db->query("SELECT `key`, value, userid FROM " . DB_PREFIX . "keyvaluestore WHERE userid=" . Auth::getUserID() . " OR userid=0");
		if ($res){
			while($arr = $res->fetch_array()){
				if ($arr["userid"] != 0){
					$this->__dic_ud[$arr["key"]] = $arr["value"];
				} else {
					$this->__dic[$arr["key"]] = $arr["value"];
				}
			}
		}
	}
	
	public function __get($var){
		list($var, $arr) = $this->getVarNameAndArr($var);
		if (array_key_exists($var, $this->__dic)){
			return $arr[$var];
		}
		return null;
	}
	
	public function __set($var, $value){
		list($var, $arr) = $this->getVarNameAndArr($var);
		$arr[$var] = cleanInputText($value);
	}
	
	private function getVarNameAndArr($varname){
		$arr;
		if (substr($var, strlen($var) - 3) == "_ud"){
			$arr = $this->__dic_ud;
			$var = substr($var, 0, strlen($var) - 3);
		} else {
			$arr = $this->__dic;
		}
		return array($var, $arr);
	}
	
	public function updateDB(){
		if ($this->changed){
			global $db;
			$uid = Auth::getUserID();
			foreach ($this->__dic as $key => $value) {
			   $this->writeValueIntoDB($key, $value);
			}
			foreach ($this->__dic_ud as $key => $value) {
			   $this->writeValueIntoDB($key, $value, $uid);
			}
		}
	}
	
	private function writeValueIntoDB($key, $value, $userid = 0){
		$res = $db->query("SELECT * FROM " . DB_PREFIX . "keyvaluestore WHERE `key`='" . $key . "' AND userid=" . intval($userid));
		if ($res->num_rows > 0) {
			$db->query("UPDATE " . DB_PREFIX . "keyvaluestore SET value='" . $db->real_escape_string($value["default"]) . "' WHERE `key`='" . $key . "' AND userid=" . intval($userid));
		} else {
			$db->query("INSERT INTO " . DB_PREFIX . "keyvaluestore(`key`, value, userid) VALUES('$key', '" . $db->real_escape_string($value["default"]) . "', " . intval($userid) . ")");
		}
	}
}