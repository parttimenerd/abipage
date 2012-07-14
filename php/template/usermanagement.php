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

function tpl_usermanagement($userarr, $as_page = true, $urlapp = "") {
    if ($as_page) {
        tpl_before("usermanagement");
        tpl_item_before();
    }
    ?>
    <form method="post" <?php echo $urlapp != "" ? ('action="?' . $urlapp . '"') : "" ?>>
        <table>
            <tr>
                <th>Auswählen</th>
                <th>ID</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Mailadresse</th>
                <th>Mathekurs</th>
                <th>Mathlehrer</th>
                <th>Modus</th>
                <th>Aktiviert</th>
                <th>Link</th>
            </tr>
            <?php foreach ($userarr as $user): ?>
                <tr>
                    <td><input type="checkbox" value="true" name="<?php echo $user->getID() ?>"/></td>
                    <td><?php echo $user->getID() ?></td>
                    <td><?php echo $user->getFirstName() ?></td>
                    <td><?php echo $user->getLastName() ?></td>
                    <td><?php echo $user->getMailAdress() ?></td>
                    <td><?php echo $user->getMathCourse() ?></td>
                    <td><?php echo $user->getMathTeacher() ?></td>
                    <td><?php echo tpl_usermode_to_text($user->getMode()) ?></td>
                    <th><?php echo $user->isActivated() ? "Ja" : "Nein" ?></td>
                    <th><?php tpl_userlink($user->getName()) ?></td>
                </tr>
            <?php endforeach ?>
        </table>
        Ausgewählte Benutzer  
        <button class="btn" type="submit" name="activate">Aktivieren</button>
        <button class="btn" type="submit" name="deactivate">Deaktivieren</button><br/>
        <?php if (Auth::getUserMode() == User::SUPERADMIN_MODE): ?>
            Modus <?php tpl_usermode_combobox("mode") ?>
            <button class="btn" type="submit" name="setmode">setzen</button><br/>
        <?php endif ?>
        <input type="password" style="width: 150px" placeholder="Neues Password"/>
        <button class="btn" type="submit" name="setpassword">Passwort setzen</button>
        (mit E-Mail Benachrichtigung)
    </form>
    <script>
        $(".usermanagement table").tablesorter();
    </script>
    <?
    if ($as_page) {
        tpl_item_after();
        tpl_after();
    }
}