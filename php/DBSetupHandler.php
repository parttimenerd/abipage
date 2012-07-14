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
        "DB_PREFIX" => array("default" => "abipage_"),
        "root_name" => array("default" => "Johannes Bechberger", "label" => "Root-Benutzername\n(mit Admin Rechten)"),
        "root_pwd" => array("default" => "test", "type" => "password", "label" => "Root Passwort"),
        "root_pwd2" => array("default" => "test", "type" => "password", "label" => "Root Passwort (Verifizierung)"),
        "root_mathteacher" => array("label" => "Root Mathelehrer"),
        "root_mathcourse" => array("default" => "1", "label" => "Root Mathekurs"),
        "root_mailadress" => array("default" => "test@test.de", "label" => "Root Mailadresse", "type" => "email"),
        "URL" => array("default" => "http://localhost/abipage", "label" => "URL")
    );

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
                        $str .= "\ndefine(\"" . $key . '", "' . $_POST[$key] . '");';
                    }
                }
                //For debug purposes...
                /* define("DB_HOST", "localhost");
                  define("DB_NAME", "db");
                  define("DB_USER", "root");
                  define("DB_PASSWORD", "");
                  define("DB_PREFIX", "abipage_" . time() . "_"); */
                file_put_contents(dirname(__FILE__) . '/db_config.php', $str);
                require dirname(__FILE__) . '/bootloader.php';
                Database::setup();
                global $db;
                $db = Database::getConnection();
                if ($db != null) {
                    User::create($_POST["root_name"], $_POST["root_mathcourse"], $_POST["root_mathteacher"], $_POST["root_mailadress"], $_POST["root_pwd"], User::SUPERADMIN_MODE, 1);
                    Auth::login($_POST["root_name"], $_POST["root_pwd"]);
                    $prefs = new PreferencesHandler();
                    $prefs->fillDBWithDefaultValues();
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

}