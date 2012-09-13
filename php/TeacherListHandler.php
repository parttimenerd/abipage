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

class TeacherListHandler extends ToroHandler {

    public function get() {
        tpl_teacherlist(TeacherList::getTeacher());
    }

    public function post() {          
        if ((isset($_POST["edit"]) || isset($_POST["delete"])) && (Auth::canEditTeacher() || Auth::canDeleteTeacher())) {
            foreach ($_POST as $key => $value) {
                if (preg_match("/^[0-9]+$/", $key)) {
                    if (isset($_POST["edit"]) && isset($_POST[$key . "last_name"]) &&
                            isset($_POST[$key . "first_name"]) && isset($_POST[$key . "sex"])) {
                        TeacherList::edit($_POST[$key], $_POST[$key . "last_name"], $_POST[$key . "sex"], $_POST[$key . "first_name"]);
                    } else if (isset($_POST["delete"]) && Auth::canDeleteTeacher()) {
                        TeacherList::delete($key);
                    }
                }
            }
            TeacherList::updateQuotes();
        }
        if (isset($_POST["add"]) && isset($_POST["input"]) && Auth::canAddTeacher()) {
            TeacherList::readTeacherListInput(cleanInputText($_POST["input"]));
        }
        return $this->get();
    }

}