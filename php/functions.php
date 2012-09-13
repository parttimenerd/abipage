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
    if (strlen($text) < 100000) {
        return $text;
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

function cleanValue($var, $type = "") {
    if ($var == null)
        return null;
    if ($var == "")
        return "";
    if ($type == "") {
        if (is_float($var))
            return floatval($var);
        if (is_numeric($var))
            return floatval($var);
        if ($var == "true" || $var == "false")
            return $var == "true";
        if (is_bool($var))
            return $var;
        return cleanInputText($var);
    } else {
        switch ($type) {
            case "int":
                return intval($var);
            case "float":
                return floatval($var);
            case "":
            case "string":
            default:
                return cleanInputText($var);
        }
    }
}

function formatText($text, $markdown = true) {
    $arr = array("\'" => "&apos;", '\"' => "&quot;", "..." => "…");
    foreach ($arr as $search => $replacement)
        $text = str_replace($search, $replacement, $text);
    if ($markdown)
        $text = Markdown($text);
    return $text;
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
    global $upload_dir_size, $env;
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
    $exts = array("jpeg", "bmp", "png", "gif", "jpg");
    if (empty($newWidth) || empty($originalFile) || !in_array(pathinfo($originalFile, PATHINFO_EXTENSION), $exts)) {
        return false;
    }
    if ($targetFile == "") {
        $targetFile = $originalFile;
    } else if (!in_array(pathinfo($targetFile, PATHINFO_EXTENSION), $exts)) {
        return false;
    }
    $ext = pathinfo($originalFile, PATHINFO_EXTENSION);
    $func = "imagecreatefrom";
    switch ($ext) {
        case "jpg":
            $func .= "jpeg";
            break;
        case "bmp":
            $func .= "wbmp";
            break;
        default:
            $func .= $ext;
    }
    $src = $func($originalFile);
    list($width, $height) = getimagesize($originalFile);
    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }
    $ext_target = pathinfo($targetFile, PATHINFO_EXTENSION);
    $func = "image" . ($ext_target == "jpg" ? "jpeg" : $ext_target);
    if ($ext_target != "bmp")
        $func($tmp, $targetFile, $ext_target != "png" ? $env->pic_quality : floor((100 - $env->pic_quality) / 10));
    else
        $func($tmp, $targetFile);
    return true;
}

function mysqliResultToArr($result) {
    $ret_arr = array();
    if ($result != null) {
        while ($arr = $result->fetch_array())
            $ret_arr[] = $arr;
    }
    return $ret_arr;
}

function jsonAjaxResponseStart() {
    ob_start();
}

define("START_TIME", microtime(true));

function jsonAjaxResponseEndSend($data = array()) {
    $str = ob_get_clean();
    if (Auth::canViewLogs()) {
        $json = array("logs" => logArray());
    } else {
        $json = array();
    }
    $json["data"] = array("html" => $str);
    $json["data"] = array_merge($json["data"], $data);
    echo json_encode($json);
}

function logArray() {
    global $db;
    $arr = array();
    $arr["logs"] = Logger::getLogs();
    $arr["time_logs"] = Logger::getTimeLogs();
    $arr["time_logs"][] = array("msg" => "Database queries", "duration" => round($db->getTime(), 6));
    $arr["db_queries"] = $db->getQueries();
    $arr["time"] = round(microtime(true) - START_TIME, 6);
    return $arr;
}

function jsonAjaxResponse($func = null) {
    jsonAjaxResponseStart();
    if (is_callable($func))
        $data = $func();
    elseif (is_array($func))
        $data = $func;
    jsonAjaxResponseEndSend(is_array($data) ? $data : array());
}

function findInSlug($slug, $param, $type, $not_arr = array(), $single = false) {
    return findInSlugArr(slugToSlugArr($slug), $param, $type, $not_arr, $single);
}

function findInSlugArr($arr, $param, $type, $not_arr = array(), $single = false) {
    $len = count($arr);
    $before_is_not = false;
    if ($param == "" || $single) {
        for ($i = 0; $i < $len; $i++) {
            $a = $arr[$i];
            if ($before_is_not) {
                $before_is_not = array_search($a, $not_arr, true) !== false;
                continue;
            }
            $before_is_not = array_search($a, $not_arr, true) !== false;
            if (($type == "symbol" && $a == $param) || ($type != "symbol" && isType($a, $type) && !$before_is_not))
                return $a;
        }
    } else {
        if ($len == 1 && $single && isType($arr[0], $type)) {
            return $arr[0];
        }
        for ($i = 0; $i < $len - 1; $i++) {
            $a = $arr[$i];
            $val = $arr[$i + 1];
            if ($a == $param && $val != "" && isType($val, $type) && !$before_is_not)
                return cleanValue($val, $type);
            $before_is_not = array_search($a, $not_arr, true) !== false;
        }
    }
    return false;
}

function isType($val, $type) {
    if ($val == null || $val == "")
        return false;
    switch ($type) {
        case "int":
        case "float":
            return is_numeric($val);
        case "":
        case "string":
            return true;
        default:
            return preg_match($type, $val) !== false;
    }
}

function findParamsInSlugArr($slugarr, $params) {
    if (empty($slugarr) || empty($params))
        return array();
    $retarr = array();
    $not_arr = array();
    foreach ($params as $param => $args_or_type) {
        if (!is_array($args_or_type) || array_search("single", $args_or_type, true) === false)
            $not_arr[] = $param;
    }
    foreach ($params as $param => $args_or_type) {
        $ret = null;
        if (is_array($args_or_type)) {
            $type = isset($args_or_type["type"]) ? $args_or_type["type"] : "";
            $single = array_search("single", $args_or_type, true) !== false;
            $ret = findInSlugArr($slugarr, $param, $type, $not_arr, $single);
        } else {
            if (is_int($param)) {//=symbol
                $ret = findInSlugArr($slugarr, $args_or_type, "symbol", $not_arr);
            } else {
                $ret = findInSlugArr($slugarr, $param, $args_or_type, $not_arr);
            }
        }
        if ($ret !== false)
            $retarr[$param] = $ret;
    }
    return $retarr;
}

function slugToSlugArr($slug) {
    if ($slug != "" && $slug != "/") {
        if (substr($slug, 0, 1) == "/")
            $slug = substr($slug, 1);
        return explode('/', $slug);
    }
    return array();
}

/**
 * e.g.: findParamsInSlug("test2/1/test2/2/testen", array("test" => "int", "testen" => array("type" => "string", "single" => true)))
 * @param type $slug
 * @param type $params
 * @return type
 */
function findParamsInSlug($slug, $params) {
    return findParamsInSlugArr(slugToSlugArr($slug), $params);
}