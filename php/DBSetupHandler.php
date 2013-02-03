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

class DBSetupHandler extends ToroHandler {

    public static $db_setup_vals = array(
        "DB_HOST" => array("default" => "localhost"),
        "DB_NAME" => array("default" => "db"),
        "DB_USER" => array("default" => "root"),
        "DB_PASSWORD" => array("default" => "", "type" => "password"),
        "DB_PREFIX" => array("default" => "abipage__"),
        "root_name" => array("default" => "Johannes Bechberger", "label" => "Root-Benutzername\n(mit Admin Rechten)"),
        "root_pwd" => array("default" => "test", "type" => "password", "label" => "Root Passwort"),
        "root_pwd2" => array("default" => "test", "type" => "password", "label" => "Root Passwort (Verifizierung)"),
        "root_mathteacher" => array("label" => "Root Mathelehrer"),
        "root_mathcourse" => array("default" => "1", "label" => "Root Mathekurs"),
        "root_mailadress" => array("default" => "test@test.de", "label" => "Root Mailadresse", "type" => "email"),
        "URL" => array("default" => "", "label" => "URL"),
    );

    public function __construct() {
        self::$db_setup_vals["DB_HOST"]["default"] = $_SERVER["SERVER_NAME"];
        self::$db_setup_vals["URL"]["default"] = 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    public function get() {
        if (!defined('DB_NAME')) {
            tpl_dbsetup(self::$db_setup_vals);
        } else {
            tpl_404();
        }
    }

    public function post() {
        if (!defined('DB_NAME')) {
            if (issetArray(self::$db_setup_vals, $_POST) && $_POST["root_pwd"] == $_POST["root_pwd2"]) {
                $str = "<?php\n";
                foreach (self::$db_setup_vals as $key => $value) {
                    if (!preg_match("/root.*/", $key)) {
                        $val = $_POST[$key];
                        if ($key == "URL" && substr($val, strlen($val) - 1) == "/") {
                            $val = substr($val, 0, strlen($val) - 1);
                        }
                        $str .= "\ndefine(\"" . $key . '", "' . $val . '");';
                    }
                }
                //For debug purposes...
                /* define("DB_HOST", "localhost");
                  define("DB_NAME", "db");
                  define("DB_USER", "root");
                  define("DB_PASSWORD", "");
                  define("DB_PREFIX", "abipage_" . time() . "_"); */
                $err = $this->testPOSTParameters();
                if ($err != "") {
                    tpl_dbsetup(self::$db_setup_vals, $err);
                    return;
                }
                file_put_contents(dirname(__FILE__) . '/db_config.php', $str);
                $uploads_dir = dirname(dirname(__FILE__)) . '/uploads';
                if (!is_dir($uploads_dir)) {
                    mkdir($uploads_dir);
                }
                if (!is_dir($uploads_dir . '/thumbs')) {
                    mkdir($uploads_dir . '/thumbs');
                }
                $htaccess_file = dirname(dirname(__FILE__)) . "/.htaccess";
                $htaccess = file_get_contents($htaccess_file);
                $uri = str_replace($_SERVER["SERVER_NAME"], "", str_replace('http://', "", $_POST["URL"]));
                file_put_contents($htaccess_file, str_replace("/abipage/", $uri, $htaccess));
                require dirname(__FILE__) . '/bootloader.php';
//                var_dump("setup");
                Database::setup();
                global $db;
                $db = Database::getConnection();
                if ($db != null) {
                    User::create($_POST["root_name"], $_POST["root_mathcourse"], $_POST["root_mathteacher"], $_POST["root_mailadress"], $_POST["root_pwd"], User::ADMIN_MODE, 1);
                    Auth::login($_POST["root_name"], $_POST["root_pwd"]);
                    $prefs = new PreferencesHandler();
                    $prefs->updateDB();
                    global $env;
                    $env = new Environment();
                    $prefs->get();
                } else {
                    $this->get();
                }
            } else {
                $this->get();
            }
        } else {
            tpl_404();
        }
    }

    public function testPOSTParameters() {
        //($host, $user, $password, $database, $port, $socket)
        $db = @new mysqli($_POST["DB_HOST"], $_POST["DB_USER"], $_POST["DB_PASSWORD"], $_POST["DB_NAME"]);
        if ($db != null && $db->connect_errno)
            return $db->connect_error;
        $db->close();
        return "";
    }

}