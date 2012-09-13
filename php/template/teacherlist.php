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

function tpl_teacherlist($teacherarr) {
    tpl_before("teacherlist", "Lehrerliste");
    tpl_item_before_form(array());
    ?>
    <table>
        <thead>
            <tr>
                <th>Auswahl</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Geschlecht</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($teacherarr as $teacher) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="' . $teacher["id"] . '" value="' . $teacher["id"] . '"/></td>';
                echo '<td><input type="text" name="' . $teacher["id"] . 'first_name" value="' . $teacher["first_name"] . '"/></td>';
                echo '<td><input type="text" name="' . $teacher["id"] . 'last_name" value="' . $teacher["last_name"] . '"/></td>';
                echo '<td><select style="display: inline;" name="' . $teacher["id"] . 'sex">
                <option value="1"' . ($teacher["ismale"] ? ' selected="selected"' : '') . '>männlich</option>
                <option value="0"' . (!$teacher["ismale"] ? ' selected="selected"' : '') . '>weiblich</option>
            </select></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    </div>
    <hr/>
    <div class="item-footer">
        Ausgewählte Lehrer 
        <button class="btn" type="submit" name="edit">Ändern</button>
        <?php if (Auth::canDeleteTeacher()): ?>
            <button class="btn" type="submit" name="delete">Löschen</button>
        <?php endif ?>
    </div>
    </form>
    </div>     
    <?php tpl_item_before_form(array(), "Lehrer hinzufügen") ?>
    <textarea name="input"></textarea>
    <?php tpl_infobox("", "Bitte geben sie die einzelnen Lehrer Zeile für Zeile ein, je ein Lehrer pro Zeile.
			Das Format der Eingabe sollte folgende sein: '[Herr|Frau] [Vorname] [Nachname]' ([Vorname] ist optional)") ?>
    <?php
    tpl_item_after_send("Lehrer hinzufügen", "add");
    tpl_after();
}