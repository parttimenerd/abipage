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
 * Outputs an infobox
 * 
 * @param string $strong_text bold text
 * @param string $message_text info text
 * @param string $href link url of this infobox, if non empty string, the infobox will be surrounded by a link tag with this url
 */
function tpl_infobox($strong_text, $message_text, $href = "") {
    ?>
    <?= $href != "" ? ('<a href="' . $href . '">') : '' ?>
    <div class="alert alert-info">
        <?php if ($strong_text != ""): ?>
            <h4 class="alert-heading"><?php echo $strong_text ?></h4>
        <?php endif ?>
        <?php echo $message_text ?>
    </div>
    <?= $href != "" ? '</a>' : '' ?>
    <?php
}

/**
 * Outputs the user mode combobox
 * 
 * @param string $name name attribute
 * @param int $preset_modenum active mode
 * @param boolean $echo_all show all user modes
 */
function tpl_usermode_combobox($name, $preset_modenum = User::NORMAL_MODE, $echo_all = false) {
    $arr = array(
        User::BLOCKED_MODE + 1 => "Gesperrt",
        User::NORMAL_MODE + 1 => "Normal",
        User::EDITOR_MODE + 1 => "Editor",
    );
    if ($echo_all || Auth::isAdmin())
        $arr = array_merge($arr, array(User::MODERATOR_MODE + 1 => "Moderator"));
    if ($echo_all || Auth::isFirstAdmin())
        $arr = array_merge($arr, array(User::ADMIN_MODE + 1 => "Administrator"));
    ?>
    <select style="display: inline;" name="<?php echo $name ?>" class="user_mode_combobox">
        <?php foreach ($arr as $key => $value): ?>
            <option value="<?php echo $key - 1 ?>"<?php if ($key == $preset_modenum) echo ' selected="selected"' ?>>
                <?php echo $value ?>
            </option>
        <?php endforeach ?>
    </select>
    <?php
}

/**
 * Return the string representation of the user mode
 * 
 * @param int $mode user mode
 * @return string string representation
 */
function tpl_usermode_to_text($mode) {
    $arr = array(
        User::ADMIN_MODE + 2 => "Administrator",
        User::MODERATOR_MODE + 2 => "Moderator",
        User::EDITOR_MODE + 2 => "Editor",
        User::NORMAL_MODE + 2 => "Normal",
        User::BLOCKED_MODE + 2 => "Gesperrt",
        User::NO_MODE + 2 => "Gast"
    );
    return $arr[isset($arr[$mode + 2]) ? $mode + 2 : User::NORMAL_MODE + 2];
}

/**
 * Returns the subtitle of the user
 * 
 * @param User $user
 * @return string subtitle
 */
function tpl_get_user_subtitle(User $user) {
    $html = "";
    if ($user->getMathCourse() > 0) {
        $html .= "Mathekurs: " . $user->getMathCourse();
    }
    if ($user->getMathTeacher() != "") {
        $html .= ($html != "" ? "; " : "") . "Mathelehrer: " . $user->getMathTeacher();
    }
    if ($user->isEditor() || $user->isBlocked()) {
        $html .= ($html != "" ? "; " : "") . tpl_usermode_to_text($user->getMode());
    }
    $last_visit = tpl_user_last_visit($user->getID(), false);
    if ($last_visit)
        $html .= ($html != "" ? "; " : "") . $last_visit;
    return $html;
}

/**
 * Outputs the time span of the timestamp
 * 
 * @param int $time unix time stamp
 * @param boolean $with_icon show clock icon?
 * @param string $class content of the css class attribute of the span
 */
function tpl_time_span($time, $with_icon = true, $class = "time") {
    ?>
    <span class="<?php echo $class ?>">
        <?php if ($with_icon) tpl_icon("clock") ?> <?= date("d.m.y", $time) ?> 
        <span class="hour-minutes hidden-phone"><?= date("H:i", $time) ?></span>
    </span>
    <?php
}

