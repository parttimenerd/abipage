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

class RatableUserContentListItem {
   
    private $props = array();
    public $responses = array();
    
    public function __construct($props) {
        $this->props = $props;
        if ($props["responses"])
            $this->responses &= $props["responses"];
    }
    
    public function __get($var){
        return $this->props[$var];
    }
    
    public function canHaveResponses(){
        global $env;
        return $env->responses_allowed && isset($this->props["response_to"]);
    }
    
    public function isAnonymous(){
        return $this->props["isanonymous"] && !Auth::canSeeNameWhenSentAnonymous();
    }
    
    public function isDeletable(){
        return Auth::canDeleteRucItem();
    }
}
?>
