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
 * Ouputs the statictics page
 * 
 * @param array $teacher_with_quote_rating_and_count array of teacher arrays with their quote stats, array("ismale" => ..., "last_name" => ..., "quote_count" => ..., "perc" => ..., "quote_rating" => ...)
 * @param array $teacher_with_rumor_rating_and_count array of teacher arrays with their rumor stats, array("ismale" => ..., "last_name" => ..., "rumor_count" => ..., "perc" => ..., "rumor_rating" => ...)
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
    <div id="quote_stats_table">
        <input class="search" placeholder="Suche" onkeyup="quote_stats_table_list.fuzzySearch($(this).val())" autocomplete="off"/>
        <table class="table table-striped stats teacher_table">
            <thead>
                <tr>
                    <th class="sort" data-sort="qst_gender"></th>
                    <th class="sort" data-sort="qst_name">Name des Lehrers</th>
                    <th class="sort" data-sort="qst_number">Anzahl</th>
                    <th class="sort" data-sort="qst_number">%</th>
                    <th class="sort" data-sort="qst_rating">&Oslash; Bewertung</th>
                </tr>
            </thead>
            <tbody class="list">
                <?php
                foreach ($tarr as $teacher):
                    if ($teacher["quote_count"] == 0)
                        continue;
                    ?>
                    <tr>
                        <td class="qst_gender"><?= $teacher["ismale"] == 1 ? "Herr" : "Frau" ?></td>
                        <td class="qst_name"><?= $teacher["last_name"] ?></td>
                        <td class="qst_number"><?= $teacher["quote_count"] ?></td>
                        <td class="qst_number"><?= round($teacher["perc"], 2) ?></td>
                        <td class="qst_rating"><?= round($teacher["quote_rating"], 2) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <h2>Stimmt es&mldr;-Beiträge mit Lehrername im Text</h2>
    <div id="rumor_stats_table">
        <input placeholder="Suche" onkeyup="rumor_stats_table_list.fuzzySearch($(this).val())" autocomplete="off"/>
        <table class="table table-striped stats teacher_table">
            <thead>
                <tr>
                    <th class="sort" data-sort="rst_gender"></th>
                    <th class="sort" data-sort="rst_name">Name des Lehrers</th>
                    <th class="sort" data-sort="rst_number">Anzahl</th>
                    <th class="sort" data-sort="rst_number">%</th>
                    <th class="sort" data-sort="rst_rating">&Oslash; Bewertung</th>
                </tr>
            </thead>
            <tbody class="list">
                <?php
                foreach ($tarr2 as $teacher):
                    if ($teacher["rumor_count"] == 0)
                        continue;
                    ?>
                    <tr>
                        <td class="rst_gender"><?= $teacher["ismale"] == 1 ? "Herr" : "Frau" ?></td>
                        <td class="rst_name"><?= $teacher["last_name"] ?></td>
                        <td class="rst_number"><?= $teacher["rumor_count"] ?></td>
                        <td class="rst_number"><?= round($teacher["perc"], 2) ?></td>
                        <td class="rst_rating"><?= round($teacher["rumor_rating"], 2) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php
    tpl_item_after();
    tpl_after();
}