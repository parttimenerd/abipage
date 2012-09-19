<?php

/*
 * Copyright (C) 2012 Johannes
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

class RatableUserContentItem {

    private $props = array();
    public $responses = array();

    public function __construct($props) {
        $this->props = $props;
        if (isset($props["responses"]) && $props["responses"])
            $this->responses &= $props["responses"];
    }

    public function __get($var) {
        return $this->props[$var];
    }

    public function canHaveResponses() {
        global $env;
        return $env->response_allowed && isset($this->props["response_to"]) && $this->props["response_to"] == -1;
    }

    public function canHaveResponsesButton() {
        global $env;
        return $env->response_allowed && isset($this->props["response_to"]) && ($this->props["response_to"] == -1 || isset($this->props["make_response_to"]));
    }

    public function isAnonymous() {
        return isset($this->props["isanonymous"]) ? ($this->props["isanonymous"] && !Auth::canSeeNameWhenSentAnonymous()) : false;
    }

    public function isDeletable() {
        return Auth::canDeleteRucItem();
    }

    public function hasPersonVal() {
        return isset($this->props["person"]);
    }

    public function getTplFunctionName() {
        return "tpl_" . $this->type . "_item";
    }

    public function getMakeResponseToID() {
        return isset($this->props["make_response_to"]) ? $this->props["make_response_to"] : $this->props["id"];
    }

    public function setMakeResponseToID($id) {
        $this->props["make_response_to"] = $id;
    }

    public function isResponse() {
        global $env;
        return $env->response_allowed && isset($this->props["response_to"]) && $this->props["response_to"] != -1;
    }

    public function __isset($name) {
        return isset($this->props[$name]);
    }

}

?>
