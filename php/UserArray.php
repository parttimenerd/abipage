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

/**
 * Description of UserArray
 *
 * @author Johannes
 */
class UserArray implements ArrayAccess, Countable {

    private $container = array();

    public function __construct($userarr = array()) {
        $this->add($userarr);
    }

    public function add($userarr) {
        if (!$userarr) {
            return;
        }
        if (is_a($userarr, "User")) {
            $this->container[$userarr->getName()] = $user;
        } else if (is_a($userarr, "UserArray")) {
            foreach ($userarr->getContainer() as $key => $value)
                $this[$key] = $value;
        } else if (is_array($userarr)) {
            foreach ($userarr as $user)
                $this[$user->getName()] = $user;
        }
    }

    public function count() {
        return count($this->container);
    }

    public function __clone() {
        foreach ($this->container as $key => $value)
            if ($value instanceof self)
                $this[$key] = clone $value;
    }

    public function __invoke($userarr) {
        return $this($userarr);
    }

    public function __call($func, $args = null) {
        $retarr = array();
        foreach ($this->container as $key => $user) {
            $res = $user->{$func}($args);
            if ($res != null)
                $retarr[$key] = $res;
        }
        return empty($retarr) ? $this : $retarr;
    }

    public function each($func) {
        $retarr = array();
        foreach ($this->container as $key => $user) {
            $res = $func($user);
            if ($res != null)
                $retarr[$key] = $res;
        }
        return empty($retarr) ? $this : $retarr;
    }

    public function offsetSet($offset, $container) {
        if (is_array($container))
            $container = new self($container);
        if ($offset === null) {
            $this->container[] = $container;
        } else {
            $this->container[$offset] = $container;
        }
    }

    public function toArray() {
        $container = $this->container;
        foreach ($container as $key => $value)
            if ($value instanceof self)
                $container[$key] = $value->toArray();
        return $container;
    }

    public function offsetGet($offset) {
        return $this->container[$offset];
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function getContainer() {
        return $this->container;
    }

}

?>
