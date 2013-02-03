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

class Environment {

    private $title_sep = " - ";
    private $__usernamesarr = array("activated" => null, "also_deactivated" => null);
    private $main_dir;
    private $__vars;
    private $__prefhandler;

    public function __construct() {
        $this->__prefhandler = new PreferencesHandler();
        $this->main_dir = dirname(dirname(__FILE__));
        $db = Database::getConnection();
        if ($db != null) {
            $this->__vars = array();
            $res = $db->query("SELECT * FROM " . DB_PREFIX . "preferences") or die($db->error);
            if ($res == null) {
                $prefs = new PreferencesHandler();
                $prefs->updateDB();
            }
            while ($arr = $res->fetch_array()) {
                $var = $arr["value"];
                if ($var == "false" || $var == "true") {
                    $var = $var == "true";
                }
                $this->__vars[$arr["key"]] = $var;
            }
        }
    }

    public function __get($var) {
        if ($var == "url") {
            return URL;
        } else if (array_key_exists($var, $this->__vars)) {
//            var_dump($var, $this->__vars[$var]);
            return $this->__vars[$var];
        } else if ($this->__prefhandler->hasDefault($var)) {
            return $this->__prefhandler->getDefault($var);
        }
        //return $this->{$var};
    }

    public function getUsers() {
        global $db;
        $arr = array();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "user ORDER BY last_name ASC") or die($db->error);
        while ($user = User::getFromMySQLResult($res)) {
            $arr[] = $user;
        }
        return new UserArray($arr);
    }

    //HACK implement search
    public function getUserNames($also_deactivated = false, $search_string = "", $also_unvisible = true) {
        global $db;
        //$key = $also_deactivated ? "also_deactivated" : "activated";
        $app = "";
        if (!$also_deactivated)
            $app = " WHERE activated=1";
        if ($search_string != "") {
            $search_string_c = str_replace(" ", "%", $db->real_escape_string($search_string));
            $app .= ($app == "" ? " WHERE " : " AND ") . "(first_name LIKE \"%$search_string_c%\" OR last_name LIKE \"%$search_string_c%\") ";
        }
        if (!$also_unvisible) {
            $app .= ($app == "" ? " WHERE " : " AND ") . " visible=" . 1;
        }
        //if ($this->__usernamesarr[$key] == null) {
        $arr = array();
        $res = $db->query("SELECT first_name, last_name FROM " . DB_PREFIX . "user" . $app . " ORDER BY last_name ASC") or die($db->error);
        while ($user = $res->fetch_array()) {
            $arr[] = array("first" => $user['first_name'], "last" => $user['last_name'], "both" => $user['first_name'] . " " . $user['last_name']);
        }
        //$this->__usernamesarr[$key] = $arr;
        //}
        return $arr; //$this->__usernamesarr[$key];
    }

    public function getNotActivatedUsers() {
        global $db;
        $arr = array();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "user WHERE activated=0");
        while ($user = User::getFromMySQLResult($res)) {
            $arr[] = $user;
        }
        return new UserArray($arr);
    }

    public function getNotReviewedUserComments() {
        $arr = array();
        $db = Database::getConnection();
        $res = $db->query("SELECT id, text, time, commented_userid, commenting_userid FROM " . DB_PREFIX . "user_comments 
		WHERE reviewed=0 ORDER BY time DESC");
        while ($comment = $res->fetch_array()) {
            $arr[] = $comment;
        }
        return $arr;
    }

    public function hasNotActivatedUsers() {
        global $db;
        $res = $db->query("SELECT count(*) AS n FROM " . DB_PREFIX . "user WHERE activated=0");
        if ($res != null) {
            $arr = $res->fetch_array();
            if ($arr["n"] > 0)
                return true;
        }
        return false;
    }

    public function hasNotReviewedUserComments() {
        global $db;
        $res = $db->query("SELECT count(*) AS n FROM " . DB_PREFIX . "user_comments 
		WHERE reviewed=0 ORDER BY time DESC");
        if ($res != null) {
            $arr = $res->fetch_array();
            if ($arr["n"] > 0)
                return true;
        }
        return false;
    }

    public function getIDUsernameDictionary() {
        $arr = array();
        $db = Database::getConnection();
        $res = $db->query("SELECT id, first_name, last_name FROM " . DB_PREFIX . "user");
        while ($user = $res->fetch_array()) {
            $arr[$user["id"]] = $user["first_name"] . ' ' . $user["last_name"];
        }
        return $arr;
    }

    /**
     * 
     * @param type $new_filename_wo_ext
     * @param type $name
     * @param type $dest_dir
     * @param type $create_thumb
     * @return array|boolean false if failed, else array with exif data (at minimum with "FileName" (path of the file) key) 
     */
    public function uploadImage($new_filename_wo_ext, $name = "uploaded_file", $dest_dir = "", $create_thumb = true) {
        if ($dest_dir == "") {
            $dest_dir = $this->upload_path;
        }
        $img_types = array("jpeg", "gif", "png", "bmp", "jpg");
        if ((!empty($_FILES[$name])) && ($_FILES[$name]['error'] == 0)) {
            $filename = basename($_FILES[$name]['name']);
            $ext = strtolower(substr($filename, strpos($filename, '.') + 1));
            //$arr = explode("/", $_FILES["uploaded_file"]["type"]);
            if (in_array($ext, $img_types) && ($_FILES[$name]["size"] < $this->max_upload_pic_size * 1048576)) {
                $full_img_dir = $this->main_dir . '/' . $dest_dir;
                $thumb_img_dir = $this->main_dir . '/' . $dest_dir . '/thumbs';
                $newname_wo_ext = $full_img_dir . '/' . $new_filename_wo_ext;
                $newname = $newname_wo_ext . '.' . $ext;
                if (file_exists($newname)) {
                    unlink($newname);
                }
                if (!file_exists($full_img_dir)) {
                    mkdir($full_img_dir);
                    if ($create_thumb) {
                        mkdir($thumb_img_dir);
                    }
                }
                $exif = array();
                if ($ext == "jpg" || $ext == "jpeg") {
                    $exif = read_exif_data($_FILES[$name]['tmp_name'], 'ANY_TAG', false);
                } else {
                    $exif["DateTime"] = 0;
                }
                $exif["FilePath"] = $newname_wo_ext . '.' . $this->pic_format;
                $exif["FileName"] = $new_filename_wo_ext . '.' . $this->pic_format;
                resizeImage($this->resize_original_image ? $this->pic_width : -1, $_FILES[$name]['tmp_name'], $newname_wo_ext . '.' . $this->pic_format, $ext);
                if ($create_thumb) {
                    resizeImage($this->thumbnail_width, $_FILES[$name]['tmp_name'], $thumb_img_dir . '/' . $new_filename_wo_ext . '.' . $this->pic_format, $ext);
                }
                return $exif;
            }
        }
        return false;
    }

    /**
     * 
     * @param User|string $to
     * @param string $topic
     * @param string $text
     */
    function sendMail($to, $topic, $text) {
        $toName = is_a($to, "User") ? $to->getName() : User::getByEMailAdress($to)->getName();
        $text = '<html>
<head>
<title>' . $this->title . ' | ' . TITLE . '</title>
</head>
<body style="margin: 0; font-family: Voltaire, Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 18px; color: #333333; background-color: #ffffff;">
<div style="position: fixed; right: 0; left: 0; z-index: 1030; margin-bottom: 0; top: 0	 ; margin-left: -20px; margin-right: -20px; *position: relative; *z-index: 2; overflow: visible; margin-bottom: 18px;">
<div style="min-height: 40px; padding-left: 20px; padding-right: 20px; background-color: #2c2c2c; background-image: -moz-linear-gradient(top, #333333, #222222); background-image: -ms-linear-gradient(top, #333333, #222222); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#333333), to(#222222)); background-image: -webkit-linear-gradient(top, #333333, #222222); background-image: -o-linear-gradient(top, #333333, #222222); background-image: linear-gradient(top, #333333, #222222); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#333333", endColorstr="#222222", GradientType=0); -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1); -moz-box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1); box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1); padding: 5px;  padding-left: 0; padding-right: 0; -webkit-border-radius: 0; -moz-border-radius: 0; border-radius: 0;">
<div style="width: 940px; margin-left: 30px;">
<p style="text-decoration: none; float: left; display: block; padding: 0px 20px 12px; margin-left: -20px; font-size: 20px; font-weight: 200; line-height: 1; color: #999999;"> ' . $this->title . ' </p>
</div></div></div>
<div style="padding-top: 75px; width: 940px; margin-right: auto; margin-left: auto; *zoom: 1; display: table;">
<div style="margin-left: 0px; margin-left: 0px; *zoom: 1; display: table;">
<div>
<div style="width: auto; min-height: 20px; padding: 19px; margin-bottom: 20px; background-color: #f5f5f5; border: 1px solid #eee; border: 1px solid rgba(0, 0, 0, 0.05); -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05); -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05); box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);">
<div>               
Hallo ' . $toName . ',<br/><br/>
' . $text . '
<br/><br/>Ihr ' . $this->title . '-Team
</div></div></div></div></div>
</body>							
</html>';
        if (is_a($to, "User"))
            $to = $to->getMailAdress();
        mail($to, $topic, $text, "From: " . TITLE . "<" . ($this->system_mail_adress != "" ? $this->system_mail_adress : ("info@" . $_SERVER['HTTP_HOST'])) . ">\r\n"
                . "X-Mailer: PHP/" . phpversion() . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=utf8\r\n");
    }

    function sendAdminMail($topic, $text) {
        User::getByMode(User::ADMIN_MODE)->sendMail($topic, $text);
    }

    function sendModeratorMail($topic, $text) {
        User::getByMode(User::MODERATOR_MODE)->exclude(Auth::getUser())->sendMail($topic, $text);
    }

}