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
 * Outputs the '500 Internal Server Error' page with the suitable header
 * 
 * @global Environment $env
 */
function tpl_500() {
    global $env;
    header('HTTP/1.0 500 Internal Server Error');
    tpl_before("fiveoo", "Interner Server Error", $env->fiveoo_subtitle, false, null, "broken_cassete");
    tpl_item_before("", "", "container");
    ?>
    <p>Entschuldigung, sie können die Seite nicht anschauen.</p>
    <p>Bitte laden sie die Seie noch einmal neu und kontaktieren sie gegenbenfalls den Administrator dieser Seite</p>
    <?php
    tpl_item_after();
    tpl_after();
}