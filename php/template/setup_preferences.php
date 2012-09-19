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

/**
  $val_arr = array("value" => array("value" => "", "label" => "", "type" => "textarea|inputfield|checkbox|password|usermode|color|email", "css_class" => "", "js_onchange" => ""))
 */
function tpl_pref_table($val_arr, $css_class = "") {
    ?>
    <table class="table table-striped pref_table<?php if ($css_class != "") echo ' ' . $css_class ?>">
        <?php foreach ($val_arr as $key => $val): ?>
            <tr<?php if (isset($val["css_class"])) echo ' class="' . $val["css_class"] . '"' ?>>
                <td class="left_column"><?php echo isset($val["label"]) ? $val["label"] : $key
            ?></td>
                <td class="right_column"><?php
            $val["name"] = $key;
            tpl_input($val);
            ?>
            </tr>
        <?php endforeach ?>		
    </table>
    <?php
}

function tpl_pref_table_categorized($val_arr, $id = "", $css_class = "") {
    if ($id == "")
        $id = Auth::random_string(10);
    ?>
    <div class="<?= $css_class ?>" id="<?= $id ?>">
        <?php
        foreach ($val_arr as $category_title => $arr)
            tpl_pref_table_category_part($id, $category_title, $arr);
        ?>
    </div>
    <?php
}

function tpl_pref_table_category_part($container_id, $category_title, $val_arr) {
    $id = isset($val_arr["id"]) ? $val_arr["id"] : $category_title;
    $mode = isset($val_arr["mode"]) ? $val_arr["mode"] : "";
    $open = isset($val_arr["open"]) ? $val_arr["open"] : false;
    $val_arr = isset($val_arr["rows"]) ? $val_arr["rows"] : $val_arr;
    $keys = array_keys($val_arr);
    for ($index = 0; $index < count($val_arr); $index++) {
        $val_arr[$keys[$index]]["name"] = $keys[$index];
        if (!isset($val_arr[$keys[$index]]["label"]))
            $val_arr[$keys[$index]]["label"] = $keys[$index];
    }
    ?>
    <button type="button" class="btn btn-danger" data-toggle="collapse" style="width: 100%; text-align: left" data-parent="#<?= $container_id ?>" href="#<?= $id ?>">
        <?= $category_title ?>
    </button>
    <div id="<?= $id ?>" class="accordion-body collapse <?= $open ? "in" : "" ?>" data-parent="#<?= $container_id ?>">
        <?
        switch ($mode) {
            default:
            case "table":
                ?>
                <table class="table table-striped">
                    <? foreach ($val_arr as $title => $arr): ?>
                        <tr>
                            <td class="left_column" style="width: 30%"><?= $arr["label"] ?></td>
                            <td class="right_column" style="width: 70%"><? tpl_input($arr) ?></td>
                        </tr>
                    <? endforeach ?>
                </table><?
            break;
        case "table-list":
                    ?>
                <table class="table table-striped">
                    <? foreach ($val_arr as $title => $arr): ?>
                        <tr>
                            <th><?= $arr["label"] ?></th>
                        </tr>
                        <tr>
                            <td><? tpl_input($arr) ?></td>
                        </tr>
                    <? endforeach ?>
                </table><?
            break;
        case "list":
                    ?>
                <ul class="unstyled" style="margin: 0px">
                    <? foreach ($val_arr as $title => $arr): ?>
                        <li class="list_header"><?= $arr["label"] ?></li>
                        <li><? tpl_input($arr) ?></lih>
                        <? endforeach ?>
                </ul><?
            break;
        case "dl-horizontal" :
                        ?>
                <dl class="dl-horizontal">
                    <? foreach ($val_arr as $title => $arr): ?>
                        <dt><?= $arr["label"] ?></dt>
                        <dd><? tpl_input($arr) ?></dd>
                    <? endforeach ?>
                </dl><?
    }
            ?>
    </div>
    <?php
}

function tpl_dbsetup($val_arr, $error_str = "") {
    //tpl_before("", "Setup");
    ?>
    <h1>Installieren der Seite</h1>
    <? if ($error_str != ""): ?>
        <p style="background-color: red; color: white; padding-top: 5%; padding-bottom: 5%; font-size: 1.2em; text-align: center; width: 100%">
            Fehler beim Verbinden mit der Datenbank: <?= $error_str ?> <br/>
            Bitte überprüfen sie ihre Eingaben.
        </p>
    <? endif ?>
    <form action="setup" method="POST">
        <?php tpl_pref_table($val_arr) ?>
        <button type="submit" class="btn">Installieren</button>
    </form>
    <?php
    tpl_infobox("Wichtig", "Diese Einstellungen können zum Teil nachträglich in der Datei 'db_options.php' verändert werden, welche bei einer Neuinstallation gelöscht werden muss.<br/>
	Sie Stimmen mit dem Installieren dieser Software den Lizenzbestimmungen (zufinden in der LICENCE-Datei des Hauptordners) zu und verpflichten sich außerdem dem Autor dieser Software eine Abizeitungen zukommen zu lassen, zu welcher diese Software ihren Beitrag geleistet hat.
	Über eine Spende würde sich der Autor dieses Programms natürlich auch freuen.");
    //tpl_after();
}

function tpl_preferences($val_arr) {
    tpl_before("preferences", "Einstellungen");
    if (Auth::canModifyPreferences())
        tpl_item_before_form(array("action" => tpl_url("preferences")));
    else
        tpl_item_before();
    ?>
    <?php tpl_pref_table_categorized($val_arr) ?>
    <?php
    if (Auth::canModifyPreferences())
        tpl_item_after_send("Speichern");
    else
        tpl_item_after();
    tpl_after();
}