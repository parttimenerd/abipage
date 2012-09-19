<?
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

function tpl_home($news = array(), ActionArray $actions = null) {
    global $env;
    tpl_before("home", $env->title, $env->subtitle);
    $tiles = array(
        "quotes" => array("lead" => "Zitate"),
        "rumors" => array("lead" => "Stimmt es..."),
        "images" => array("lead" => "Bilder"),
        "user/all" => array("lead" => "Schüler")
    );
        if ($env->user_polls_open)
        $tiles["userpolls"] = array("lead" => "Umfragen");
    if ($env->has_forum)
        $tiles[$env->forum_url] = array("lead" => "Forum");
    if ($env->has_wiki)
        $tiles[$env->wiki_url] = array("lead" => "Wiki");
    $tiles["user/me"] = array("lead" => "Me");
    //tpl_tiles($tiles, 3, "hidden-desktop");
    tpl_item_before();
    echo formatText($env->mainpage_text);
    tpl_item_after();
    if (!empty($news))
        tpl_news_list($news, false);
    ?><hr class="hidden-desktop"/><?
    if ($actions != null)
        tpl_actions_page($actions, false, "hidden-desktop");
    tpl_after();
}

function tpl_home_no_user() {
    global $env;
    tpl_before("home", $env->title, $env->subtitle);
    $tiles = array(
        "login" => array("lead" => "Anmelden", "sub" => array("text" => "Passwort vergessen?", "href" => "forgot_password")),
        "register" => array("lead" => "Registrieren")
    );
    //tpl_tiles($tiles, 6);
    tpl_item_before();
    echo formatText($env->mainpage_text);
    tpl_item_after();
    tpl_after();
}

function tpl_tiles($tiles, $width, $class = "") {
    echo '<div class="row tile_container ' . $class  . '">';
    foreach ($tiles as $href => $arr)
        tpl_tile($href, $arr, $width, $class);
    echo '</div>';
}

function tpl_tile($href_part, $arr, $width, $class = "") {
    ?>
    <a href="<?= tpl_url($href_part) ?>">
        <div class="span<?= isset($arr["width"]) ? $arr["width"] : $width ?> tile">
            <?= $arr["lead"] ?><br/>
            <? if (isset($arr["sub"]) && isset($arr["sub"]["text"]) && isset($arr["sub"]["href"])): ?>
                <a href="<?= tpl_url($arr["sub"]["href"]) ?>" class="sub">
                    <?= $arr["sub"]["text"] ?>
                </a>
            <? endif ?>
        </div>
    </a>
    <?
}