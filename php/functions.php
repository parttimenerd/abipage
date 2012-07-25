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

function issetArray($vals_arr, $input_arr) {
    foreach ($vals_arr as $key => $value) {
        if (!isset($input_arr[$key])) {
            return false;
        }
    }
    return true;
}

//$htmlpurifier = null;
//require_once 'libs/markdown/markdown.php';

function formatInputText($text /* , $allow_html = false */) {
//    if ($allow_html) {
//        global $htmlpurifier;
//        if (!$htmlpurifier) {
//            require_once 'libs/htmlpurifier/HTMLPurifier.standalone.php';
//            $htmlpurifier = new HTMLPurifier();
//        }
//        $text = $htmlpurifier->purify($text);
//    } else {
    $arr = array("\'" => "&apos;", '\"' => "&quot;");
    foreach ($arr as $search => $replacement)
        $text = str_replace($search, $replacement, $text);
    $text = strip_tags($text);
//    }
    if (strlen($text < 20000)) {
        return trim($text);
    } else {
        return "";
    }
}

function formatPostArray() {
    $arr = array();
    foreach ($_POST as $key => $value)
        $arr[$key] = formatInputText($value);
    $_POST = $arr;
    return $arr;
}

function cleanInputText($input, $db = null) {
    if ($db == null) {
        global $db;
    }
    return $db->real_escape_string(formatInputText($input));
}

function formatText($text) {
    $arr = array("\'" => "&apos;", '\"' => "&quot;");
    foreach ($arr as $search => $replacement)
        $text = str_replace($search, $replacement, $text);
    return Markdown($text);
}

function register_user_in_forum($user, $password) {
    global $env;
    if ($user != null && $env->has_forum) {
        require_once($env->forum_path . '/SSI.php');
        $regOptions = array(
            'is_guest' => 'true',
            'interface' => 'admin',
            'username' => $user->getName(),
            'email' => $user->getMailAdress(),
            'password' => $password,
            'password_check' => $password,
            'check_reserved_name' => true,
            'check_password_strength' => false,
            'check_email_ban' => false,
            'send_welcome_email' => true,
            'require' => false,
            'memberGroup' => 0,
        );
        require_once($env->main_dir . '/' . $env->forum_path . '/Sources/Subs-Members.php');
        registerMember($regOptions);
        var_dump("Yeah");
    }
}

function register_user_in_wiki($user, $password) {
    global $env;
    if ($user != null && $env->has_wiki) {
        require_once($env->main_dir . '/' . $env->wiki_path . '/maintenance/Maintenance.php');
        $wikiuser = User::newFromName($user->getName());
        $wikiuser->setPassword($password);
        $wikiuser->addToDatabase();
        $wikiuser->saveSettings();
    }
}

function get_in_forum_online_user_count() {
    global $env;
    if ($env->has_forum) {
        require_once($env->forum_path . '/SSI.php');
        $arr = getMembersOnlineStats(array());
        return $arr["num_users_online"];
    }
    return -1;
}

function format_bytes($a_bytes) {
    if ($a_bytes < 1024) {
        return $a_bytes . ' B';
    } elseif ($a_bytes < 1048576) {
        return round($a_bytes / 1024, 2) . ' KiB';
    } elseif ($a_bytes < 1073741824) {
        return round($a_bytes / 1048576, 2) . ' MiB';
    } elseif ($a_bytes < 1099511627776) {
        return round($a_bytes / 1073741824, 2) . ' GiB';
    }
}

$upload_dir_size = -1;

function get_upload_dir_size() {
    global $upload_dir_size;
    if ($upload_dir_size == -1) {
        $upload_dir_size = get_dir_size($env->main_dir . '/' . $env->upload_path);
    }
    return $upload_dir_size;
}

function get_upload_dir_size_mib() {
    return round(get_upload_dir_size() / 1073741824, 2);
}

function get_dir_size($dir) {
    $size = 0;
    if (is_dir($dir)) {
        foreach (scandir($dir) as $d) {
            if ($d != '.' && $d != '..') {
                $size += get_dir_size($dir . '/' . $d);
            }
        }
    } else {
        $size = filesize($dir);
    }
    return $size;
}

function get_db_size() {
    global $db;
    $res = $db->query("SELECT SUM(data_length + index_length) AS 'size' FROM information_schema.TABLES WHERE TABLE_NAME LIKE '" . DB_PREFIX . "%'") or die($db->error);
    if ($res) {
        $arr = $res->fetch_array();
        return intval($arr["size"]);
    }
    return -1;
}

function issetAndNotEmptyArr($keyarr, $arr) {
    foreach ($keyarr as $key) {
        if (!isset($arr[$key]) || $arr[$key] == "") {
            return false;
        }
    }
    return true;
}

function resizeImage($newWidth, $originalFile, $targetFile = "") {
    global $env;
    $exts = array("jpeg", "bmp", "png", "gif");
    if (empty($newWidth) || empty($originalFile) || !in_array(pathinfo($originalFile, PATHINFO_EXTENSION), $exts)) {
        return false;
    }
    if ($targetFile == "") {
        $targetFile = $originalFile;
    } else if (!in_array(pathinfo($targetFile, PATHINFO_EXTENSION), $exts)) {
        return false;
    }
    $func = "imagecreatefrom" . pathinfo($originalFile, PATHINFO_EXTENSION);
    $src = $func($originalFile);
    list($width, $height) = getimagesize($originalFile);
    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }
    $ext_target = pathinfo($targetFile, PATHINFO_EXTENSION);
    $func = "image" . $ext_target;
    $func($tmp, $targetFile, $ext_target != "png" ? $env->pic_quality : floor((100 - $env->pic_quality) / 10));
    return true;
}