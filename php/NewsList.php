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

class NewsList {

    public static function getNews($max_length = -1) {
        global $db;
        $limit_str = $max_length > 0 ? (" LIMIT 0, " . $max_length) : "";
        return mysqliResultToArr($db->query("SELECT * FROM " . DB_PREFIX . "news ORDER BY time DESC" . $limit_str));
    }

    public static function getNewsByID($id) {
        global $db;
        return mysqliResultToArr($db->query("SELECT * FROM " . DB_PREFIX . "news WHERE id=" . intval($id)));
    }

    public static function getNewsByPhrase($phrase, $max_length) {
        global $db;
        $phrase = cleanInputText($phrase);
        $limit_str = $max_length > 0 ? (" LIMIT 0, " . $max_length) : "";
        return mysqliResultToArr($db->query("SELECT * FROM " . DB_PREFIX . "news WHERE MATCH(title, content) AGAINST('" . $phrase . "') OR title LIKE '%" . $phrase . "%' OR content LIKE '%" . $phrase . "%' ORDER BY time DESC" . $limit_str));
    }

    public static function writeNews($title, $content, $send_emails = true) {
        global $db;
        $ctitle = cleanInputText($title);
        $ccontent = cleanInputText($content);
        $db->query("INSERT INTO " . DB_PREFIX . "news(id, title, content, time, userid) VALUES(NULL, '$ctitle', '$ccontent', " . time() . ", " . Auth::getUserID() . ")");
        if ($send_emails)
            User::getAll()->sendMail($title, $content);
    }

}

?>
