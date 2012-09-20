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

function tpl_news_list($news, $as_page = true) {
    if ($as_page) {
        tpl_before("news");
        if (Auth::canWriteNews()) {
            tpl_infobox('', 'Neue Nachricht schreiben', tpl_url("news/write"));
        }
    }
    foreach ($news as $item) {
        tpl_news($item);
    }
    if ($as_page) {
        tpl_after();
    }
}

function tpl_news($news_item) {
    tpl_item_before($news_item["title"], "newspaper", "content-item news-item");
    echo formatText($news_item["content"]);
    tpl_item_after_news_item($news_item);
}

function tpl_item_after_news_item($news_item) {
    ?>
    </div>
    <hr/>
    <div class="item-footer">
        <ul>
            <li class="time_span_li"><?php tpl_time_span($news_item["time"]) ?></li>
            <li class="user_span_li"><?php tpl_user_span($news_item["userid"]) ?></li>
        </ul>
    </div>
    </div>
    <?
}

function tpl_write_news($as_page = true) {
    if ($as_page) {
        tpl_before("news/write");
    }
    tpl_item_before_form(array("method" => "POST", "action" => tpl_url("news/write")), "Nachricht schreiben", "pencil", "write-news");
    ?>
    <? tpl_input(array("name" => "title", "placeholder" => "Nachrichtentitel", "required" => "on")) ?>
    <? tpl_input(array("name" => "text", "value" => "", "type" => "textarea")) ?>
    <?php
    tpl_item_after_form(array("write" => array("text" => "Schreiben"), "write-email" => array("text" => "Schreiben und als Newsletter versenden", "icon" => "email")));
    if ($as_page) {
        tpl_after();
    }
}