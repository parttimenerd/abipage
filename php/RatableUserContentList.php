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

class RatableUserContentList {

    protected $table = "";
    protected $db = "";
    private $count = -1;
    protected $where_app = "";
    protected $from_app = "";
    protected $items_per_page;
    protected $response_allowed;

    /** $fromapp = ", table"; $where_app = "AND id=0" */
    public function __construct($table, $response_allowed = false, $from_app = "", $where_app = "") {
        global $env, $db;
        $this->items_per_page = $env->items_per_page;
        $this->table = DB_PREFIX . $table;
        $this->db = $db;
        $this->where_app = $where_app;
        $this->from_app = $from_app;
        $this->response_allowed = $response_allowed;
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

    public function getItems($start = 0, $time_sort = true, $desc = true, $user = null) {
        if (!$user) {
            $user = Auth::getUser();
        }
        $arr = array();
        if ($this->getCount() > 0) {
            if ($start > $this->getCount()) {
                $start = ($this->getPageCount() * $this->items_per_page) - $this->items_per_page;
            }
            //$start = intval($start) - (intval($start) % $this->items_per_page);
            $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*, (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 " . $this->where_app . " ORDER BY " . ($time_sort ? "time" : "rating") . " " . ($desc ? "DESC" : "ASC") . " LIMIT " . $start . ", " . $this->items_per_page) or die($this->db->error);
            if ($res != null) {
                while ($result = $res->fetch_array()) {
                    $arr[] = new RatableUserContentItem($result);
                }
            }
            if ($this->response_allowed) {
                $str = "";
                foreach ($arr as $ruci)
                    $str .= ($str != "" ? ", " : "") . $ruci->id;
                $res = $this->db->query("SELECT " . $this->table . ".rating AS rating, " . $this->table . ".*, (SELECT " . $this->table . "_ratings.rating FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id AND userid=" . $user->getID() . ") AS own_rating, (SELECT COUNT(*) FROM " . $this->table . "_ratings WHERE itemid=" . $this->table . ".id) AS rating_count FROM " . $this->table . ", " . DB_PREFIX . "user u " . $this->from_app . " WHERE u.id = userid AND u.activated = 1 " . $this->where_app . " AND " . $this->table . ".response_to IN (" . $str . ") ORDER BY time ASC LIMIT 0, " . $this->items_per_page) or die($this->db->error);
                if ($res != null) {
                    while ($result = $res->fetch_array()) {
                        $arr[$result["response_to"]]->responses[] = new RatableUserContentItem($result);
                    }
                }
            }
        }
        $retarr = array("items" => $arr, "start" => $start, "page" => ($start / $this->items_per_page) + 1);
        return $retarr;
    }

    public function updateRating($id) {
        $cid = intval($id);
        $res = $this->db->query("SELECT COUNT(*) as count, rating FROM " . $this->table . "_ratings WHERE itemid=" . $cid . " GROUP BY rating");
        $data = array("rating");
        $avg = 0;
        $count = 0;
        if ($res) {
            while ($arr = $res->fetch_array()) {
                $data["rating"][intval($arr["rating"])] = array("count" => $arr["count"]);
                $count += $arr["count"];
                $avg += $arr["rating"] * $arr["count"];
            }
            $avg /= $count;
            for ($i = 1; $i <= 5; $i++)
                $data["rating"][$i]["ratio"] = $data["rating"][$i]["count"] / $count;
        }
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
        return array("rating" => $this->updateRating($id), "edit" => $edit);
    }

    public function deleteItem($id, $trigger_action = true) {
        global $env;
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
        if (!$trigger_action) {
            $env->addAction($id, Auth::getUserName(), "delete_" . str_replace(DB_PREFIX, "", $this->table));
        }
        return true;
    }

    public function setApps($from_app, $where_app) {
        $this->where_app = $where_app;
        $this->from_app = $from_app;
    }

    public function appendToFromApp($text) {
        $this->from_app .= ' ' . $text;
    }

    public function appendToWhereApp($text) {
        $this->where_app .= ' ' . $text;
    }

    public function getItemsPerPage() {
        return $this->items_per_page;
    }

}