function tpl_user_span($user_id = -1, $with_icon = true, $anonymous = false, $can_user_see_name_when_ano = null) {
    if ($can_user_see_name_when_ano === null) {
        $can_user_see_name_when_ano = Auth::canSeeNameWhenSentAnonymous();
    }
    if ($user_id == -1) {
        $anonymous = true;
    }
    ?>
    <span class="user_span">
        <?php
        if ($with_icon) {
            if ($anonymous) {
                tpl_icon("guy_fawkes", "Anonym abgesendet");
            } else {
                tpl_icon("user");
            }
        }
        echo " ";
        if ($can_user_see_name_when_ano || !$anonymous) {
            if ($user_id == Auth::getUserID()) {
                echo '<a href="' . tpl_url("user/me") . '">Me</a>';
            } else {
                tpl_userlink($user_id, false);
            }
        }
        if ($anonymous) {
            if (!$can_user_see_name_when_ano) {
                echo 'Anonym';
            } else if (!$with_icon) {
                echo ' [Anonym]';
            }
        }
        ?>
    </span>
    <?php
}

/**
 * Returns the full url for the given relative url
 * 
 * @param string $relative_url
 * @return string full url
 */
function tpl_url($relative_url) {
    if (substr($relative_url, 0, 4) != "http") {
        $url_part = str_replace(' ', '_', $relative_url);
        if (strstr($url_part, "/") == 0) {
            return URL . $url_part;
        } else {
            return URL . '/' . $url_part;
        }
    } else {
        return $relative_url;
    }
}

/**
 * array("id" => "username", ...)
 * 
 * @var array
 */
$id_username_dic = array();

/**
 * Outputs the userlink for a given user
 * 
 * @global array $id_username_dic array("id" => "username", ...)
 * @global Environment $env
 * @param mixed $id_or_name name of id of the user
 * @param boolean $last_name_first is the last name shown before the first name?
 * @return string url of the user page
 */
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
        return $url;
    }
}

/**
 * Outputs or return the user last visit time span, if the current user is the same as the given user
 * 
 * @global array $id_username_dic array("id" => "username", ...)
 * @global Environment $env
 * @param mixed $name_or_id name of id of the user 
 * @param boolean $brackets show brackets arround the time?
 * @param type $does_echo output the time span?
 * @return string|boolean false, if the current user can't see the last visit time of the user, else html string if $does_echo is false
 */
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
            $str = '<span class="last_visit_time">' . ($brackets ? "[" : "") . 'Letzter Besuch: ' . ($timediff > 60 ? tpl_timediff_span($timediff, $time, false) : "Jetzt") . ($brackets ? "]" : "") . '</span>';
            if ($does_echo) {
                echo $str;
            } else {
                return $str;
            }
        }
    }
    return false;
}

/**
 * Outputs or returns a time difference span
 * 
 * @param int $timediff time difference
 * @param int $time time (to allow the time to be updated automatically by the client js)
 * @param boolean $does_echo output the html code?
 * @param boolean $only_time output/return only time span
 * @return string htlm code if $does_echo is false
 */
function tpl_timediff_span($timediff, $time, $does_echo = true, $only_time = false) {
    $text = "";
    $arr = array(
        0 => array(1, 60, array("Sekunde", "n", "einer")),
        1 => array(60, 3600, array("Minute", "n", "einer")),
        2 => array(3600, 86400, array("Stunde", "n", "einer")),
        3 => array(86400, 2626560, array("Tag", "en", "einem")),
        4 => array(2626560, 31518720, array("Monat", "en", "einem")),
        5 => array(31518720, 1E10, array("Jahr", "en", "einem"))
    );
    $update_via_js = true;
    for ($i = 0; $i < count($arr); $i++) {
        $steparr = $arr[$i];
        if ($steparr[1] > $timediff) {
            $value = floor($timediff / $steparr[0]);
            $text = ($value == 1 ? $steparr[2][2] : $value) . " " . ($value == 1 ? $steparr[2][0] : $steparr[2][0] . $steparr[2][1]);
            break;
        }
        if ($steparr[0] >= 3600)
            $update_via_js = false;
    }
    $str = '<span class="timediff"' . ($update_via_js ? (' time="' . ($time) . '"') : '') . '>' . ($only_time ? '' : 'Vor ') . $text . '</span>';
    if ($does_echo) {
        echo $str;
    } else {
        return $str;
    }
}

