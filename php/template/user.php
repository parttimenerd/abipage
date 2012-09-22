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

function tpl_userlist($usernamearr) {
    tpl_before("user/all", "Schülerliste", "");
    tpl_item_before("", "", "userlist");
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
    tpl_after();
}

function tpl_user_prefs(User $user) {
    tpl_before("user/me/preferences");
    tpl_item_before_form(array(), "", "", "userprefs");
    ?>
    <input type="text" title="Name" placeholder="Name ([Vorname] [Nachname])" name="name" value="<?php echo $user->getName() ?>" pattern="((von )?[A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <input type="email" title="E-Mail-Adresse" placeholder="E-Mail-Adresse" name="mail_adress" value="<?php echo $user->getMailAdress() ?>"<?php echo!Auth::isModerator() ? " readonly='readonly'" : "" ?>/><br/>
    <input type="password" title="Passwort" placeholder="Passwort" name="password" autocomplete="off"/><br/>
    <input type="password" title="Passwort wiederholen" placeholder="Passwort wiederholen" name="password_repeat"/><br/>
    <input type="password" title="Altes Passwort" placeholder="Altes Passwort" name="old_password"/><br/>
    <input type="number" name="math_course" title="Mathekursnummer" placeholder="Mathekursnummer" size="1" value="<?php echo $user->getMathCourse() ?>" min="1" max="20"/><br/>
    <input type="text" name="math_teacher" title="Mathelehrer" placeholder="Mathelehrer" value="<?php echo $user->getMathTeacher() ?>" pattern="((von )?[A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <input type="checkbox" <?= $user->sendEmailWhenBeingCommented() ? "checked" : "" ?> value="true" name="send_email_when_being_commented"/><label>Bei Kommentierung durch andere E-Mail senden?</label>
    <?php
    tpl_item_after_send("Ändern");
    tpl_after();
}

function tpl_user($user) {
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

function tpl_user_write_comment() {
    global $env;
    tpl_item_before("Kommentar schreiben", "pencil", "write_comment");
    ?>
    <textarea name="text" id="textarea" placeholder="Kommentar"></textarea>
    <?php
    if ($env->review_user_comments_automatically)
        tpl_infobox("", "Die Kommentare müssen maschinell, von einem " . (Auth::isModerator() ? "anderen " : "") . "Moderator oder Administrator freigeschalten werden.");
    else
        tpl_infobox("", "Die Kommentare müssen von einem " . (Auth::isModerator() ? "anderen " : "") . "Moderator oder Administrator freigeschalten werden.");

    tpl_item_after_send_anonymous("Absenden", "Anonym absenden", "sendUserComment(false)", "sendUserComment(true)");
}

function tpl_user_comment($user, $comment) {
    tpl_item_before("", "", $comment["notified_as_bad"] ? " notified_as_bad" : "", $comment["id"]);
    echo $comment["text"];
    ?>
    </div>
    <hr/>
    <div class="item-footer">
        <ul>
            <li><? tpl_time_span($comment["time"]) ?></li>
            <li><? tpl_user_span((Auth::isModerator() && !Auth::isSameUser($user)) || !$comment["anonymous"] ? $comment["commenting_userid"] : -1) ?></li>
            <li><? if (Auth::isSameUser($user)): ?>
                    <span class="notify_as_bad">
                        <?php if ($comment["notified_as_bad"]) { ?>
                            <button class="btn icon notify" onclick="userCommentNotify('<?php echo $comment["id"] ?>')"></button>
                        <?php } else { ?>
                            <button class="btn icon notify" onclick="userCommentNotify('<?php echo $comment["id"] ?>')"></span>
                    <?php } ?>
                    </span>
                <? elseif (Auth::isModerator()):
                    $apps = array();
                    if ($comment["isanonymous"])
                       $apps[] = "Anonym abgesendet";
                    if ($comment["notified_as_bad"])
                       $apps[] = "Als schlecht markiert";
                    echo !empty($apps) ? ('<span class="mod_info">[' . join("; ", $apps) . ']</span>') : "";
                endif;
                ?>
            </li>
            <? if (Auth::canDeleteUserComment() && !Auth::isSameUser($user) && !Auth::isSameUser(!$comment["isanonymous"] ? $comment["commenting_userid"] : -1)): ?>
                <li class="delete_span_li"> 
                    <span class="del_item"><?php tpl_icon("delete", "Löschen", "deleteUserComment('" . $comment["id"] . "')") ?></span>
                <? endif; ?>
            </li>
        </ul>
    </div>
    </div>
    <?php
}

function tpl_user_page() {
    
}

function tpl_item_after_user($time, $user) {
    ?>
    </div>
    <div class="item-footer">
        <?php tpl_time_span($time) ?>
        <?php tpl_user_span($user) ?>
    </div>
    </div>
    <?php
}