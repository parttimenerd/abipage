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

function tpl_usermode_combobox($name, $preset_modenum = User::NORMAL_MODE, $without_superadmin = false) {
    $arr = array(
        User::MODERATOR_MODE => "Moderator",
        User::EDITOR_MODE => "Editor",
        User::NORMAL_MODE => "Normal"
    );
    if (!$without_superadmin) {
        $arr = array_merge($arr, array(User::ADMIN_MODE => "Administrator"));
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
    if (Auth::isAdmin()) {
        $html .= ($html != "" ? "; " : "") . tpl_usermode_to_text($user->getMode());
    }
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
            tpl_userlink($user_id);
        }
        ?>
    </span>
    <?php
}

function tpl_url($relative_url) {
    global $env;
    return URL . '/' . $relative_url;
}

$id_username_dic = array();

function tpl_userlink($id_or_name) {
    if ($id_or_name != "") {
        global $id_username_dic;
        if (is_int($id_or_name)) {
            if (empty($id_username_dic)) {
                $id_username_dic = $env->getIDUsernameDictionary();
            }
            $name = $id_username_dic[$id];
        } else {
            $name = $id_or_name;
        }
        ?>
        <a href="<?php echo tpl_url('user/' . str_replace(" ", "_", $name)) ?>" class="userlink"><?php echo $name ?></a> 
        <?php
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

function tpl_headerpic() {
    global $env;
    if ($env->has_headerpic) {
        $headerpic_dir = $env->main_dir . '/' . $env->headerpic_path;
        $arr = scandir($headerpic_dir);
        $pic = "";
        foreach (shuffle($arr) as $file) {
            $path = $headerpic_dir . '/' . $file;
            if (substr($file, strlen($file) - 5) != ".txt" && file_exists($path) && is_file($path)) {
                $pic = $env->url . '/' . $env->headerpic_path . '/' . $file;
                break;
            }
        }
        if ($pic != "") {
            ?>
            <img src="<?php echo $pic ?>" width="940" height="198" alt=""/>
            <?php
        }
    }
}

function tpl_icon($name, $title = "", $onclick = "", $format = "svg") {
    echo '<img class="icon ' . $name . '" src="' . tpl_url("img/icons/" . $name . '.' . $format) . '" title="' . $title . '" onclick="' . $onclick . '"/>';
}

function tpl_popover($text, $title, $content, $class = "") {
    ?>
    <a href="#" rel="popover" data-content="<?php echo $content ?>" data-original-title="<?php echo $title ?>" class="<?php echo $class ?>"><?php echo $text ?></a>
    <?php
}