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
 * Outputs the userlist page
 * 
 * @param array $usernamearr array of user names, array(array("both" => ...), ...)
 */
function tpl_userlist($usernamearr, $phrase = "", $ajax = false) {
    global $env;
    if (!$ajax) {
        tpl_before("user/all", "Schülerliste", "", array("phrase" => $phrase));
        tpl_add_js("search_time_buffer = " . $env->search_update_interval);
    }
    tpl_all_userpages_infobox();
    tpl_item_before("", "", "content-item userlist");
    ?>
    <ui>
        <?php foreach ($usernamearr as $namearr): ?>
            <li>
                <?php tpl_userlink($namearr["both"], true) ?>
                <? if (Auth::isModerator()) tpl_user_last_visit($namearr["both"], true, true) ?>
            </li>
        <?php endforeach ?>
    </ui>
    <?php
    tpl_item_after();
    if (!$ajax) {
        tpl_after();
    }
}

function tpl_all_userpages_infobox() {
    if (Auth::isViewingResults()) {
        tpl_infobox("", "Gesammelte Benutzerseiten ansehen. Achtung: Laden dieser Seite kann sehr lange dauern.", tpl_url("user/all_pages"));
    }
}

/**
 * Outputs the preferences page of this user
 * 
 * @param User $user
 */
function tpl_user_prefs(User $user) {
    tpl_before("user/me/preferences");
    tpl_item_before_form(array(), "", "", "userprefs");
    ?>
    <input type="text" title="Name" placeholder="Name ([Vorname] [Nachname])" name="name" value="<?php echo $user->getName() ?>" pattern="((von )?[A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <input type="email" title="E-Mail-Adresse" placeholder="E-Mail-Adresse" name="mail_adress" value="<?php echo $user->getMailAdress() ?>"<?php echo!Auth::isModerator() ? " readonly='readonly'" : "" ?>/><br/>
    <? tpl_new_password_input("register", "password") ?>
    <input type="password" title="Altes Passwort" placeholder="Altes Passwort" name="old_password"/><br/>
    <input type="number" name="math_course" title="Mathekursnummer" placeholder="Mathekursnummer" size="1" value="<?php echo $user->getMathCourse() ?>" min="1" max="20"/><br/>
    <input type="text" name="math_teacher" title="Mathelehrer" placeholder="Mathelehrer" value="<?php echo $user->getMathTeacher() ?>" pattern="((von )?[A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <input type="checkbox" <?= $user->sendEmailWhenBeingCommented() ? "checked" : "" ?> value="true" name="send_email_when_being_commented"/><label>Bei Kommentierung durch andere E-Mail senden?</label>
    <?php
    tpl_item_after_send("Ändern");
    tpl_after();
}

/**
 * Outputs the user page of this user
 * 
 * @global Environment $env
 * @param User $user
 */
function tpl_user(User $user) {
    global $env;
    if (Auth::isSameUser($user)) {
        tpl_before("user/me");
        tpl_infobox("", "Einstellungen verändern", tpl_url("user/me/preferences"));
        if ($env->user_characteristics_editable)
            tpl_infobox("", "Steckbrief editieren", tpl_url("user_characteristics"));
    } else {
        tpl_before("user/" . str_replace(' ', '_', $user->getName()), $user->getName(), tpl_get_user_subtitle($user));
    }
    if ($user->getID() != Auth::getUserID() && $env->user_comments_editable)
        tpl_user_write_comment();
    foreach ($user->getUserComments($user->getID() == Auth::getUserID() || Auth::isModerator()) as $comment)
        tpl_user_comment($user, $comment);
    ?>
    </div>
    <?php
    tpl_after();
}

/**
 * Outputs the write comment form
 *  
 * @global Environment $env
 */
