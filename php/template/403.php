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
 * Outputs the '403 Forbidden' page with the suitable header
 * 
 * @global Environment $env
 */
function tpl_403() {
    global $env;
    header('HTTP/1.0 404 Forbidden');
    tpl_before("fourothree", "Zugriff verweigert", $env->fourothree_subtitle, false, null, "fort");
    tpl_item_before("", "", "html_error");
    ?>
    <style>
    </style>
    <p>Entschuldigung, aber ihnen fehlen die nötigen Rechte um diese Seite anzuschauen.</p>
    <p>Das kann an folgendem Gründen liegen:</p>
    <ul>
        <li>sie sind entweder nicht angemeldet</li>
        <li>oder sie versuchen auf eine Seite zuzugreifen, die zur Zeit gesperrt ist</li>
        <li>oder jene Seite ist nur für Benutzer mit bestimmten Privilegien erreichbar</li>
    </ul>
    <?php

    tpl_item_after();
    tpl_after();
}