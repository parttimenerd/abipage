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

if (!defined("LOG_DEBUG")) {
    define("LOG_DEBUG", 23423423);
    define("LOG_INFO", 23423429);
}

/**
 * Description of Logger
 *
 * @author Parttimenerd
 */
class Logger {

    private static $buffer = array();
    private static $time_buffer = array();

    //TODO write Logger

    public static function log($msg, $type = LOG_DEBUG) {
        $arr = self::errTypeToStrArr($type);
        if (!is_string($msg))
            $msg = json_encode($msg);
        self::$buffer[] = array("msg" => $msg, "backtrace" => self::debug_backtrace(), "type" => $type, "type_str" => $arr["type"], "parent_type" => $arr["parent_type"], "time" => round(microtime(true) - BEGIN_TIME, 6));
    }

    public static function getLogs() {
        return self::$buffer;
    }

    public static function debug_backtrace($limit = -1) {
        if ($limit != -1 && version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $arr = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit + 2);
        } else {
            $arr = debug_backtrace(true);
        }
        $retarr = array();
        for ($index = 2; $index < count($arr); $index++) {
            $retarr[] = $arr[$index];
            if (isset($retarr[$index - 2]["file"]))
                $retarr[$index - 2]["file"] = substr($retarr[$index - 2]["file"], strlen(BASE_DIR));
        }
        return $retarr;
    }

    private static function errTypeToStrArr($type) {
        switch ($type) {
            case E_DEPRECATED:
                return array("type" => "deprecated", "parent_type" => "warning");
            case E_COMPILE_ERROR:
                return array("type" => "compile error", "parent_type" => "error");
            case E_COMPILE_WARNING:
                return array("type" => "compile warning", "parent_type" => "warning");
            case E_CORE_ERROR:
                return array("type" => "core error", "parent_type" => "error");
            case E_CORE_WARNING:
                return array("type" => "core warning", "parent_type" => "warning");
            case E_ERROR:
                return array("type" => "error", "parent_type" => "error");
            case E_NOTICE:
                return array("type" => "notice", "parent_type" => "notice");
            case E_PARSE:
                return array("type" => "parse", "parent_type" => "error");
            case E_RECOVERABLE_ERROR:
                return array("type" => "recoverable error", "parent_type" => "warning");
            case E_USER_DEPRECATED:
                return array("type" => "user deprecated", "parent_type" => "warning");
            case E_USER_ERROR:
                return array("type" => "user error", "parent_type" => "error");
            case E_USER_NOTICE:
                return array("type" => "user notice", "parent_type" => "notice");
            case E_USER_WARNING:
                return array("type" => "user warning", "parent_type" => "warning");
            case LOG_INFO:
                return array("type" => "info", "parent_type" => "info");
            case LOG_DEBUG:
            default:
                return array("type" => "debug", "parent_type" => "debug");
        }
    }

    public static function getTimeLogs() {
        $arr = array();
        foreach (self::$time_buffer as $key => $value) {
            $arr[] = array_merge(array("msg" => $key), $value);
        }
        return $arr;
    }

    public static function startTimeLog($time_log_title) {
        self::$time_buffer[$time_log_title] = array("start_time" => round(microtime(true) - BEGIN_TIME, 6));
    }

    public static function stopTimeLog($time_log_title) {
        if (isset(self::$time_buffer[$time_log_title])) {
            self::$time_buffer[$time_log_title]["stop_time"] = round(microtime(true) - BEGIN_TIME, 6);
            $arr = self::$time_buffer[$time_log_title];
            self::$time_buffer[$time_log_title]["duration"] = $arr["stop_time"] - $arr["start_time"];
            return self::$time_buffer[$time_log_title]["duration"];
        }
        return -1;
    }

    public static function addTimeLog($time_log_title, $duration, $start_time = -1, $stop_time = -1) {
        self::$time_buffer[$time_log_title] = array("duration" => round($duration, 6));
        if ($start_time != -1)
            self::$time_buffer[$time_log_title]["start_time"] = round($start_time, 6);
        if ($stop_time != -1)
            self::$time_buffer[$time_log_title]["stop_time"] = round($stop_time, 6);
    }

}

?>
