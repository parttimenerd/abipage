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

function tpl_admin($userarr, $comments) {
    global $env;
    tpl_before('admin');
    $uspace = get_dir_size($env->main_dir . '/' . $env->upload_path);
    tpl_item_before("Uploadordner", "");
    ?>
    <table>
        <tr>
            <td>Belegter Speicherplatz</td>
            <td><?php echo format_bytes($uspace) ?></td>
        </tr>
        <tr>
            <td>Freier Speicherplatz</td>
            <td><?php echo format_bytes(($env->max_uploads_size * 1048567) - $uspace) ?></td>
        </tr>
        <tr>
            <td>Füllstand</td>
            <td><?php echo round($uspace / ($env->max_uploads_size * 1048567) * 100, 0) ?>%</td>
        </tr>
        <tr>
            <td>Größe der Datenbanktabellen</td>
            <td><?php echo format_bytes(get_db_size()) ?></td>
        </tr>
    </table>
    <?php
    tpl_item_after();
    tpl_item_before("Nicht aktivierte Benutzer");
    tpl_usermanagement($userarr, false, "usermanagement");
    tpl_item_after();
    tpl_item_before("Nicht freigeschaltene Kommentare", "", "management");
    ?>
    <form method="post">
        <table>
            <tr>
                <th>Auswählen</th>
                <th>ID</th>
                <th>Text</th>
                <th>Kommentierter Benutzer</th>
                <th>Kommentierer</th>
                <th>Datum</th>
            </tr>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><input type="checkbox" value="true" name="<?php echo $comment["id"] ?>"/></td>
                    <td><?php echo $comment["id"] ?></td>
                    <td><?php echo $comment["text"] ?></td>
                    <td><?php tpl_userlink($comment["commented_userid"]) ?></td>
                    <td><?php tpl_userlink($comment["commenting_userid"]) ?></td>
                    <td><?php tpl_time_span($comment["time"]) ?></td>
                </tr>
            <?php endforeach ?>
        </table>
        Ausgewählte Kommentare 
        <button class="btn" type="submit" name="review">Freischalten</button>
        <button class="btn" type="submit" name="delete">Löschen</button>
    </form>
    <script>
        $("table").ready(function(){
            $("table").tablesorter();
        });
    </script>
    <?php
    tpl_item_after();
    tpl_after();
}