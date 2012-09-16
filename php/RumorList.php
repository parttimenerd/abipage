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

class RumorList extends RatableUserContentList {

    public function __construct() {
        parent::__construct("rumors", true);
    }

    public function addRumor($text, $anonymous, $response_to = -1, $senduser = null, $time = -1) {
        global $env;
        if ($senduser == null) {
            $senduser = Auth::getUser();
        }
        if ($time == -1) {
            $time = time();
        }
        $text = cleanInputText($text, $this->db);
        PiwikHelper::addTrackGoalJS("Rumor written");
        $this->db->query("INSERT INTO " . $this->table . "(id, text, userid, isanonymous, time, rating, response_to, data) VALUES(NULL, '" . $text . "', " . $senduser->getID() . ", " . ($anonymous ? 1 : 0) . ", " . intval($time) . ", 0, " . intval($response_to) . ", '')") or die($this->db->error);
        $id = $this->db->insert_id;
        Actions::addAction($id, $senduser->getName(), "add_rumor");
        return $id;
    }

    protected function appendSearchAfterPhraseImpl($cphrase) {
        $this->appendToWhereApp(" AND (MATCH(text) AGAINST('" . $cphrase . "') OR text LIKE '%" . $cphrase . "%')");
    }

}
