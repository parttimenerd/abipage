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

class PollsHandler extends ToroHandler {

    public function get($slug = "") {
        if ($slug === "/edit" && Auth::canEditUserPolls()) {
            tpl_edit_polls(Poll::getAll());
        } else {
            tpl_polls(Poll::getAll());
        }
    }

    public function get_result($slug = "") {
        if ($slug != "/edit") {
            tpl_poll_results(Poll::getAll());
        } else {
            $this->get($slug);
        }
    }

    public function post($slug = "") {
        if ($slug === "/edit" && Auth::canEditUserPolls() && isset($_POST["edit"])) {
            foreach ($_POST as $key => $value) {
                if (preg_match("/[0-9]+$/", $key)) {
                    switch ($_POST[$key . "_action"]) {
                        case "add":
                            Poll::create($_POST[$key . "_type"], $_POST[$key . "_question"], $_POST[$key . "_position"]);
                            break;
                        case "delete":
                            $poll = Poll::getByID($key);
                            if ($poll != null)
                                $poll->delete();
                            break;
                        case "edit":
                            $poll = Poll::getByID($key);
                            if ($poll == null)
                                break;
                            $poll->setPosition($_POST[$key . "_position"])->setQuestion($_POST[$key . "_question"])->updateDB();
                            break;
                    }
                }
            }
        } else if (isset($_POST["submit"])) {
            foreach ($_POST as $key => $value) {
                if (is_numeric($key)) {
                    $poll = Poll::getByID($key);
                    if ($poll)
                        $poll->submitAnswer($value);
                }
            }
        }
        $this->get($slug);
    }

}

?>
