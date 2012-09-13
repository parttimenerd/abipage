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
    private $buffer = "";
    private $count = 0;
    private $time = 0;
    
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
        $res = $this->db->query($query) or die($this->db->error . "<br/>\n " . debug_print_backtrace());
        $this->addMsg($this->count . ". query ", $query, microtime(true) - $time); 
        return $res;
    }
    
    private function addMsg($title, $msg, $time){
        $this->time += $time;
        $this->buffer .= ($this->buffer != "" ? "\n" : "") . '<tr><td>' . $title .  "</td><td>" . (round($time * 10000) / 10) . "ms</td><td>" . $msg . "</td></tr>";
    }
    
    public function printBuffer(){
        $this->buffer .= ($this->buffer != "" ? "\n" : "") . "<td>Summe der Queries</td><td>" . (round($this->time * 10000) / 10) . "ms</td><td></td>";
        ?>
        <table>
            <?= $this->buffer ?>
        </table>
        <?
    }
}
?>