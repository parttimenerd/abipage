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

class NewsListHandler extends ToroHandler {

    public function get($slug = "") {
        if (strlen($slug) <= 1)
            tpl_news_list(NewsList::getNews());
        elseif ($slug == "/write") {
            if (!Auth::canWriteNews()) {
                tpl_403();
            } else {
                tpl_write_news();
            }
        }
        else
            tpl_news_list(NewsList::getNewsByPhrase(substr($slug, 1)));
    }

    public function post($slug = "") {
        if (!Auth::canWriteNews()) {
            tpl_403();
            return;
        }
        if ($slug == "/write") {
            if (isset($_POST["title"]) && isset($_POST["text"]) && strlen($_POST["text"]) > 5 && strlen($_POST["title"]) > 3) {
                NewsList::writeNews($_POST["title"], $_POST["text"], isset($_POST["write-email"]));
            } else {
                $this->get($slug);
                return;
            }
        }
        return $this->get();
    }

}

?>
