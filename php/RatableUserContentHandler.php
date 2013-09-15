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

    /**
     *
     * @var RatableUserContentList
     */
    protected $list;
    private $tpl_list_func_name;
    protected $item_per_page;
    protected $slug_params = array(
        "search" => "string",
        "search_def" => array("single"),
        "page" => "int",
        "id" => "int", //Not yet implemented in the ui
        "start_time" => "int",
        "sort" => "(time|rating)",
        "user" => "string",
        "desc", "asc");

    public function __construct($list, $tpl_list_func_name) {
        parent::__construct();
        $this->list = $list;
        $this->items_per_page = $this->list->getItemsPerPage();
        $this->tpl_list_func_name = $tpl_list_func_name;
    }

    public function get($slug = null) {
        $params = array();
        if ($slug != null) {
            $params = $this->configListFromSlug($slug);
        } else if (isset($_GET["start"]) && is_int($_GET["start"])) {
            $this->list->setStart(intval($_GET["start"]));
        }
        if (isset($_GET["phrase"])) {
            $phrase = $_GET["phrase"];
            $this->list->appendSearchAfterPhrase($phrase);
        } else {
            $phrase = "";
            if (isset($_GET["id"]) && intval($_GET["id"]) > 0) {
                $this->list->setMustIncludeId($_GET["id"]);
                tpl_add_js("document.getElementById('" . intval($_GET["id"]) . "').scrollIntoView()");
            }
        }
        if (isset($_GET["page"]))
            $this->list->setStart($this->items_per_page * (intval($_GET["page"]) - 1));

        $ajax = isset($params["ajax"]) || isset($_GET["ajax"]);
        if ($ajax) {
            jsonAjaxResponseStart();
        }
        if (!isset($_GET["only_update"]) || $_GET["only_update"] == false) {
            $arr = $this->list->getItems();
            call_user_func($this->tpl_list_func_name, $arr["items"], $arr["page"], $this->list->getPageCount(), $phrase, !$ajax);
        }
        if ($ajax) {
            $html = ob_get_clean();
            ob_start();
            if (isset($_GET["last_time_updated"])) {
                $this->list->appendToWhereApp("AND time > " . $_GET["last_time_updated"]);
                $this->list->resetCount();
                $this->list->setStart(10E100);
                if (!$this->list->isEmpty()) {
                    $arr = $this->list->getItems();
                    call_user_func($this->tpl_list_func_name, $arr["items"], -1, -1, null, false);
                }
            }
            $items = ob_get_clean();
            jsonAjaxResponseEndSend(array(), array("items" => $items), $html);
        }
    }

    private function configListFromSlug($slug) {
        $params = findParamsInSlug($slug, $this->slug_params);
        if (isset($params["start_time"]) > 10000)
            $this->list->appendToWhereApp("time < " . $params["start_time"]);
        if (isset($params["search_def"]))
            $this->list->appendSearchAfterPhrase(str_replace('_', ' ', $params["search_def"]));
        if (isset($params["search"]))
            $this->list->appendSearchAfterPhrase(str_replace('_', ' ', $params["search"]));
        if (isset($params["page"]) && $params["page"] >= 1)
            $this->list->setStart($this->items_per_page * ($params["page"] - 1));
        if (isset($params["user"]))
            $this->list->appendSearchAfterUser($params["user"]);
        if (isset($params["sort"]))
            $this->list->appendSearchAfterUser($params["sort"]);
        if (isset($params["desc"]))
            $this->list->setOrderDirection("desc");
        if (isset($params["asc"]))
            $this->list->setOrderDirection("asc");
        $this->configListFromSlugParamsImpl($params);
        return $params;
    }

    protected function configListFromSlugParamsImpl($params) {
        
    }

    public function get_result($slug = "") {
        $this->list->setOrderBy("rating")->setOrderDirection("desc");
        $this->get($slug);
    }

    public function post($slug = "") {
        if (isset($_POST["rating"]) && isset($_POST["id"])) {
            $arr = $this->list->rate(intval($_POST["id"]), intval($_POST["rating"]));
            jsonAjaxResponseStart();
            tpl_average($arr["rating"][0], $arr["rating"][1], $arr["rating"][2]);
            if ($arr["edited"]) {
                PiwikHelper::addTrackGoalJS("Item rating edited");
            } else {
                PiwikHelper::addTrackGoalJS("Item rated");
            }
            PiwikHelper::echoJSTrackerCode(false);
            jsonAjaxResponseEndSend();
        } else if (isset($_POST["delete"]) && Auth::canDeleteRucItem() && isset($_POST["id"])) {
            if ($this->list->deleteItem(intval($_POST["id"]), isset($_POST["cause"]) ? $_POST["cause"] : "")) {
                jsonAjaxResponseStart();
                PiwikHelper::addTrackGoalJS("Item deleted");
                PiwikHelper::echoJSTrackerCode(false);
                jsonAjaxResponseEndSend(array("id" => intval($_POST["id"])));
            }
        } else if (isset($_POST["send"]) || !empty($_FILES["uploaded_file"]) || isset($_POST["send_anonymous"])) {
            $id = $this->post_impl();
            if ($id !== false) {
                $item = $this->list->getItemByID($id);
                if ($item != null) {
                    jsonAjaxResponseStart();
                    call_user_func($item->getTplFunctionName(), $item);
                    jsonAjaxResponseEndSend();
                }
            }
        } else if (isset($_POST["edit"]) && Auth::canEditRucItems()) {
            if (isset($_POST["edit"]["id"])) {
                $this->list->editItem($_POST["edit"]["id"], $_POST["edit"]);
                jsonAjaxResponseStart();
                jsonAjaxResponseEndSend(array("success" => true, "args" => $_POST["edit"]));
            } else {
                jsonAjaxResponseStart();
                jsonAjaxResponseEndSend(array("success" => false));
            }
        } else {
            $phrase = isset($_POST["phrase"]) ? $_POST["phrase"] : "";
            $this->configListFromSlug($slug);
            $this->list->appendSearchAfterPhrase($phrase);
            if (isset($_GET["page"]))
                $this->list->setStart($this->items_per_page * (intval($_GET["page"]) - 1));
            $arr = $this->list->getItems();
            call_user_func($this->tpl_list_func_name, $arr["items"], $arr["page"], $this->list->getPageCount(), $phrase, false);
        }
    }

}