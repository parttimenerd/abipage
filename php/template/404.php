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

function tpl_404() {
    global $env;
    tpl_before(null, "Seite nicht gefunden", $env->fourofour_subtitle);
    tpl_item_before("", "", "container");
    ?>
    <style>
        h1 { text-align: center; }
    </style>
    <!--    <h1>Nicht gefunden <span>:(</span></h1>-->
    <p>Entschuldigung, aber die Seite die sie versucht haben anzuschauen existiert nicht.</p>
    <p>Es scheint, das dass ein Ergebnis von einem der folgenden Punkte ist:</p>
    <ul>
        <li>eine falsch eingegebene Adresse</li>
        <li>ein nicht mehr aktueller Link</li>
    </ul>
    <?php

    tpl_item_after();
    tpl_after();
}