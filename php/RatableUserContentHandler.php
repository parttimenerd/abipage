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

class RatableUserContentHandler extends ToroHandler {

    protected $list;
    private $tpl_list_func_name;
    protected $item_per_page;

    public function __construct($list, $tpl_list_func_name) {
        parent::__construct();
        $this->list = $list;
        $this->items_per_page = $this->list->getItemsPerPage();
        $this->tpl_list_func_name = $tpl_list_func_name;
    }

    public function get($slug = null) {
        $time_sort = true;
        $sort_desc = true;
        if (isset($_GET["sort"])) {
            $arr = explode("_", $_GET["sort"]);
            $time_sort = $arr[0] == "time";
            $sort_desc = $arr[1] == "desc";
        }
        if ($slug != null && preg_match("/\/[0-9]+/", $slug)) {
            $start = $this->items_per_page * (intval(substr($slug, 1)) - 1);
        } else if (isset($_GET["page"])) {
            $start = $this->items_per_page * (intval($_GET["page"]) - 1);
        } else if (empty($_GET) || !isset($_GET["start"]) || !is_int($_GET["start"])) {
            $start = 0;
        } else {
            $start = intval($_GET["start"]);
        }
        $phrase = isset($_POST["phrase"]) ? $_POST["phrase"] : "";
        $this->processPhrase($phrase);
        $arr = $this->list->getItems($start, $time_sort, $sort_desc);
        call_user_func($this->tpl_list_func_name, $arr["items"], $arr["page"], $this->list->getPageCount(), ($time_sort ? "time" : "rating") . '_' . ($sort_desc ? "desc" : "asc"), $phrase);
    }

    public function post() {
        global $env;
        if (isset($_POST["rating"])) {
            echo $this->list->rate(intval($_POST["rating"]));
        } else if (isset($_POST["delete"]) && Auth::isAdmin() && isset($_POST["id"])) {
            if ($this->list->deleteItem(intval($_POST["id"]))) {
                echo intval($_POST["id"]);
            }
        } else if (isset($_POST["send"]) || !empty($_FILES["uploaded_file"]) || isset($_POST["send_anonymous"])) {
            if ($this->post_impl()) {
                $arr = $this->list->getItems(0, true, true);
                if (count($arr["items"]) != 0) {
                    call_user_func($this->tpl_list_func_name, array($arr["items"][0]), $arr["page"], $this->list->getPageCount(), "", "", false);
                }
            }
        } else {
            $begin = isset($_POST["page"]) ? intval($_POST["page"]) * $env->items_per_page : 0;
            $time_sort = true;
            $sort_desc = true;
            $phrase = isset($_POST["phrase"]) ? $_POST["phrase"] : "";
            if (isset($_POST["sort"]) && $_POST["sort"] != "") {
                $arr = explode("_", $_POST["sort"]);
                $time_sort = $arr[0] == "time";
                $sort_desc = $arr[1] == "desc";
            }
            $this->processPhrase($phrase);
            $arr = $this->list->getItems($begin, $time_sort, $sort_desc);
            call_user_func($this->tpl_list_func_name, $arr["items"], $arr["page"], $this->list->getPageCount(), ($time_sort ? "time" : "rating") . '_' . ($sort_desc ? "desc" : "asc"), $phrase, false);
        }
    }

    public function post_impl() {
        
    }

    public function post_xhr() {
        $this->post();
    }

    public function processPhrase($phrase) {
        if ($phrase == null || $phrase == "") {
            return;
        }
        $phrase = cleanInputText($phrase);
        $this->processPhraseImpl($phrase);
    }

}