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
 * Description of ReviewText
 *
 * @author Johannes
 */
class ReviewText {

    const INSULTS_FILENAME = 'insults.json';
    private static $insults = array();
    private static $suspicious_strings = array("**", "<", ">", "..", "...");
    private $words = array();
    private $text = "";

    public function __construct($text) {
        $this->text = $text;
        //$this->words = $this->splitTextIntoWords($text);
        if (empty(self::$insults)){
            $arr = explode("\n", file_get_contents(dirname(__FILE__) . '/resources/' . self::INSULTS_FILENAME));
            self::$insults = array_merge((array) json_decode($arr[1], true), self::$suspicious_strings);
        }
        foreach (array(" ", ".", ";", "\n", "\r", "\t") as $value)
            $this->text = str_replace($value, "", $this->text);
    }

    private function splitTextIntoWords($text) {
        return preg_split("( |\.|,|-|;)", $text, PREG_SPLIT_NO_EMPTY);
    }

    public static function checkText($text) {
        $obj = new ReviewText($text);
        return $obj->check();
    }

    public function check() {
        return $this->_checkText('.*' . $this->text . '.*');
    }

    private function _checkText($text) {
        foreach (self::$insults as $insult){
            if (stristr($text, $insult)){
                if (DEBUG)
                    echo "Stelle: " . stristr($text, $insult) . "; Suchwort: " . $insult . "<br/>\n";
                return false;
            }
        }
        return true;
    }

    private function _checkWords($words) {
        foreach ($words as $word)
            if (!$this->checkWord($word))
                return false;
        return true;
    }

}

?>
