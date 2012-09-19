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

class DebugDBWrapper {
    private $db;
    private $count = 0;
    private $time = 0;
    private $queries = array();
    
    public function __construct($db){
        $this->db = $db;
    }
    
    public function __call($func, $args){
        return call_user_func_array(array($this->db, $func), $args);
    }
    
    public function __get($var){
        return $this->db->{$var};
    }
    
    public function query($query){
        $this->count++;
        $time = microtime(true);
        $res = $this->db->query($query) or Logger::log($this->db->error, E_USER_ERROR);
        $time = round(microtime(true) - $time, 6);
        $this->queries[] = array("num" => count($this->queries), "query" => $query, "time" => $time, "backtrace" => Logger::debug_backtrace(1));
        $this->time += $time;
        return $res;
    }
    
    public function getQueries(){
        $queries = array();
        foreach ($this->queries as $key => $value) {
            $queries[] = array_merge($value, array("perc" => round($value["time"] / $this->time * 100, 3)));
        }
        return array("time" => $this->time, "queries" => $queries);
    }
    
    public function getTime(){
        return $this->time;
    }
}
?>