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
  $val_arr = array("value" => array("default" => "", "label" => "", "type" => "textarea|inputfield|checkbox|password|usermode|color|email", "css_class" => "", "js_onchange" => ""))
 */
function tpl_pref_table($val_arr, $css_class = "") {
    ?>
    <table class="pref_table<?php if ($css_class != "") echo ' ' . $css_class ?>">
        <?php foreach ($val_arr as $key => $val): ?>
            <tr<?php if (isset($val["css_class"])) echo ' class="' . $val["css_class"] . '"' ?>>
                <td class="left_column"><?php echo isset($val["label"]) ? $val["label"] : $key
        ?></td>
                <td class="right_column"><?php
        $default = isset($val["default"]) ? $val["default"] : '';
        $type = isset($val["type"]) ? $val["type"] : "inputfield";
        switch ($type) {
            case "textarea":
                echo '<textarea name="' . $key . '">' . $default . '</textarea>';
                break;
            case "inputfield":
                echo "<input type='text' name='" . $key . "' value='" . $default . "'/>";
                break;
            case "password":
                echo '<input type="password" name="' . $key . '" value="' . $default . '"/>';
                break;
            case "checkbox":
                echo '<input type="checkbox" name="' . $key . '" value="true"' . ($default == "true" ? ' checked="checked"' : '') . '/>';
                break;
            case "usermode":
                tpl_usermode_combobox($key, $default == "" ? 0 : intval($default));
                break;
            case "color":
                tpl_color_selector($key, $default, isset($val["js_onchange"]) ? $val["js_onchange"] : "");
                break;
            case "email":
                echo '<input type="email" name="' . $key . '" value="' . $default . '"/>';
                break;
        }
            ?>
            </tr>
        <?php endforeach ?>		
    </table>
    <?php
}

function tpl_dbsetup($val_arr) {
    //tpl_before("", "Setup");
    ?>
    <h1>Installieren der Seite</h1>
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
    tpl_item_before_form(array("action" => tpl_url("preferences")));
    ?>
    <?php tpl_pref_table($val_arr) ?>
    <?php
    tpl_item_after_send("Speichern");
    tpl_after();
}