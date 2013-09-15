<?
/*
 * Copyright (C) 2013 Parttimenerd
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

function tpl_deleted_items_list($list_array) {
    tpl_before("deleted_items_list", "Gelöschte Beiträge");
    tpl_deleted_items_list_part($list_array[DeletedItemsList::QUOTE], DeletedItemsList::QUOTE);
    tpl_deleted_items_list_part($list_array[DeletedItemsList::RUMOR], DeletedItemsList::RUMOR);
    tpl_deleted_items_list_part($list_array[DeletedItemsList::IMAGE], DeletedItemsList::IMAGE);
    tpl_deleted_items_list_part($list_array[DeletedItemsList::USER_COMMENT], DeletedItemsList::USER_COMMENT);
    tpl_after();
}

function tpl_deleted_items_list_part($deleted_items, $type) {
    if (empty($deleted_items)) {
        return;
    }
    $title = "";
    if ($type == DeletedItemsList::QUOTE) {
        $title = "Gelöschte Zitate";
    } else if ($type == DeletedItemsList::RUMOR) {
        $title = "Gelöschte Stimmt es...-Beiträge";
    } else if ($type == DeletedItemsList::IMAGE) {
        $title = "Gelöschte Bilder";
    } else {
        $title = "Gelöschte Bentuzzerkommentare";
    }
    tpl_item_before($title, "", "deleted_items_list");
    ?>
    <table>
        <thead>
            <tr>
                <td>Autor</td>
                <td>Erstelldatum</td>
                <? if ($type != DeletedItemsList::IMAGE): ?>
                    <? if ($type == DeletedItemsList::QUOTE): ?>
                        <td>Zitierte Person</td>
                    <? elseif ($type == DeletedItemsList::USER_COMMENT): ?>
                        <td>Kommentierter</td>
                        <td>Als schlecht markiert</td>
                    <? endif; ?>
                    <td>Text</td>
                    <td>Anonym?</td>
                <? else: ?>
                    <td>Beschreibung</td>
                    <td>Kategorie</td>
                    <td>Aufnahmedatum</td>
                <? endif; ?>
                <td>Löscher</td>
                <td>Löschdatum</td>
                <td>Löschgrund</td>
            </tr>
        </thead>
        <tbody>
            <? foreach ($deleted_items as $item):
                ?>
                <tr>
                    <td>
                        <?
                        if ($type != DeletedItemsList::USER_COMMENT || (!Auth::isSameUser($item["commented_user"]) || !$item["isanonymous"])) {
                            tpl_userlink($item["authorid"]);
                        }
                        ?>
                    </td>
                    <td><? tpl_time_span($item["author_time"]) ?></td>
                    <? if ($type != DeletedItemsList::IMAGE): ?>
                        <? if ($type == DeletedItemsList::QUOTE): ?>
                            <td><?= $item["person"] ?></td>
                        <? elseif ($type == DeletedItemsList::USER_COMMENT): ?>
                            <td><? tpl_userlink($item["commented_user"]) ?></td>
                            <td><?= $item["notified_as_bad"] ? "Ja" : "Nein" ?></td>
                        <? endif; ?>
                        <td><?= $item["text"] ?></td>
                        <td><?= $item["isanonymous"] ? "Ja" : "Nein" ?></td>
                    <? else: ?>
                        <td><?= $item["description"] ?></td>
                        <td><?= $item["category"] ?></td>
                        <td><? tpl_time_span($item["capture_time"]) ?></td>
                    <? endif; ?>
                    <td><? tpl_userlink($item["deleting_userid"]) ?></td>
                    <td><? tpl_time_span($item["delete_time"]) ?></td>
                    <td><?= $item["delete_cause"] ?></td>
                </tr>
                <?
            endforeach;
            ?>
        </tbody>
    </table>
    <?
    tpl_item_after();
}
?>