<?

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

class Database {

    private static $db = null;

    private static function connect() {
        if (defined('DB_NAME')) {
            self::$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            if (self::$db->connect_errno) {
                printf("Connect failed: %s\n", self::$db->connect_error);
                self::$db = null;
            }
            if (defined('DEBUG')){
                self::$db = new DebugDBWrapper(self::$db);
            }
        }
    }

    public static function getConnection() {
        if (self::$db == null) {
            self::connect();
        }
        return self::$db;
    }

    public static function setup() {
        self::connect();
        //var_dump("Erzeuge Datenbanktabellen...");
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "user(id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(30), last_name VARCHAR(30), math_course TINYINT, math_teacher VARCHAR(30), mail_adress VARCHAR(40), mode TINYINT, activated TINYINT, visible TINYINT, crypt_str VARCHAR(250), data TEXT, FULLTEXT(first_name, last_name, math_teacher, mail_adress)) ENGINE = MYISAM") or die("Can't create user table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "preferences(`key` VARCHAR(40) PRIMARY KEY, value TEXT, FULLTEXT(`key`, value)) ENGINE = MYISAM") or die("Can't create preferences table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "keyvaluestore(id INT AUTO_INCREMENT PRIMARY KEY, `key` VARCHAR(40), value TEXT, userid INT, FULLTEXT(`key`, value)) ENGINE = MYISAM") or die("Can't create keyvaluestore table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "user_characteristics(id INT AUTO_INCREMENT PRIMARY KEY, userid INT, type TINYINT, topic INT, text TEXT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "user_characteristics_topics(id INT AUTO_INCREMENT PRIMARY KEY, type TINYINT, text TEXT, position INT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics_topics table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "user_comments(id INT AUTO_INCREMENT PRIMARY KEY, commented_userid INT, commenting_userid INT, text TEXT, time BIGINT, notified_as_bad TINYINT, reviewed TINYINT, isanonymous TINYINT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_comments table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "polls(id INT AUTO_INCREMENT PRIMARY KEY, type TINYINT, question VARCHAR(200), position TINYINT, data TEXT, FULLTEXT(question)) ENGINE = MYISAM") or die("Can't create polls table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "poll_answers(userid INT, pollid INT, type TINYINT, answer TEXT, FULLTEXT(answer)) ENGINE = MYISAM") or die("Can't create poll_answers table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "quotes(id INT AUTO_INCREMENT PRIMARY KEY, person VARCHAR(100), teacherid INT, text TEXT, userid INT, isanonymous TINYINT, time BIGINT, rating FLOAT NOT NULL, rating_count INT NOT NULL, response_to INT, data TEXT, FULLTEXT(person, text)) ENGINE = MYISAM") or die("Can't create quotes table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "quotes_ratings(userid INT, itemid INT, rating TINYINT)") or die("Can't create quotes_ratings table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "rumors(id INT AUTO_INCREMENT PRIMARY KEY, text TEXT, userid INT, isanonymous TINYINT, time BIGINT, rating FLOAT NOT NULL, rating_count INT NOT NULL, response_to INT, data TEXT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create rumours table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "rumors_ratings(userid INT, itemid INT, rating TINYINT)") or die("Can't create rumours_ratings table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "images(id INT AUTO_INCREMENT PRIMARY KEY, userid INT, description TEXT, format VARCHAR(5), time BIGINT, rating FLOAT NOT NULL, rating_count INT NOT NULL, data TEXT, FULLTEXT(description)) ENGINE = MYISAM") or die("Can't create pictures table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "images_ratings(userid INT, itemid INT, rating TINYINT)") or die("Can't create picture_ratings table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "teacher(id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(30), last_name VARCHAR(30), namestr VARCHAR(35), ismale TINYINT, FULLTEXT(namestr)) ENGINE = MYISAM") or die("Can't create teacher table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "news(id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(100), content TEXT, time BIGINT, FULLTEXT(title, content)) ENGINE = MYISAM") or die("Can't create news table: " . self::$db->error);
        self::$db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "actions(id INT AUTO_INCREMENT PRIMARY KEY, userid INT, itemid INT, person VARCHAR(50), type VARCHAR(20), time BIGINT)") or die("Can't create news table: " . self::$db->error);
    }

}