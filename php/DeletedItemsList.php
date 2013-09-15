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

class DeletedItemsList {

    const RUMOR = 1;
    const QUOTE = 2;
    const IMAGE = 3;
    const USER_COMMENT = 4;

    public static function getDeletedItems($type, $max_length = -1) {
        global $db;
        $limit_str = $max_length > 0 ? (" LIMIT 0, " . intval($max_length)) : "";
        $arr = mysqliResultToArr($db->query("SELECT * FROM " . DB_PREFIX . "deleted_items WHERE type=" . intval($type) . " ORDER BY delete_time DESC" . $limit_str));
        $retarr = array();
        foreach ($arr as $item) {
            $temp = json_decode($item["data"], true);
            $temp = array_merge($temp, $item);
            $retarr[] = $temp;
        }
        return $retarr;
    }

    public function getAll() {
        $arr = array();
        $arr[self::RUMOR] = self::getDeletedItems(self::RUMOR);
        $arr[self::QUOTE] = self::getDeletedItems(self::QUOTE);
        $arr[self::IMAGE] = self::getDeletedItems(self::IMAGE);
        $arr[self::USER_COMMENT] = self::getDeletedItems(self::USER_COMMENT);
        return $arr;
    }

    /**
     * Please call it before you delete the item from the database.
     * 
     * @global type $db
     * @param type $type
     * @param type $itemid
     * @param type $item
     */
    public static function addDeletedItemToList($type, $itemid, $cause) {
        global $db;
        $type = intval($type);
        $itemid = intval($itemid);
        $cause = sanitizeInputText($cause);
        $deleting_userid = Auth::getUserID();
        $delete_time = time();
        $authorid = -1;
        $author_time = -1;
        $data = array();
        if ($type == self::RUMOR || $type == self::QUOTE || $type == self::IMAGE) {
            $list = $type == self::RUMOR ? new RumorList() : new QuoteList();
            $item = $list->getItemByID($itemid);
            if ($item == null) {
                return;
            }
            $authorid = intval($item->userid);
            $author_time = intval($item->time);
            $data["text"] = $item->text;
            $data["isanonymous"] = $item->isAnonymous();
            $data["rating"] = $item->rating;
            $data["rating_count"] = $item->rating_count;
            if ($type == self::QUOTE) {
                $data["person"] = $item->person;
            }
            if ($type == self::IMAGE) {
                $data["description"] = $item->description;
                $data["category"] = $item->category;
                $data["capture_time"] = $item->capture_time;
            }
        } else if ($type == self::USER_COMMENT) {
            $item = User::getUserCommentStatic($itemid);
            if (empty($item)) {
                return;
            }
            $authorid = $item["commenting_userid"];
            $author_time = $item["time"];
            $data["commented_userid"] = $item["commented_userid"];
            $data["notified_as_bad"] = intval($item["notified_as_bad"]) == 1;
            $data["reviewed"] = $item["reviewed"];
            $data["text"] = $item["text"];
            $data["isanonymous"] = $item["isanonymous"];
        }
        $db->query("INSERT INTO " . DB_PREFIX . "deleted_items(id, type, itemid, deleting_userid, authorid, data, delete_cause, author_time, delete_time) VALUES(NULL, $type, $itemid, $deleting_userid, $authorid, '" . $db->escape_string(json_encode($data)) . "', '" . $cause . "', $author_time, $delete_time)") or die($db->error);
    }

    public static function stringToTypeID($str) {
        switch ($str) {
            case "quote":
                return self::QUOTE;
            case "rumor":
                return self::RUMOR;
            case "image":
                return self::IMAGE;
            case "user_comment":
                return self::USER_COMMENT;
        }
    }

}

?>
