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
 * Outputs the admin dashboard
 * 
 * @global Environment $env
 * @param UserArray $userarr users to show in the user management table
 * @param array $comments comments (comment = array('id' => ...)) to show in the user management table
 */
function tpl_admin(UserArray $userarr, $comments) {
    global $env;
    tpl_before('admin');
    $uspace = @get_upload_dir_size();
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
    if (!empty($userarr)) {
        tpl_item_before("Nicht aktivierte Benutzer");
        tpl_usermanagement($userarr, false, "usermanagement");
        tpl_item_after();
    }
    if (!empty($comments)) {
        tpl_item_before("Nicht freigeschaltete Kommentare", "", "management");
        ?>
        <div id="not_reviewed_comments_table">
            <input class="search" placeholder="Suche" onkeyup="not_reviewed_comments_table_list.fuzzySearch($(this).val())" autocomplete="off"/>
            <form method="post">
                <input type="hidden" name="access_key" value="<?= Auth::getAccessKey() ?>"/>
                <table>
                    <thead>
                        <tr>
                            <th title="Auswählen"></th>
                            <th class="sort" data-sort="comment_id">ID</th>
                            <th class="sort" data-sort="comment_text">Text</th>
                            <th class="sort" data-sort="commented_user">Kommentierter Benutzer</th>
                            <th class="sort" data-sort="commenting_user">Kommentierer</th>
                            <th class="sort" data-sort="comment_date">Datum</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        <?php
                        foreach ($comments as $comment):
                            $same = Auth::isSameUser($comment["commenting_userid"]);
                            ?>
                            <tr>
                                <td>
                                    <? if (!$same): ?>
                                        <input type="checkbox" value="true" name="<?php echo $comment["id"] ?>" checked="checked"/>
                                    <? endif; ?>
                                </td>
                                <td class="comment_id"><?php echo $comment["id"] ?></td>
                                <td class="comment_text"><?php echo $comment["text"] ?></td>
                                <td class="commented_user"><?php tpl_userlink($comment["commented_userid"]) ?></td>
                                <td class="commenting_user"><?php tpl_userlink($comment["commenting_userid"]) ?></td>
                                <td><?php tpl_time_span($comment["time"]) ?></td>
                                <td class="comment_date" style="display: none"><?= $comment["time"] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                Ausgewählte Kommentare 
                <button class="btn" type="submit" name="review">Freischalten</button>
                <button class="btn" type="submit" name="delete">Löschen</button>
            </form>
        </div>
        <?php
        tpl_item_after();
    }
    tpl_after();
}