function tpl_user_write_comment() {
    global $env;
    tpl_item_before("Kommentar schreiben", "pencil", "write_comment");
    ?>
    <textarea name="text" id="textarea" placeholder="Kommentar"></textarea>
    <?php
    tpl_infobox("", "Die Kommentare müssen teilweise von einem " . (Auth::isModerator() ? "anderen " : "") . "Moderator oder Administrator freigeschalten werden.");
    tpl_item_after_send_anonymous("Absenden", "Anonym absenden", "sendUserComment(false)", "sendUserComment(true)");
}

function tpl_user_comment_not_reviewed_info() {
    tpl_infobox("", "Ihr Kommentar muss noch moderiert werden");
}

/**
 * Outputs a user comment
 * 
 * @param mixed $user user or user id
 * @param array $comment user comment
 */
function tpl_user_comment($user, $comment) {
    tpl_item_before("", "", $comment["notified_as_bad"] ? " notified_as_bad" : "", $comment["id"]);
    echo $comment["text"];
    ?>
    </div>
    <hr/>
    <div class="item-footer">
        <ul>
            <li><? tpl_time_span($comment["time"]) ?></li>
            <li><? tpl_user_span($comment["commenting_userid"], true, $comment["isanonymous"], Auth::isModerator() && !Auth::isSameUser($user)) ?></li>
            <li><? if (Auth::isSameUser($user)): ?>
                    <span class="notify_as_bad">
                        <?php if ($comment["notified_as_bad"]) { ?>
                            <button class="btn icon notify" onclick="userCommentNotify('<?php echo $comment["id"] ?>')"></button>
                        <?php } else { ?>
                            <button class="btn icon notify" onclick="userCommentNotify('<?php echo $comment["id"] ?>')"></span>
                    <?php } ?>
                    </span>
                <? elseif (Auth::canSeeNameWhenSentAnonymous() && $comment["notified_as_bad"]):
                    tpl_icon("dissaprove");
                endif;
                ?>
            </li>
            <li class="delete_span_li"> 
                <? if (Auth::canDeleteUserComment() && !Auth::isSameUser($user) && !Auth::isSameUser($comment["commenting_userid"])): ?>
                    <span class="del_item"><?php tpl_icon("delete", "Löschen", "deleteUserComment('" . $comment["id"] . "')") ?></span>
                <? endif; ?>
            </li>
        </ul>
    </div>
    </div>
    <?php
}

/**
 * Outputs the user page, with results
 * TODO to be developed
 */
function tpl_user_page(User $user, $user_characteristics_items, $as_page = true) {
    if ($as_page) {
        tpl_before("", $user->getName(), tpl_get_user_subtitle($user));
        tpl_all_userpages_infobox();
    }
    tpl_usercharacteristics_result_page($user, $user_characteristics_items, false);
    tpl_user_comment_results($user);
    if ($as_page) {
        tpl_after();
    }
}

function tpl_user_comment_results(User $user) {
    foreach ($user->getUserComments() as $comment) {
        tpl_item_before();
        echo $comment["text"];
        tpl_item_after();
    }
}

/**
 * Outputs the user page, with results
 * TODO to be developed
 * @param array $array $array[] = array("user" => $user, "user_characteristics" => UserCharacteristicsItem::getAll($user));
 */
function tpl_user_pages($array) {
    global $env;
    tpl_before("", "Gesammelte Benutzerseite", $env->all_user_page_results_page_subtitle);
    foreach ($array as $item) {
        tpl_infobox("", $item["user"]->getName() . " (" . tpl_get_user_subtitle($item["user"]) . ")", tpl_url("user/" . $item["user"]->getName()));
        tpl_user_page($item["user"], $item["user_characteristics"], false);
    }
    tpl_after();
}

function tpl_user_not_found($given, $suggestions) {
    global $env;
    header('HTTP/1.0 404 Not Found');
    tpl_before("fourofour", $given . " nicht gefunden", $env->fourofour_subtitle, false, null, "owl");

    tpl_item_before("Meinten sie...");
    foreach ($suggestions as $username) {
        ?><a class="suggestion" href="<?= tpl_url("user/" . $username) ?>"><?= $username ?></a><br/><?
    }
    tpl_item_after();
    tpl_after();
}