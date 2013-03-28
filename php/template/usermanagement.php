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
 * Outputs the user management table
 * 
 * @param UserArray $userarr users to be shown in the table
 * @param boolean $as_page output as page?
 * @param string $urlapp '<form action="?' . $urlapp . '"'
 * @global Environment $env
 */
function tpl_usermanagement(UserArray $userarr, $as_page = true, $urlapp = "") {
    global $env;
    if ($as_page) {
        tpl_before("usermanagement");
        tpl_item_before();
    }
    ?>
    <div id="user_table">
        <input class="search" placeholder="Suche" onkeyup="user_table_list.fuzzySearch($(this).val())" autocomplete="off"/>
        <form method="post" <?php echo $urlapp != "" ? ('action="?' . $urlapp . '"') : "" ?> class="usermanagement">
            <input type="hidden" name="access_key" value="<?= Auth::getAccessKey() ?>"/>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="sort" data-sort="is_selected" title="Auswählen"></th>
                        <th style="display: none"></th>
                        <th class="sort" data-sort="id">ID</th>
                        <th class="sort" data-sort="first_name">Vorname</th>
                        <th class="sort" data-sort="last_name">Nachname</th>
                        <th class="sort" data-sort="mail_adress">Mailadresse</th>
                        <th class="sort" data-sort="math_course">Mathekurs</th>
                        <th class="sort" data-sort="math_teacher">Mathelehrer</th>
                        <th class="sort" data-sort="user_mode">Modus</th>
                        <th style="display: none"></th>
                        <th class="sort" data-sort="activated">Aktiviert</th>
                        <th class="sort" data-sort="visible">Sichtbar</th>
                        <th class="sort" data-sort="comments_moderated">Kommentare moderiert?</th>
                        <? if ($env->user_characteristics_editable): ?>
                            <th class="sort" data-sort="unanswered_ucquestions">Unbeantwortete Steckbrieffragen</th>
                        <? endif ?>
                        <th>Link</th>
                        <th>Einstellungen</th>
                    </tr>
                </thead>
                <tbody class="list">
                    <?php foreach ($userarr->toArray() as $user): ?>
                        <tr>
                            <td>
                                <? if (!Auth::isSameUser($user) && Auth::canEditUser($user)): ?>
                                    <input type="checkbox" onclick="var elem = $('#selected_<?= $user->getID() ?>');
                        elem.html(elem.html() === 'a' ? 'b' : 'a')" value="true" name="<?php echo $user->getID() ?>" title="Auswählen"/>
                                       <? endif; ?>
                            </td>
                            <td class="is_selected" id="selected_<?= $user->getID() ?>" style="display: none">a</td>
                            <td class="id"><?php echo $user->getID() ?></td>
                            <td class="first_name"><?php echo $user->getFirstName() ?></td>
                            <td class="last_name"><?php echo $user->getLastName() ?></td>
                            <td class="mail_adress"><a href="mailto:<?php echo $user->getMailAdress() ?>"><?php echo $user->getMailAdress() ?></a></td>
                            <td class="math_course"><?php echo $user->getMathCourse() ?></td>
                            <td class="math_teacher"><?php echo $user->getMathTeacher() ?></td>
                            <td class="user_mode"><?php echo tpl_usermode_to_text($user->getMode()) ?></td>
                            <td class="user_mode" style="display:none;"><?= $user->getMode() ?></td>
                            <td class="activated"><?php echo $user->isActivated() ? "Ja" : "Nein" ?></td>
                            <td class="visible"><?php echo $user->isVisible() ? "Ja" : "Nein" ?></td>
                            <td class="comments_moderated"><?php echo $user->isUserMarkedToHaveHisCommentsBeAlwaysModerated() ? "Ja" : "Nein" ?></td>
                            <? if ($env->user_characteristics_editable): ?>
                                <td class="unanswered_ucquestions"><?= $user->getNumberOfUCQuestionsToBeAnswered() ?></td>
                            <? endif ?>
                            <td><a href="<?= tpl_url('user/' . $user->getName()) ?>">Link</a></td>
                            <td><a href="<?= tpl_url('user/' . $user->getName() . '/preferences') ?>">Einstellungen</a></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            Ausgewählte Benutzer  
            <button class="btn" type="submit" name="activate">Aktivieren</button>
            <button class="btn" type="submit" name="deactivate">Deaktivieren</button><br/>
            <?php if (Auth::canSetUserMode()): ?>
                Modus <?php tpl_usermode_combobox("mode") ?>
                <button class="btn" type="submit" name="setmode">setzen</button><br/>
                <button class="btn btn-danger" type="submit" name="delete">Benutzer löschen (Es werden dabei alle Beiträge der betreffenden Benutzer gelöscht!!!)</button><br/>
            <?php endif ?>
            <input type="password" name="password" style="width: 150px" placeholder="Neues Passwort"/>
            <button class="btn" type="submit" name="setpassword">Passwort setzen</button>
            (mit E-Mail Benachrichtigung der jeweiligen Benutzer)<br/>
            <? if (Auth::canSetUserVisibility()): ?>
                <input type="checkbox" checked="checked" name="visible" value="true" style="margin-right: 10px"/>Sichtbar?
                <button class="btn" name="setvisible">Sichtbarkeit setzen</button><br/>
            <? endif ?>
            <input type="checkbox" checked="checked" name="is_marked" value="true" style="margin-right: 10px"/>Werden Kommentare moderiert?
            <button class="btn" name="mark">Setzen</button>
        </form>
    </div>
    <?
    if ($as_page) {
        tpl_item_after();
        tpl_after();
    }
}