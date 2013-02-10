<?php
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

function tpl_visu_begin($title, $program_used, $program_used_link, $use_jquery = true) {
    global $env;
    ?>
    <html>
        <head>
            <meta charset="utf-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

            <title><?php echo ($title != null ? ($title . $env->title_sep . $env->title) : '') ?></title>
            <meta name="author" content="Johannes Bechberger"/>
            <meta name="viewport" content="width=device-width"/>
            <? if (defined("UNMINIFIED_SOURCE")): ?>
                <link href="<?php echo tpl_url("css/project.css") ?>" rel="stylesheet"/>   
            <? else: ?>
                <link href="<?php echo tpl_url("css/project.min.css") ?>" rel="stylesheet"/>   
            <? endif; ?>
            <link href="<?php echo tpl_url("css/style.css?42") ?>" rel="stylesheet"/>
            <link rel = "shortcut icon" href = "<?php echo tpl_url($env->favicon) ?>"/>
            <!--Le HTML5 shim, for IE6-8 support of HTML5 elements-->
            <!--[if lt IE 9]>
            <script src = "http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
            <link href='http://fonts.googleapis.com/css?family=PT+Sans|Josefin+Sans:400,700,italic,300|Just+Me+Again+Down+Here' rel='stylesheet' type='text/css'/>

            <? if ($env->has_piwik) PiwikHelper::echoJSTrackerCode(true, $title) ?>
            <? if ($use_jquery): ?>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
                <script>window.jQuery || document.write('<script src="<?php echo tpl_url("js/lib/jquery-1.7.2.js") ?>"><\/script>')</script>
            <? endif ?>
        </head>
        <body>
            <a href="<?= tpl_url("") ?>">&laquo; Zurück zur Hauptseite</a> 
            <h3><?= $title ?> (Realisiert mit Hilfe von <a href="<?= $program_used_link ?>"><?= $program_used ?></a>)</h3>
            <?
        }

        function tpl_visu_end() {
            ?>
        </body>
    </html>
    <?
}

function tpl_user_connection_visu_page($as_page = true) {
    if ($as_page) {
        tpl_visu_begin("Benutzerbeziehungsvisualisierung", "MooWheel", "http://labs.unwieldy.net/moowheel/", false);
    }
    ?>
    <script type="text/javascript" src="<?= tpl_url("js/visu_libs/moowheel/mootools-1.2-core-nc.js") ?>"></script>
    <script type="text/javascript" src="<?= tpl_url("js/visu_libs/moowheel/mootools-1.2-more.js") ?>"></script>      
    <script type="text/javascript" src="<?= tpl_url("js/visu_libs/moowheel/canvastext.js") ?>"></script>
    <script type="text/javascript" src="<?= tpl_url("js/visu_libs/moowheel/moowheel.js") ?>"></script>

    <style type="text/css" media="screen">
        html, body {
            padding: 0;
            margin: 0;
        }

        body {
            text-align: left;
            background-color: white;
            padding: 10px 0 0 10px;
        }

        canvas#canvas {
            display: block;
            border: 1px solid #fff;
            background-color: white;
            margin: 10px 0;
        }

        a {
            color: black;
            font: 500 14px "PT Sans", tahoma, verdana, arial, sans-serif;
            /*}*/
        </style> 
        <script type="text/javascript">
            var wc_url = "<?= tpl_url("user_connection_visu/ajax") ?>";
            var wc = null;
            var data = null;
            window.onload = function() {
                new Request.JSON({url: wc_url, onSuccess: function(data) {
                        window.data = data;
                        initMooWheel(4);
                    }}).get();

    //                $('switch').addEvent('click', function() {
    //                    var which = $('switch').getElement('span');
    //
    //                    $('canvas').empty();
    //                    if (window.data !== null) {
    //                        switch (which.innerHTML) {
    //                            case 'Cold':
    //                                wc = new MooWheel(window.data, $('canvas'), {type: 'cold', radialMultiplier: 10, lines: {color: '#fff'}});
    //                                which.set('html', 'Heat').setStyle('color', '#e64545');
    //                                wc.type = "cold";
    //                                break;
    //                            case 'Heat':
    //                                wc = new MooWheel(window.data, $('canvas'), {type: 'heat', radialMultiplier: 10, lines: {color: '#fff'}});
    //                                which.set('html', 'Cold').setStyle('color', '#308eee');
    //                                break;
    //                        }
    //                    }
    //                });
            };
            function initMooWheel(radialMultiplier) {
                wc = new MooWheel(window.data, $('canvas'), {type: 'heat', radialMultiplier: radialMultiplier, lines: {color: 'black'}});
            }
        </script>
        <!--&nbsp; <a href="#" id="switch">Switch to <span style="color:#308eee;">Cold</span> Wheel</a>-->
        <div id="canvas"></div>
        Erzeugt auf Basis von <?= User::getNumberOfUserComments() ?> Benutzerkommentaren.
        <?
        if ($as_page) {
            tpl_visu_end();
        }
    }
    ?>
