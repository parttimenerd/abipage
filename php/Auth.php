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

class Auth {

    const HASH_ROUNDS = 200;
    const SALT_LENGTH = 40;
    const COOKIE_EXPIRE_DAYS = 300;

    private static $user = -1;
    private static $user_not_activated = false;

    public static function random_string($length = self::SALT_LENGTH) {
        $res = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($res, rand(0, strlen($res) - 1), 1);
        }
        return $str;
    }

    public static function hash($str, $salt, $hash_rounds = self::HASH_ROUNDS) {
        $res = $str;
        for ($i = 0; $i < $hash_rounds; $i++) {
            $res = base64_encode(sha1($res . $salt));
        }
        return $res;
    }

    public static function crypt($pwd, $salt = null, $rounds = self::HASH_ROUNDS) {
        if ($salt == null) {
            $salt = self::random_string();
        }
        return self::hash($pwd, $salt, $rounds) . '$' . $salt;
    }

    public static function verify($id, $pwd) {
        $user = User::getByID($id);
        if ($user != null) {
            if ($user->isActivated()) {
                if (self::cryptCompare($pwd, $user->getCryptStr())) {
                    self::$user = $user;
                    return true;
                }
            } else {
                self::$user_not_activated = true;
            }
        }
        return false;
    }

    public static function updateLastVisitTime() {
        if (Auth::getUser()) {
            Auth::getUser()->updateLastVisitTime();
            Auth::getUser()->updateDB();
        }
    }

    public static function getLastVisitTime($id = -1) {
        if ($id == -1) {
            if (Auth::getUser())
                return Auth::getUser()->getLastVisitTime();
        } else {
            return User::getByID($id)->getLastVisitTime();
        }
    }

    public static function cryptCompare($pwd, $crypt_str) {
        if (strlen($pwd) > 0 && strlen($crypt_str) > 0) {
            $arr = explode('$', $crypt_str);
            $hashed = self::hash($pwd, $arr[1], self::HASH_ROUNDS - 1);
            if ($arr[0] == $hashed) {
                return true;
            } else if ($arr[0] == self::hash($hashed, $arr[1], 1)) {
                return true;
            }
        }
        return false;
    }

    public static function verifyByCookie() {
        if (isset($_COOKIE["abipage_id"]) && isset($_COOKIE["abipage_pwd"])) {
            if (self::verify(intval($_COOKIE["abipage_id"]), $_COOKIE["abipage_pwd"])) {
                return true;
            }
        }
        return false;
    }

    public static function setCookie($id, $pwd, $salt) {
        ob_end_clean();
        setcookie("abipage_id", $id, time() + self::COOKIE_EXPIRE_DAYS * 86400);
        setcookie("abipage_pwd", self::hash($pwd, $salt, 1), time() + self::COOKIE_EXPIRE_DAYS * 86400);
    }

    public static function getCookieID() {
        return isset($_COOKIE["abipage_id"]) ? intval($_COOKIE["abipage_id"]) : -1;
    }

    public static function login($name, $pwd) {
        $user = is_numeric($name) ? User::getById($name) : User::getByName($name);
        if ($user != null) {
            if (self::cryptCompare($pwd, $user->getCryptStr())) {
                self::$user = $user;
                self::setCookie($user->getID(), $pwd, $user->getCryptSalt());
                return true;
            }
        }
        return false;
    }

    public static function logout() {
        if (Auth::getUser() == null)
            return;
        Auth::getUser()->updateAccessKey();
        self::setCookie(Auth::getUserID(), Auth::random_string(), Auth::random_string());
        self::$user = null;
    }

    /**
     * Current user, null if no user is logged in
     * 
     * @return User
     */
    public static function getUser() {
        if (is_int(self::$user)) {
            if (!self::verifyByCookie()) {
                self::$user = null;
            }
        }
        return self::$user;
    }

    public static function getAccessKey() {
        return self::getUser() != null ? self::$user->getAccessKey() : '';
    }

    public static function hasAccess() {
        return self::getUser() ? self::getUser()->compareAccessKey(isset($_REQUEST["access_key"]) ? $_REQUEST["access_key"] : "") : false;
    }

    public static function getUserMode() {
        return self::getUser() != null ? self::$user->getMode() : User::NO_MODE;
    }

    public static function getUserName() {
        return self::getUser() != null ? self::$user->getName() : "";
    }

    public static function getUserID() {
        return self::getUser() != null ? self::$user->getID() : -1;
    }

     public static function isBlocked() {
        return self::getUser() != null ? (self::$user->getMode() == User::BLOCKED_MODE) : false;
    }
    
    public static function isEditor() {
        return self::getUser() != null ? (self::$user->getMode() >= User::EDITOR_MODE) : false;
    }

    public static function isModerator() {
        return self::getUser() != null ? (self::$user->getMode() >= User::MODERATOR_MODE) : false;
    }

    public static function isAdmin() {
        return self::getUser() != null ? (self::$user->getMode() == User::ADMIN_MODE) : false;
    }

    public static function isFirstAdmin() {
        return self::getUserID() == 1;
    }

    public static function isSameUser($user) {
        $id = $user == null ? -2 : (is_numeric($user) ? intval($user) : $user->getID());
        return $id == self::getUserID();
    }

    public static function canEditUser($user) {
        if (is_numeric($user))
            $user = User::getByID(intval($user));
        return $user != null && (Auth::isSameUser($user) || Auth::isAdmin() || (Auth::getUserMode() > $user->getMode() && Auth::getUserMode() < User::EDITOR_MODE));
    }

    public static function isLoggedIn() {
        return self::getUser() != null;
    }

    public static function isNotActivated() {
        return self::$user_not_activated;
    }

    public static function isViewingResults() {
        global $env, $store;
        return $env->results_viewable && $store->result_mode_ud;
    }

    public static function canViewLogs() {
        global $env;
        return ($env->show_logs || (defined("SHOW_LOGS_TO_ADMIN") && SHOW_LOGS_TO_ADMIN) ) && Auth::isAdmin();
    }

    public static function canWriteNews() {
        return Auth::isModerator();
    }

    public static function canVisitSiteWhenUnderConstruction() {
        return Auth::isAdmin();
    }

    public static function canViewPreferencesPage() {
        return Auth::isAdmin();
    }

    public static function canModifyPreferences() {
        return Auth::isAdmin();
    }

    public static function canSeeNameWhenSentAnonymous() {
        return Auth::isModerator();
    }

    public static function canDeleteRucItem() {
        return Auth::isModerator();
    }

    public static function canDeleteUserComment() {
        return Auth::isModerator();
    }

    public static function canDeleteUser() {
        return Auth::isAdmin();
    }

    public static function canAddTeacher() {
        return Auth::isModerator();
    }

    public static function canEditTeacher() {
        return Auth::isModerator();
    }

    public static function canDeleteTeacher() {
        return Auth::isModerator();
    }

    public static function canSetUserMode() {
        return Auth::isModerator();
    }

    public static function canSeeDebugOutput() {
        return Auth::isAdmin();
    }

    public static function canSetUserVisibility() {
        return Auth::isAdmin();
    }

    public static function canEditUserPolls() {
        return Auth::isEditor();
    }

    public static function canViewDashboard() {
        return Auth::isModerator();
    }

    public static function canEditRucItems() {
        return Auth::isModerator();
    }

}