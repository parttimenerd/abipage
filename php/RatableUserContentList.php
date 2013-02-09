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

abstract class RatableUserContentList {

    protected $table = "";
    protected $type = "";
    protected $db = "";
    private $count = -1;
    protected $where_app = "";
    protected $from_app = "";
    protected $items_per_page;
    protected $response_allowed;
    protected $has_anonymous_field;
    protected $order_by;
    protected $order_direction;
    protected $order_by_dic = array(
        "user" => "u.id",
        "rating",
        "own_rating",
        "rating_count",
        "time"
    );
    protected $start = 0;

    /** $fromapp = ", table"; $where_app = "AND id=0" */
    public function __construct($table, $response_allowed = false, $has_anonymous_field = true, $order_by = "time", $order_direction = "desc", $from_app = "", $where_app = "") {
        global $env, $db;
        $this->items_per_page = $env->items_per_page;
        $this->table = DB_PREFIX . $table;
        $this->type = substr($table, strlen($table) - 1) == "s" ? substr($table, 0, strlen($table) - 1) : $table;
        $this->db = $db;
        $this->where_app = $where_app;
        $this->from_app = $from_app;
        $this->response_allowed = $response_allowed;
        $this->setOrderBy($order_by);
        $this->setOrderDirection($order_direction);
        $this->has_anonymous_field = $has_anonymous_field;
        if ($this->has_anonymous_field)
            $this->order_by_dic["anonymous"] = 'isanonymous';
    }

