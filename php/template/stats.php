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

function tpl_stats_page($teacher_with_quote_rating_and_count) {
    tpl_before("stats", "Statistik");
    $tarr = $teacher_with_quote_rating_and_count;
    tpl_item_before("Lehrer", "", "stats teacher_stats");
    ?>
    <?php if (Auth::isModerator()): ?>
        <a href="teacherlist">Liste verändern</a><br/>
    <?php endif ?>
    <table class="stats teacher_table">
        <tr>
            <th></th>
            <th>Name</th>
            <th>Zitate</th>
            <th>&Oslash; Bewertung der Zitate</th>
        </tr>
        <?php foreach ($tarr as $teacher): ?>
            <tr>
                <td><?php echo $teacher["ismale"] == 1 ? "Herr" : "Frau" ?></td>
                <td><?php echo $teacher["last_name"] ?></td>
                <td><?php echo $teacher["quote_count"] ?></td>
                <td><?php echo round($teacher["quote_rating"], 2) ?></td>
            </tr>
        <?php endforeach ?>
    </table>
    <script>
        $(".teacher_table").tablesorter();
    </script>
    <?php
    tpl_item_after();
    tpl_after();
}