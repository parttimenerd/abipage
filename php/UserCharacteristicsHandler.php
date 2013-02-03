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

class UserCharacteristicsHandler extends ToroHandler {

    public function get($slug = "") {
        if ($slug == "") {
            if (Auth::isViewingResults()) {
                tpl_usercharacteristics_result_page(Auth::getUser(), UserCharacteristicsItem::getAll(Auth::getUser()));
            } else {
                tpl_usercharacteristics_answer_page(UserCharacteristicsTopic::getAll());
            }
        }
    }

    public function post($slug = "") {
        if (isset($_POST["submit"])) {
            foreach ($_POST as $key => $value) {
                if (is_numeric($key)) {
                    $topic = UserCharacteristicsTopic::getByID($key);
                    if ($topic) {
                        $topic->submit($value);
                    }
                }
            }
            foreach ($_FILES as $key => $value) {
                if (is_numeric($key)) {
                    $topic = UserCharacteristicsTopic::getByID($key);
                    if ($topic && $value != "") {
                        $topic->submitPicture($key);
                    }
                }
            }
        }
        $this->get($slug);
    }

}