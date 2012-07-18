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
        parent::__construct("images");
        $this->items_per_page = $env->images_per_page;
    }

    public function deleteItem($id) {
        global $env;
        $cid = intval($id);
        $res = $this->db->query("SELECT id, format FROM " . $this->table . " WHERE id=" . $cid) or die($this->db->error);
        if ($res != null) {
            $arr = $res->fetch_array();
            $filename = $arr["id"] . '.' . $arr["format"];
            $file = $env->main_dir . '/' . $env->upload_path . '/' . $filename;
            $thumbfile = $env->main_dir . '/' . $env->upload_path . '/thumbs/' . $filename;
            parent::deleteItem($cid);
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
        $this->db->query("UPDATE " . $this->table . " SET description = '" . cleanInputText($descr) . "' WHERE id=" . intval($id)) or die($this->db->error);
    }

    public function addImage($descr = "", $time = -1, $user = null) {
        global $env;
        if (get_upload_dir_size_mib() + 3 > $env->max_uploads_size){
            sendAdminMail("Upload-Ordner ist voll", "Es können keine Bilder mehr hochgeladen werden, da der Upload-Ordner voll ist, bitte löschen sie entweder Bilder oder vergrößern sie die Größe des Upload-Ordners in den Seiteneinstellungen.");
            return;
        }
        global $env;
        if ($time == -1) {
            $time = time();
        }
        if (!$user) {
            $user = Auth::getUser();
        }
        $descr = $this->db->real_escape_string(cleanInputText($descr));
        $this->db->query("INSERT INTO " . $this->table . "(id, userid, description, format, time, rating) VALUES(NULL, " . $user->getID() . ", '" . $descr . "', '" . $env->pic_format . "', " . $time . ", 0)") or die($this->db->error);
        $id = $this->db->insert_id;
        $env->addAction($id, $user->getName(), "upload_image");
        return $id;
    }

}