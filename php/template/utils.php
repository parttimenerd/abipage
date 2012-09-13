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

function tpl_infobox($strong_text, $message_text) {
    ?>
    <div class="alert alert-info">
        <?php if ($strong_text != ""): ?>
            <h4 class="alert-heading"><?php echo $strong_text ?></h4>
        <?php endif ?>
        <?php echo $message_text ?>
    </div>
    <?php
}

function tpl_usermode_combobox($name, $preset_modenum = User::NORMAL_MODE, $echo_all = false) {
    $arr = array(
        User::NORMAL_MODE => "Normal",
        User::EDITOR_MODE => "Editor",
    );
    if ($echo_all || Auth::isAdmin()) {
        $arr = array_merge($arr, array(
            User::MODERATOR_MODE => "Moderator",
            User::ADMIN_MODE => "Administrator"
                ));
    }
    ?>
    <select style="display: inline;" name="<?php echo $name ?>" class="user_mode_combobox">
        <?php foreach ($arr as $key => $value): ?>
            <option value="<?php echo $key ?>"<?php if ($key == $preset_modenum) echo ' selected="selected"' ?>>
                <?php echo $value ?>
            </option>
        <?php endforeach ?>
    </select>
    <?php
}

function tpl_usermode_to_text($mode) {
    $arr = array(
        User::ADMIN_MODE => "Administrator",
        User::MODERATOR_MODE => "Moderator",
        User::EDITOR_MODE => "Editor",
        User::NORMAL_MODE => "Normal",
        User::NO_MODE => "Gast"
    );
    return $arr[isset($arr[$mode]) ? $mode : User::NORMAL_MODE];
}

function tpl_get_user_subtitle($user) {
    $html = "";
    if ($user->getMathCourse() > 0) {
        $html .= "Mathekurs: " . $user->getMathCourse();
    }
    if ($user->getMathTeacher() != "") {
        $html .= ($html != "" ? "; " : "") . "Mathelehrer: " . $user->getMathTeacher();
    }
    if ($user->isEditor()) {
        $html .= ($html != "" ? "; " : "") . tpl_usermode_to_text($user->getMode());
    }
    $last_visit = tpl_user_last_visit($user->getID(), false);
    if ($last_visit)
        $html .= ($html != "" ? "; " : "") . $last_visit;
    return $html;
}

function tpl_time_span($time, $with_icon = true, $class = "time") {
    ?>
    <span class="<?php echo $class ?>"><?php if ($with_icon) tpl_icon("clock") ?> <?php echo date("d.m.y H:i", $time) ?></span>
    <?php
}

function tpl_user_span($user_id = -1, $with_icon = true) {
    ?>
    <span class="user_span">
        <?php
        if ($with_icon)
            tpl_icon("user");
        echo " ";
        if ($user_id == -1) {
            echo 'Anonym';
        } else if ($user_id == Auth::getUserID()) {
            echo '<a href="' . tpl_url("user/me") . '">Me</a>';
        } else {
            tpl_userlink($user_id, false);
        }
        ?>
    </span>
    <?php
}

function tpl_url($relative_url) {
    if (substr($relative_url, 0, 4) != "http") {
        return URL . '/' . str_replace(' ', '_', $relative_url);
    } else {
        return $relative_url;
    }
}

$id_username_dic = array();

function tpl_userlink($id_or_name, $last_name_first = false) {
    global $id_username_dic, $env;
    if ($id_or_name != "") {
        if (is_numeric($id_or_name)) {
            if (empty($id_username_dic)) {
                $id_username_dic = $env->getIDUsernameDictionary();
            }
            $name = $id_username_dic[$id_or_name];
        } else {
            $name = $id_or_name;
        }
        $url = tpl_url('user/' . str_replace(" ", "_", $name));
        if ($last_name_first) {
            $namearr = User::splitName($name);
            $namestr = $namearr[1] . ', ' . $namearr[0];
        } else {
            $namestr = $name;
        }
        ?>
        <a href="<?php echo $url ?>" class="userlink"><?php echo $namestr ?></a> 
        <?php
    }
}