    public function getCount() {
        if ($this->count == -1) {
            $res = $this->db->query("SELECT count(t.id) AS count FROM " . $this->table . " t, " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated=1 " . $this->where_app) or die($this->db->error);
            if ($res == null) {
                $this->item_count = 0;
            } else {
                $arr = $res->fetch_array();
                $this->item_count = $arr["count"];
            }
        }
        return intval($this->item_count);
    }

    public function isEmpty() {
        return $this->getCount() == 0;
    }

    public function getPageCount() {
        return ceil($this->getCount() / floatval($this->items_per_page));
    }

    public function getItems($start = -1) {
        if ($start == -1)
            $start = $this->start;
        $user = Auth::getUser();
        $arr = array();
        if ($this->getCount() > 0) {
            if ($start > $this->getCount()) {
                $start = ($this->getPageCount() * $this->items_per_page) - $this->items_per_page;
            }
            $ano_app = !Auth::canSeeNameWhenSentAnonymous() ? ', userid = 0' : '';
            //$start = intval($start) - (intval($start) % $this->items_per_page);
            $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*" . $ano_app . ", (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 " . ($this->response_allowed ? ("AND " . $this->table . ".response_to = -1") : "") . $this->where_app . " ORDER BY " . $this->order_by . " " . $this->order_direction . " LIMIT " . $start . ", " . $this->items_per_page) or die($this->db->error);
            if ($res != null) {
                while ($result = $res->fetch_array()) {
                    if (isset($result["response_to"]))
                        $result["response_to"] = -1;
                    $result["type"] = $this->type;
                    $arr[$result["id"]] = new RatableUserContentItem($result);
                }
            }
            if ($this->response_allowed && !empty($arr)) {
                $str = "";
                foreach ($arr as $ruci)
                    $str .= ($str != "" ? ", " : "") . $ruci->id;
                $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*, (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 " . $this->where_app . " AND " . $this->table . ".response_to IN (" . $str . ") ORDER BY " . $this->table . ".time ASC") or die($this->db->error);
                if ($res != null) {
                    while ($result = $res->fetch_array()) {
                        $result["type"] = $this->type;
                        $arr[$result["response_to"]]->responses[] = new RatableUserContentItem($result);
                    }
                }
            }
        }
        $retarr = array("items" => $arr, "start" => $start, "page" => ($start / $this->items_per_page) + 1);
        return $retarr;
    }

    public function getItemByID($id) {
        $cid = intval($id);
        $user = Auth::getUser();
        $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*, (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 AND " . $this->table . ".id = " . $cid) or die($this->db->error);
        $ruci = null;
        if ($res != null) {
            $result = $res->fetch_array();
            if ($result != null) {
                $result["type"] = $this->type;
                $ruci = new RatableUserContentItem($result);
            }
        }
        if ($this->response_allowed && $ruci && $ruci->canHaveResponses()) {
            $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*, (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 " . $this->where_app . " AND " . $this->table . ".response_to = " . $cid . " ORDER BY " . $this->table . ".time ASC") or die($this->db->error);
            if ($res != null) {
                while ($result = $res->fetch_array()) {
                    $result["type"] = $this->type;
                    $ruci->responses[] = new RatableUserContentItem($result);
                }
            }
        }
        return $ruci;
    }

    public function updateRating($id) {
        $cid = intval($id);
        $res = $this->db->query("SELECT COUNT(*) as count, rating FROM " . $this->table . "_ratings WHERE itemid=" . $cid . " GROUP BY rating");
        $rating_data = array();
        $avg = 0;
        $count = 0;
        if ($res) {
            while ($arr = $res->fetch_array()) {
                $rating_data[intval($arr["rating"])] = array("count" => $arr["count"]);
                $count += $arr["count"];
                $avg += $arr["rating"] * $arr["count"];
            }
            $avg /= $count;
            for ($i = 1; $i <= 5; $i++)
                if (isset($rating_data[$i]))
                    $rating_data[$i]["ratio"] = $rating_data[$i]["count"] / $count;
        }
        $data = array("rating" => $rating_data);
        $this->db->query("UPDATE " . $this->table . " SET rating=" . $avg . ", data='" . json_encode($data) . "' WHERE id=" . $cid) or die($this->db->error);
        return array($avg, $count, $data);
    }

    public function rate($id, $rating, $user = null) {
        $user = Auth::getUser();
        $cid = intval($id);
        $res = $this->db->query("SELECT rating FROM " . $this->table . "_ratings WHERE itemid=" . $cid . " AND userid=" . Auth::getUserID()) or die($this->db->error);
        if ($res && $res->fetch_array()) {
            $edit = true;
            $this->db->query("UPDATE " . $this->table . "_ratings SET rating=" . intval($rating) . " WHERE itemid=" . $cid . " AND userid=" . Auth::getUserID());
        } else {
            $edit = false;
            $this->db->query("INSERT INTO " . $this->table . "_ratings(userid, itemid, rating) VALUES(" . $user->getID() . ", " . $cid . ", " . intval($rating) . ")") or die($this->db->error);
        }
        return array("rating" => $this->updateRating($id), "edited" => $edit);
    }

    public function deleteItem($id, $trigger_action = true) {
        $cid = intval($id);
        $this->db->query("DELETE FROM " . $this->table . " WHERE id=" . $cid) or die($this->db->error);
        $this->db->query("DELETE FROM " . $this->table . "_ratings WHERE itemid=" . $cid) or die($this->db->error);
        if ($this->response_allowed) {
            $res = $this->db->query("SELECT id FROM " . $this->table . " WHERE response_to=" . $cid) or die($this->db->error);
            if ($res != null) {
                while ($arr = $res->fetch_array())
                    $this->deleteItem($arr["id"], true);
            }
        }
        if ($trigger_action) {
            Actions::addAction($id, Auth::getUserName(), "delete_" . $this->type);
        }
        return $this;
    }

    public function setOrderBy($order_by) {
        foreach ($this->order_by_dic as $key => $value) {
            if ((is_int($key) && $value === $order_by) || $key === $order_by) {
                $this->order_by = $value;
                break;
            }
        }
        return $this;
    }

    public function setOrderDirection($direction) {
        $direction = strtoupper($direction);
        if ($direction == "DESC" || $direction == "ASC")
            $this->order_direction = $direction;
        return $this;
    }

    public function setApps($from_app, $where_app) {
        $this->where_app = $where_app;
        $this->from_app = $from_app;
        return $this;
    }

    public function appendToFromApp($text) {
        $this->from_app .= ' ' . $text;
        return $this;
    }

    public function appendToWhereApp($text) {
        $this->where_app .= ' ' . $text;
    }

    public function getItemsPerPage() {
        return $this->items_per_page;
    }

    public function appendSearchAfterPhrase($phrase) {
        $phrase = sanitizeInputText($phrase);
        if ($phrase != "" && $phrase != null)
            $this->appendSearchAfterPhraseImpl($phrase);
        return $this;
    }

    protected abstract function appendSearchAfterPhraseImpl($phrase);

    public function appendSearchAfterUser($user_str) {
        if ($user_str == "" || $user_str == null)
            return;
        $ano_app = !Auth::canSeeNameWhenSentAnonymous() ? 'AND isanonymous = 0' : '';
        if (is_numeric($user_str) || $user_str == "me") {
            $id = $user_str == "me" ? Auth::getUserID() : intval($user_str);
            $this->appendToWhereApp(" AND u.id = " . $id);
        } else {
            $namearr = User::splitName(sanitizeInputText($user_str));
            $this->appendToWhereApp(" AND (u.first_name LIKE '%" . $namearr[0] . "%' OR u.last_name LIKE '%" . $namearr[1] . "%')" . $ano_app);
        }
        return $this;
    }

    public function setStart($start) {
        $start = intval($start);
        if ($start > 0)
            $this->start = $start;
        return $this;
    }

    public function resetCount(){
        $this->count = -1;
        $this->getCount();
    }
    
}