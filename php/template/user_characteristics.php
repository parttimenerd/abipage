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

/*user_characteristics(id INT AUTO_INCREMENT PRIMARY KEY, userid INT, type TINYINT, topic INT, text TEXT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics table: " . self::$db->error);
user_characteristics_topics(id INT AUTO_INCREMENT PRIMARY KEY, type TINYINT, text TEXT, position INT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics_topics table: " . self::$db->error);*/

function tpl_all_usercharacteristics_page($itemarr){
    
}

function tpl_usercharacteristics_answer_page($topics){
    
}

?>