/**
 * Outputs the text
 * 
 * @param string $text
 * @param boolean $format_with_markdown format the text as Markdown
 */
function tpl_text($text, $format_with_markdown = false) {
    if ($format_with_markdown) {
        $text = Markdown($text);
    }
    echo $text;
}

/**
 * Output the color selector
 * 
 * @param string $name name of the input
 * @param string $default_value value of the input
 * @param string $js_onchange onchange JavaScript code of the input
 * @param string $id id of the input
 */
function tpl_color_selector($name, $default_value = "#ff0000", $js_onchange = "", $id = "") {
    if ($id == "") {
        $id = $name . rand(0, 100);
    }
    ?>
    <input type="text" name="<?php echo $name ?>" style="background: <?= $default_value ?>" value="<?php echo $default_value ?>" id="<?php echo $id ?>"/>
    <script>
        $('body').ready(function() {
            $('#<?php echo $id ?>').ColorPicker({
                color: '<?php echo $default_value ?>',
                onShow: function(colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function(colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function(hsb, hex, rgb) {
                    $('#<?= $id ?>').css('backgroundColor', '#' + hex);
                    $("#<?= $id ?>").attr("value", "#" + hex);
    <?php
    if ($js_onchange != "") {
        echo $js_onchange . (substr($js_onchange, strlen($js_onchange) - 2) != ';' ? ';' : '');
    }
    ?>
                },
                onSubmit: function(hsb, hex, rgb) {
                    $('#<?php echo $id ?> div').css('backgroundColor', '#' + hex);
    <?php
    if ($js_onchange != "") {
        echo $js_onchange . (substr($js_onchange, strlen($js_onchange) - 2) != ';' ? ';' : '');
    }
    ?>
                }
            });
        });
    </script>
    <?php
}

/**
 * Outputs the icon
 * 
 * @param string $name name of the icon
 * @param string $title value of the title attribute of the icon img tag
 * @param string $onclick value of the onclick attribute of the icon img tag
 * @param string $class_app value appended to the css class attribute of the icon img tag
 * @param boolean $has_container is it surrounded by an container?
 * @param string $format format of the icon
 */
function tpl_icon($name, $title = "", $onclick = "", $class_app = "", $has_container = false, $format = "svg") {
    if ($has_container)
        echo '<div class="icon-container ' . $name . ' ' . $class_app . '">';
    echo '<img class="icon ' . $name . ' ' . $class_app . '" src="' . tpl_url("img/icons/" . $name . '.' . $format) . '" ' . ($title != "" ? (' title="' . $title . '"') : "") . 'onclick="' . $onclick . '"/>';
    if ($has_container)
        echo '</div>';
}

/**
 * Outputs an popover
 * 
 * @param string $text popover text
 * @param string $title title of the popover
 * @param string $content content of the popover
 * @param string $class content of the css class attribute of the popover
 */
function tpl_popover($text, $title, $content, $class = "") {
    ?>
    <a href="#" rel="popover" data-content="<?php echo $content ?>" data-original-title="<?php echo $title ?>" class="<?php echo $class ?>"><?php echo $text ?></a>
    <?php
}

/**
 * Outputs a datalist html element
 * 
 * @param string $id id of the datalist
 * @param array $stringarr data line array
 */
function tpl_datalist($id, $stringarr) {
    ?>
    <datalist <?= $id != "" ? ('id="' . $id . '"') : "" ?>>
        <? foreach ($stringarr as $str): ?>
            <option value="<?= formatText($str, false) ?>"></option>
        <? endforeach; ?>
    </datalist>
    <?
}

/**
 * Outputs an html input element
 * 
 * @param array $args array("value" => "", "label" => "", "type" => "textarea|inputfield|checkbox|password|usermode|color|email", "css_class" => "", "js_onchange" => "")
 */
function tpl_input($args = array("name" => "default", "value" => "", "placeholder" => "", "onchange" => "")) {
    global $editor_needed, $env;
    if ($args["name"] == "default")
        Logger::log("Use this function correct!!!", LOG_INFO);
    $name = $args["name"];
    $value = isset($args["default"]) ? $args["default"] : (isset($args["value"]) ? $args["value"] : "");
    $type = "inputfield";
    if (isset($args["type"])) {
        $type = $args["type"];
    } else if (is_numeric($value)) {
        $type = "number";
    } else if ($value == "true" || $value == "false") {
        $type = "checkbox";
    }
    $id = isset($args["id"]) ? $args["id"] : $name;
    $str = 'name="' . $args["name"] . '" id="' . $id . '"';
    if (isset($args["placeholder"]) && $args["placeholder"] != "")
        $str .= ' placeholder="' . $args["placeholder"] . '" ';
    if (isset($args["js_onchange"]) && $args["js_onchange"] != "")
        $str .= ' onchange="' . $args["js_onchange"] . '" ';
    if (isset($args["required"]) && $args["required"] != "")
        $str .= ' required="' . $args["required"] . '" ';
    switch ($type) {
        case "textarea":
            if ($env->wysiwyg_editor_enabled) {
                ?>
                <div class="textarea_container" id="<?= $id ?>">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#<?= $name ?>_editor" data-toggle="tab">Editor</a></li>
                        <li><a href="#<?= $name ?>_code" data-toggle="tab">Code</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="<?= $name ?>_editor">
                            <textarea class="textarea" onchange="$('#<?= $name ?>_code textarea').val($(this).wysiwyg('getContent'));"><?= $value ?></textarea>
                        </div>
                        <div class="tab-pane" id="<?= $name ?>_code">
                            <textarea <?= $str ?> onkeyup="$('#<?= $name ?>_editor .textarea').wysiwyg('setContent', $(this).val());"><?= $value ?></textarea>
                        </div>
                    </div>
                </div>
                <?
                tpl_add_js('$("#' . $name . '_editor textarea.textarea").wysiwyg({
                            css: "' . tpl_url("css/style.css") . '",
                            i18n: {lang: "de"},
                            rmUnwantedBr: true,
                            controls: {
                                increaseFontSize : { visible : true },
                                decreaseFontSize : { visible : true }
                            },
                            removeHeadings: true
                        }); ');
//                echo '<textarea ' . $str . ' class="textarea">' . $value . '</textarea>';
                $editor_needed = true;
                break;
            }
        case "codearea":
            echo '<textarea ' . $str . ' class="codearea">' . $value . '</textarea>';
            break;
        case "number":
            echo "<input type='number' " . $str . " value='" . $value . "'/>";
            break;
        case "inputfield":
            echo "<input type='text' " . $str . " value=\"" . $value . "\"/>";
            break;
        case "password":
            echo '<input type="password" ' . $str . ' value="' . $value . '"/>';
            break;
        case "checkbox":
            echo '<input type="checkbox" ' . $str . ' value="true"' . ($value == "true" ? ' checked="checked"' : '') . '/>';
            break;
        case "usermode":
            tpl_usermode_combobox($args["name"], $value == "" ? 0 : intval($value));
            break;
        case "color":
            tpl_color_selector($args["name"], $value, isset($args["js_onchange"]) && $args["js_onchange"] != "" ? $args["js_onchange"] : "", $id);
            break;
        case "email":
            echo '<input type="email" ' . $str . ' value="' . $value . '"/>';
            break;
    }
}

/**
 * Adds the time zone offset to the given time
 * @global Environment $env
 * @param int $time time in seconds
 * @return int
 */
function usertime($time = -1) {
    global $env;
    return ($time == -1 ? time() : $time) + $env->time_zone_offset * 3600;
}