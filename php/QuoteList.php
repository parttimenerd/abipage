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

class QuoteList extends RatableUserContentList {

    public function __construct() {
        parent::__construct("quotes", true);
    }

    public function addQuote($person, $text, $anonymous, $response_to = -1, $teacherid = -1, $senduser = null, $time = -1) {
        global $env;
        if ($senduser == null) {
            $senduser = Auth::getUser();
        }
        if ($time == -1) {
            $time = time();
        }
        $tid = intval($teacherid);
        $person = cleanInputText($person, $this->db);
        $text = cleanInputText($text, $this->db);
        if ($tid == -1) {
            $res = $this->db->query("SELECT id FROM " . DB_PREFIX . "teacher WHERE namestr LIKE '%" . $person . "'");
            if ($res) {
                $arr = $res->fetch_array();
                if ($arr) {
                    $tid = $arr["id"];
                }
            }
        }
        $name = $person;
        if ($tid != -1) {
            $res = $this->db->query("SELECT name_str FROM " . DB_PREFIX . "teacher WHERE id=" . $tid);
            if ($res) {
                $arr = $res->fetch_array();
                if ($arr) {
                    $name = $arr["name_str"];
                }
            }
        }
        $this->db->query("INSERT INTO " . $this->table . "(id, person, teacherid, text, userid, isanonymous, time, rating, rating_count, response_to, data) VALUES(NULL, '" . $person . "', " . $tid . ", '" . $text . "', " . $senduser->getID() . ", " . ($anonymous ? 1 : 0) . ", " . intval($time) . ", 0, 0, " . intval($response_to) . ", '')");
        $env->addAction($this->db->insert_id, $name, "add_quote");
    }

}