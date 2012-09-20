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

function tpl_actions_sidebar() {
    global $env, $store;
    ?>
    <script>
        var actions_url = "<?php echo tpl_url("ajax/actions"); ?>";
        var last_action_id = "<?php echo $store->last_action_id ?>";
        var showed_actions = "<?php echo $env->showed_actions ?>";
    </script>
    <div class="span3 sidebar">
        <div class="well">
            <ul class="nav nav-list action_list_container">
                <li class="nav-header" id="action_header"><a href="<?= tpl_url("actions") ?>">Aktionen</a></li>
                <?php
                $action_arr = Actions::getLastActions();
                tpl_actions($action_arr);
                tpl_add_js('var last_action_id = ' . $action_arr->getLastActionID());
                ?>
            </ul>
        </div><!--/.well -->
    </div><!--/span .sidebar-->
    <?php
}

function tpl_actions_page(ActionArray $actions, $as_page = true, $class_app = "") {
    if ($as_page) {
        tpl_before("actions");
    }
    echo '<div class="action_list_container ' . $class_app . '">';
    $actions->each(function($action) {
                tpl_item_before("", "", "action action_list_item", 'action_' . $action["id"]);
                tpl_action($action);
                tpl_item_after();
            });
    echo '</div>';
    tpl_add_js('var last_action_id = ' . $actions->getLastActionID());
    if ($as_page) {
        tpl_after();
    }
}

function tpl_actions(ActionArray $actions) {
    $actions->each(function($action) {
                tpl_action($action, true, '<li class="action action_list_item" id="action_' . $action["id"] . '">', '</li>');
            });
}

function tpl_action($action, $with_time = true, $before_html = '', $after_html = '') {
    echo $before_html;
    if ($with_time) {
        tpl_timediff_span(time() - $action["time"]);
        echo " ";
    }
    $url = "";
    switch ($action["type"]) {
        case "add_user_comment":
            echo "Kommentar bei ";
            $url = tpl_userlink($action["person"]);
            break;
        case "delete_user_comment":
            echo "Kommentar gelöscht";
            break;
        case "add_quote":
            $url = tpl_url('quotes');
            echo '<a href="' . $url . '">Zitat</a> von ' . $action["person"];
            break;
        case "add_rumor":
            $url = tpl_url('rumors');
            echo '<a href="' . $url . '">Stimmt es...</a> Beitrag geschrieben';
            break;
        case "upload_image":
            $url = tpl_url('images');
            echo '<a href="' . $url . '">Bild</a> hochgeladen';
            break;
        case "new_user":
            $url = tpl_userlink(intval($action["person"]));
            echo " registriert";
            break;
        case "delete_image":
            $url = tpl_url('images');
            echo '<a href="' . $url . '">Bild</a> gelöscht';
            break;
        case "delete_quote":
            $url = tpl_url('quotes');
            echo '<a href="' . $url . '">Zitat</a> gelöscht';
            break;
        case "delete_rumor":
            $url = tpl_url('rumors');
            echo '<a href="' . $url . '">Stimmt es...</a> Beitrag gelöscht';
            break;
        case "register":
            echo 'Neuer Benutzer registriert';
            break;
        case "write_news":
            $url = tpl_url('news');
            echo '<a href="' . $url . '">Neue Nachricht</a> von ' . $action["person"] . ' geschrieben';
            break;
    }
    echo $after_html;
    return $url;
}