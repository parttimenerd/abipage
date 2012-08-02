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

function tpl_under_construction() {
    global $env;
    ?>
    <html>
        <head>
            <meta charset="utf-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

            <title><?php echo "Under construction" . $env->title_sep . $env->title ?></title>
            <meta name="author" content="Johannes Bechberger"/>
            <meta name="viewport" content="width=device-width"/>

            <link href="<?php echo tpl_url("css/under_construction.css") ?>" rel="stylesheet"/>
            <link href='http://fonts.googleapis.com/css?family=Voltaire|Josefin+Sans:400,700,600,400italic,300|Just+Me+Again+Down+Here' rel='stylesheet' type='text/css'/>
        </head>
        <body>
            <div id="container">
                <?php tpl_icon("pc_worker") ?><br/>
                The site is currently under construction, it will be accessible soon.
            </div>
        </body>
    </html>
    <?php
}