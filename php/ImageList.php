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

class ImageList extends RatableUserContentList {

    protected $items_per_page;

    public function __construct() {
        global $env;
        parent::__construct("images", false, true);
        $this->items_per_page = $env->images_per_page;
        array_push($this->order_by_dic, "category");
        array_push($this->order_by_dic, "capture_time");
    }

    public function deleteItem($id, $trigger_action = true) {
        global $env;
        $cid = intval($id);
        $res = $this->db->query("SELECT id, format FROM " . $this->table . " WHERE id=" . $cid) or die($this->db->error);
        if ($res != null) {
            $arr = $res->fetch_array();
            $filename = $arr["id"] . '.' . $arr["format"];
            $file = BASE_DIR . $env->main_dir . '/' . $env->upload_path . '/' . $filename;
            $thumbfile = BASE_DIR . $env->main_dir . '/' . $env->upload_path . '/thumbs/' . $filename;
            parent::deleteItem($cid, $trigger_action);
            if (file_exists($file)) {
                unlink($file);
            }
            if (file_exists($thumbfile)) {
                unlink($thumbfile);
            }
            return true;
        }
        return false;
    }

    public function setDescription($id, $descr) {
        $this->db->query("UPDATE " . $this->table . " SET description = '" . sanitizeInputText($descr) . "' WHERE id=" . intval($id)) or die($this->db->error);
        return $this;
    }
    
    public function setCategory($id, $category) {
        $this->db->query("UPDATE " . $this->table . " SET category = '" . sanitizeInputText($category) . "' WHERE id=" . intval($id)) or die($this->db->error);
        return $this;
    }
    
    public function setExif($id, $exif) {
        $ctime = strtotime(isset($exif["DateTimeOriginal"]) ? $exif["DateTimeOriginal"] : $exif["DateTime"]);
        $datastr = $this->db->real_escape_string(json_encode($exif));
        $this->db->query("UPDATE " . $this->table . " SET capture_time = " . $ctime . ", data='" . $datastr . "' WHERE id=" . intval($id)) or var_dump($this->db->error);
        return $this;
    }

    public function addImage($descr = "", $category = "", $time = -1, $user = null) {
        global $env;
        if (get_upload_dir_size_mib() + 3 > $env->max_uploads_size) {
            $env->sendAdminMail("Upload-Ordner ist voll", "Es können keine Bilder mehr hochgeladen werden, da der Upload-Ordner voll ist, bitte löschen sie entweder Bilder oder vergrößern sie die Größe des Upload-Ordners in den <a href='" . tpl_url("preferences") . "'>Seiteneinstellungen</a>.");
            return;
        }
        global $env;
        if ($time == -1) {
            $time = time();
        }
        if (!$user) {
            $user = Auth::getUser();
        }
        $descr = sanitizeInputText($descr);
        $category = sanitizeInputText($category);
        $this->db->query("INSERT INTO " . $this->table . "(id, userid, description, category, capture_time, format, time, rating, data) VALUES(NULL, " . $user->getID() . ", '" . $descr . "', '" . $category . "', 0, '" . $env->pic_format . "', " . $time . ", 0, '')") or die($this->db->error);
        $id = $this->db->insert_id;
        Actions::addAction($id, $user->getName(), "upload_image");
        return $id;
    }

    protected function appendSearchAfterPhraseImpl($cphrase) {
        $this->appendToWhereApp(" AND (MATCH(description) AGAINST('" . $cphrase . "') OR description LIKE '%" . $cphrase . "%' OR category LIKE '%" . $cphrase . "%')");
    }

    public function getCategories() {
        $res = $this->db->query("SELECT DISTINCT category FROM " . $this->table);
        $arr = array();
        if ($res != null) {
            while ($a = $res->fetch_array())
                $arr[] = $a["category"];
        }
        return $arr;
    }

}