function tpl_user_last_visit($name_or_id, $brackets = true, $does_echo = false) {
    global $id_username_dic, $env;
    if (is_numeric($name_or_id)) {
        $id = intval($name_or_id);
    } else {
        if (empty($id_username_dic)) {
            $id_username_dic = $env->getIDUsernameDictionary();
        }
        $id = array_search($name_or_id, $id_username_dic);
    }
    if ($id) {
        $time = Auth::getLastVisitTime($id);
        if ($time && $time > 0 && !Auth::isSameUser($id)) {
            $timediff = time() - $time;
            $str = '<span class="last_visit_time">' . ($brackets ? "[" : "") . 'Letzter Besuch:' . ($timediff > 60 ? tpl_timediff_span($timediff, false) : "Jetzt") . ($brackets ? "]" : "") . '</span>';
            if ($does_echo) {
                echo $str;
            } else {
                return $str;
            }
        }
    }
    return false;
}

function tpl_timediff_span($timediff, $does_echo = true, $only_time = false) {
    $text = "";
    $arr = array(
        array(1, 60, array("Sekunde", "n", "einer")),
        array(60, 3600, array("Minute", "n", "einer")),
        array(3600, 86400, array("Stunde", "n", "einer")),
        array(86400, 2626560, array("Tag", "en", "einem")),
        array(2626560, 31518720, array("Monat", "en", "einem")),
        array(31518720, 1E10, array("Jahr", "en", "einem"))
    );
    $update_via_js = true;
    foreach ($arr as $steparr) {
        if ($steparr[1] > $timediff) {
            $value = floor($timediff / $steparr[0]);
            $text = ($value == 1 ? $steparr[2][2] : $value) . " " . ($value == 1 ? $steparr[2][0] : $steparr[2][0] . $steparr[2][1]);
            break;
        }
        if ($steparr[0] >= 3600)
            $update_via_js = false;
    }
    $str = '<span class="timediff"' . ($update_via_js ? (' time="' . (time() + $timediff) . '"') : '') . '>' . ($only_time ? '' : 'Vor ') . $text . '</span>';
    if ($does_echo) {
        echo $str;
    } else {
        return $str;
    }
}

function tpl_text($text, $format_with_markdown = false) {
    if ($format_with_markdown) {
        $text = Markdown($text);
    }
    echo $text;
}

function tpl_color_selector($name, $default_value = "#ff0000", $js_onchange = "", $id = "") {
    if ($id == "") {
        $id = $name . rand(0, 100);
    }
    ?>
    <input type="text" name="<?php echo $name ?>" value="<?php echo $default_value ?>" id="<?php echo $id ?>"/>
    <script>
        $('#<?php echo $id ?>').ColorPicker({
            color: '<?php echo $default_value ?>',
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                $('#<?php echo $id ?> div').css('backgroundColor', '#' + hex);
    <?php
    if ($js_onchange != "") {
        echo $js_onchange . (substr($js_onchange, strlen($js_onchange) - 2) != ';' ? ';' : '');
    }
    ?>
            },
            onSubmit: function (hsb, hex, rgb) {
                $('#<?php echo $id ?> div').css('backgroundColor', '#' + hex);
    <?php
    if ($js_onchange != "") {
        echo $js_onchange . (substr($js_onchange, strlen($js_onchange) - 2) != ';' ? ';' : '');
    }
    ?>
            }
        });
    </script>
    <?php
}

function tpl_icon($name, $title = "", $onclick = "", $format = "svg") {
    echo '<img class="icon ' . $name . '" src="' . tpl_url("img/icons/" . $name . '.' . $format) . '" ' . ($title != "" ? (' title="' . $title . '"') : "") . 'onclick="' . $onclick . '"/>';
}

function tpl_popover($text, $title, $content, $class = "") {
    ?>
    <a href="#" rel="popover" data-content="<?php echo $content ?>" data-original-title="<?php echo $title ?>" class="<?php echo $class ?>"><?php echo $text ?></a>
    <?php
}

function tpl_datalist($id, $stringarr) {
    ?>
    <datalist id="<?= $id ?>">
        <? foreach ($stringarr as $str): ?>
            <option value="<?= formatText($str, false) ?>"></option>
        <? endforeach; ?>
    </datalist>
    <?
}