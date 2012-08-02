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

function tpl_stats_page($teacher_with_quote_rating_and_count, $teacher_with_rumor_rating_and_count) {
    tpl_before("stats", "Statistik");
    $tarr = $teacher_with_quote_rating_and_count;
    $tarr2 = $teacher_with_rumor_rating_and_count;
    tpl_item_before("Lehrer", "", "stats teacher_stats");
    ?>
    <?php if (Auth::isModerator()): ?>
        <a href="teacherlist">Liste verändern</a><br/>
    <?php endif ?>
    <h2>Zitate</h2>
    <table class="stats teacher_table tablesorter">
        <thead>
            <tr>
                <th></th>
                <th>Name des Lehrers</th>
                <th>Anzahl</th>
                <th>%</th>
                <th>&Oslash; Bewertung</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($tarr as $teacher):
                if ($teacher["quote_count"] == 0)
                    continue;
                ?>
                <tr>
                    <td><?= $teacher["ismale"] == 1 ? "Herr" : "Frau" ?></td>
                    <td><?= $teacher["last_name"] ?></td>
                    <td><?= $teacher["quote_count"] ?></td>
                    <td><?= round($teacher["perc"], 2) ?></td>
                    <td><?= round($teacher["quote_rating"], 2) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <h2>Stimm es...-Beiträge mit Lehrername im Text</h2>
    <table class="stats teacher_table tablesorter">
        <thead>
            <tr>
                <th></th>
                <th>Name des Lehrers</th>
                <th>Anzahl</th>
                <th>%</th>
                <th>&Oslash; Bewertung</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($tarr2 as $teacher):
                if ($teacher["rumor_count"] == 0)
                    continue;
                ?>
                <tr>
                    <td><?= $teacher["ismale"] == 1 ? "Herr" : "Frau" ?></td>
                    <td><?= $teacher["last_name"] ?></td>
                    <td><?= $teacher["rumor_count"] ?></td>
                    <td><?= round($teacher["perc"], 2) ?></td>
                    <td><?= round($teacher["rumor_rating"], 2) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <?php
    tpl_item_after();
    tpl_after();
}