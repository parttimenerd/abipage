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

class PiwikHelper {

    private static $goals = array("New user registered", "Rumor written", "Quote written", "Image uploaded", "User commented", "Item written", "Item deleted", "Item rated", "Item rating edited", "Characters typed");
    private static $lines = array();

    public static function setup() {
        global $store;
        foreach (self::$goals as $goal) {
            if (!self::addGoal($goal))
                return false;
        }
        return true;
    }

    private static function addGoal($goal) {
        global $env, $store;
        $arr = $store->piwik_goals ? (array) $store->piwik_goals : array();
        if (isset($arr[$goal]))
            return false;
        $url = $env->piwik_url;
        $url .= "?module=API&method=Goals.addGoal&format=json";
        $url .= "&idSite=" . $env->piwik_site_id;
        $url .= "&name=" . urlencode($goal);
        $url .= "&matchAttribute=manually";
        $url .= "&patternType=regex";
        $url .= "&pattern=.*";
        $url .= "&caseSensitive=0";
        $url .= "&revenue=0";
        $url .= "&allowMultipleConversionsPerVisit=1";
        $url .= "&idGoal=";
        $url .= "&token_auth=" . $env->piwik_token_auth;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $json = curl_exec($ch);
        curl_close($ch);
        $json = (array) json_decode($json);
        if (isset($json["result"]))
            return false;
        $arr[$goal] = $json["value"];
        $store->piwik_goals = $arr;
        return true;
    }

    public static function echoJSTrackerCode($with_init = true, $document_title = "") {
        global $env;
        if ($env->has_piwik != null)
            tpl_piwik_js_tracker_code($env->piwik_site_id, $env->piwik_url, self::$lines, $with_init, $document_title);
    }

    public static function addTrackGoalJS($goal, $value = null) {
        $index = self::getIdOfGoal($goal);
        if (is_string($value)) {
            self::addTrackGoalJS("Item written");
            self::addTrackGoalJS("Characters type", strlen($value));
            $value = null;
        }
        self::addJSTrackerCodeLine("piwikTracker.trackGoal(" . $index . ($value != null ? (", " . $value) : "") . ");");
    }

    public static function getIdOfGoal($goal) {
        global $store;
        if ($store->piwik_goals) {
            $json = (array) $store->piwik_goals;
            if (isset($json[$goal]))
                return $json[$goal];
        }
        return -1;
    }

    public static function addCustomVariableJS($index, $name, $value, $visit_bound) {
        self::addJSTrackerCodeLine("piwikTracker.setCustomVariable($index, \"$name\", \"$value\", " . ($visit_bound ? "\"visit\"" : "\"page\"") . ");");
    }

    public static function addJSTrackerCodeLine($line) {
        self::$lines[] = $line;
    }

}

?>
