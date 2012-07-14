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

$js = "";
$has_sidebar = false;

function tpl_before($class = null, $title = null, $subtitle = null, $subnav = null) {
    global $env;
    $usermenu = null;
    if (Auth::getUserMode() != User::NO_MODE || $env->open) {
        $menus = array(
            "images" => array("Bilder", $env->images_subtitle),
            "quotes" => array("Zitate", $env->quotes_subtitle),
            "rumors" => array("Stimmt es...", $env->rumors_subtitle),
            "user/all" => array("Schüler", $env->userall_subtitle)
        );
        if ($env->user_polls_open) {
            $menus["userpolls"] = array("Umfragen", $env->userpolls_subtitle);
        }
        $menus["meta"] = array("head" => array("Meta", ""), "dropdown" => array());
        if ($env->stats_open || Auth::isAdmin()) {
            $meta_dropdown["stats"] = array("Statistiken", $env->stats_subtitle);
        }
        $userapp = array();
        if (Auth::getUser() != null) {
            $user = Auth::getUser();
            $usermenu = array("head" => array($user->getName(), tpl_get_user_subtitle($user)));
            if ($class == "user") {
                $title = $user->getName();
                $subtitle = tpl_get_user_subtitle($user);
            }
            //$userapp["user"] = array("Benutzerseite");
            $userapp["user"] = array("Beutzerseite", tpl_get_user_subtitle($user));
            $userapp["user/me/preferences"] = array("Einstellungen", $env->userpreferences_subtitle);
            if ($env->user_characteristics_editable) {
                $userapp["user_characteristics"] = array("Steckbrief", $env->uc_subtitle);
            }
            if (Auth::isAdmin()) {
                $meta_dropdown["usermanagement"] = array("Benutzerverwaltung", $env->usermanagement_subtitle);
                $meta_dropdown["teacherlist"] = array("Lehrerliste", $env->teacherlist_subtitle);
                $meta_dropdown["admin"] = array("Dashboard", $env->dashboard_subtitle);
                $meta_dropdown["uc_management"] = array("Steckbriefverwaltung", $env->uc_management_subtitle);
                $meta_dropdown["up_management"] = array("Umfragenverwaltung", $env->up_management_subtitle);
            }
            if (Auth::isSuperAdmin()) {
                $meta_dropdown["preferences"] = array("Einstellungen", $env->preferences_subtitle);
            }
            $meta_dropdown["terms_of_use"] = array("Nutzungsbedigungen", $env->terms_of_use_subtitle);
            $meta_dropdown["impress"] = array("Impressum", $env->impress_subtitle);
            $meta_dropdown["humans.txt"] = array("humans.txt", "");
            $menus["meta"]["dropdown"] = $meta_dropdown;
            $usermenu["user_prefs"] = array("Einstellungen", $env->userpreferences_subtitle);
            $userapp["logout"] = array("Abmelden", "");
            $usermenu["dropdown"] = $userapp;
            $menus["user"] = $usermenu;
        }
    } else {
        $menus = array(
            "login" => array("Anmelden", ""),
            "register" => array("Registrieren", ""),
            "terms_of_use" => array("Nutzungsbedigungen", $env->terms_of_use_subtitle)
        );
    }
    if ($class != null) {
        if (isset($menus[$class])) {
            $arr = $menus[$class];
        } else if ($class == "user") {
            $arr = $usermenu;
        } else if (isset($menus["user"]["dropdown"][$class])) {
            $arr = $menus["user"]["dropdown"][$class];
        } else if (isset($menus["meta"]["dropdown"][$class])) {
            $arr = $menus["meta"]["dropdown"][$class];
        } else {
            $arr = array("", "");
        }
        if ($title == null) {
            $title = $arr[0];
        }
        if ($subtitle == null) {
            $subtitle = $arr[1];
        }
    }
    ?>
    <!doctype html>
    <html lang="de">
        <head>
            <meta charset="utf-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

            <title><?php echo ($title != null ? ($title . $env->title_sep . $env->title) : '') ?></title>
            <meta name="author" content="Johannes Bechberger"/>
            <meta name="viewport" content="width=device-width"/>

            <link href="<?php echo tpl_url("css/bootstrap.css") ?>" rel="stylesheet"/>
            <link href="<?php echo tpl_url("css/bootstrap-responsive.css") ?>" rel="stylesheet"/>
            <link href="<?php echo tpl_url("css/docs.css") ?>" rel="stylesheet"/>
            <link href="<?php echo tpl_url("css/style.css") ?>" rel="stylesheet"/>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
            <script>window.jQuery || document.write('<script src="<?php echo tpl_url("js/libs/jquery-1.7.2.min.js") ?>"><\/script>')</script>
            <!-- scripts concatenated and minified via ant build script-->
            <script src="<?php echo tpl_url("js/libs/modernizr-2.5.3.js") ?>"></script>
            <link rel="shortcut icon" href="<?php echo tpl_url($env->favicon) ?>"/>
            <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
            <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
            <link href='http://fonts.googleapis.com/css?family=Voltaire|Josefin+Sans:400,700,600,400italic,300|Just+Me+Again+Down+Here' rel='stylesheet' type='text/css'/>
        </head>

        <!-- Navbar
        ================================================== -->
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="sign-icon">+</span>
                    </button>
                    <a class="brand" href="<?php echo $env->url ?>"><?php echo $env->title ?></a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <?php
                            foreach ($menus as $key => $value):
                                if (!isset($value["dropdown"])):
                                    ?>
                                    <li class="<?php $key == $class ? "active" : "" ?>">
                                        <a href="<?php echo tpl_url($key) ?>"><?php echo $value[0] ?></a>
                                    </li>
                                    <?php
                                else:
                                    $dropdown = $value["dropdown"];
                                    $head = $value["head"];
                                    ?>
                                    <li class="<?php echo (array_key_exists($class, $dropdown) || $key == $class) ? 'active' : '' ?> dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="<?php echo tpl_url($key) ?>">
                                            <?php echo $head[0] ?>
                                            <b class="caret"></b>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($dropdown as $key2 => $value2): ?>
                                                <li><a href="<?php echo tpl_url($key2) ?>"><?php echo $value2[0] ?></a></li>
                                            <?php endforeach ?>
                                        </ul>
                                    </li>
                                <?php
                                endif;
                            endforeach
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div><!-- navbar -->
        <div class="container">
            <div id="h5p-message"></div> <script>window.h5please=function(a){ document.getElementById("h5p-message").innerHTML=a.html }</script> <script async src="http://api.html5please.com/boxshadow+svg-img+cssgradients+fontface+csstransforms.json?callback=h5please&texticon&html"></script>  
            <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
            <header class="jumbotron subhead" id="overview">
                <h1><?php echo $title ?></h1>
                <p class="lead"><?php echo $subtitle ?></p>
                <?php
                global $has_sidebar;
                if ($subnav != null && count($subnav) == 4) {
                    $has_sidebar = true;
                    tpl_subnav($subnav["url_part"], $subnav["page"], $subnav["pagecount"], $subnav["phrase"]);
                } else {
                    $has_sidebar = $subnav == true;
                    tpl_no_subnav();
                }
            }

            function tpl_after() {
                ?>         </div> <?php
            global $env, $js, $has_sidebar;
            if (Auth::getUserMode() != User::NO_MODE && !$env->open && $has_sidebar) {
                tpl_actions_sidebar();
            }
                ?>
    </div>
    <!-- Footer
    ================================================== -->
    <footer class="footer">
        <p>Powered by abipage. Designed and built by Johannes Bechberger with <a href="http://twitter.github.com/bootstrap/">Twitter Bootstrap</a>. <a href="<?php echo tpl_url("humans.txt") ?>">humans.txt</a>
    </footer>
    </div><!--/.container -->
    <div class="go_up">
        <a href="#" onclick="scrollToTop()">
            <?php tpl_icon("up", "Zum Seitenanfang") ?>
        </a>
    </div>
    </div><!-- /container -->
    <?php if ($env->has_piwik) echo $env->piwik_tracking_code ?>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo tpl_url("js/libs/bootstrap.min.js") ?>"></script>
    <script src="<?php echo tpl_url("js/plugins.js") ?>"></script>
    <script src="<?php echo tpl_url("js/script.js") ?>"></script>
    <script src="<?php echo tpl_url("js/application.js") ?>"></script>

    <script>
        piwik_custom_variable(1, "user_mode", "<?php echo tpl_usermode_to_text(Auth::getUserMode()) ?>", "visit");
    <?php echo $js ?>
    </script>
    <div id="side_bar_helper_div"/>
    <div id="more_helper_div"/>
    </body>
    </html>
    <?php
}

function tpl_no_subnav() {
    global $has_sidebar, $env;
    ?>
    </header>
    <div class="container">

        <hr class="no_subnav"/>

        <div class="row">
            <div class="<?php echo (Auth::getUserMode() != User::NO_MODE && !$env->open && $has_sidebar != false) ? "span9" : "span12" ?> content">
                <?php
            }

            function tpl_subnav($url_part, $page, $pagecount, $phrase) {
                global $has_sidebar, $env;
                ?>
                <div class="subnav">
                    <div class="nav nav-pills">
                        <input type="text" name="phrase" id="search_field" placeholder="Suche" value="<?php echo $phrase ?>" onkeypress="if (event.keyCode == 13) search($(this).val())"/>
                    </div>
                </div>	
                </header>

                <div class="row">
                    <div class="<?php echo (Auth::getUserMode() != User::NO_MODE && !$env->open && $has_sidebar) ? "span9" : "span12" ?> content">
                        <?php
                    }

                    function tpl_actions_sidebar() {
                        global $env;
                        ?>
                        <script>
                            var actions_url = "<?php echo tpl_url("ajax/actions"); ?>";
                        </script>
                        <div class="span3 sidebar">
                            <div class="well">
                                <ul class="nav nav-list">
                                    <li class="nav-header" id="action_header">Aktionen</li>
                                    <?php
                                    foreach ($env->getLastActions() as $action) {
                                        echo '<li id="action_' . $action["id"] . '">';
                                        tpl_timediff_span(time() - $action["time"]);
                                        switch ($action["type"]) {
                                            case "add_user_comment":
                                                echo "Kommentar bei ";
                                                tpl_userlink(intval($action["person"]));
                                                break;
                                            case "add_quote":
                                                echo '<a href="' . tpl_url('quotes') . '">Zitat</a> von ' . $action["person"];
                                                break;
                                            case "add_rumor":
                                                echo '<a href="' . tpl_url('rumors') . '">Stimmt es...</a> Beitrag geschrieben';
                                                break;
                                            case "upload_image":
                                                echo '<a href="' . tpl_url('images') . '">Bild</a> hochgeladen';
                                                break;
                                            case "new_user":
                                                tpl_userlink(intval($action["person"]));
                                                echo " registriert";
                                                break;
                                            case "delete_images":
                                                echo '<a href="' . tpl_url('images') . '">Bild</a> gelöscht';
                                                break;
                                            case "delete_quotes":
                                                echo '<a href="' . tpl_url('quotes') . '">Zitat</a> gelöscht';
                                                break;
                                            case "delete_rumors":
                                                echo '<a href="' . tpl_url('rumors') . '">Stimmt es...</a> Beitrag gelöscht';
                                                break;
                                        }
                                        echo "</li>\n";
                                    }
                                    ?>
                                </ul>
                            </div><!--/.well -->
                        </div><!--/span .sidebar-->
                        <?php
                    }

                    function tpl_timediff_span($timediff) {
                        $text = "";
                        $arr = array(
                            60 => array("Sekunde", "n"),
                            60 => array("Minute", "n"),
                            24 => array("Stunde", "n"),
                            30.4 => array("Tag", "e"),
                            12 => array("Monat", "e"),
                            1 => array("Jahr", "e")
                        );

                        $start = 1;
                        $last = array("Sekunde", "n");
                        foreach ($arr as $mul => $val) {
                            //var_dump($timediff, $start * $mul, $val);
                            if ($timediff < $start * $mul) {
                                $t = round($timediff / $start, 0);
                                $text = $t . ' ' . $last[0] . ($t > 1 ? $last[1] : '');
                                break;
                            }
                            $last = $val;
                            //var_dump($start * $mul);
                            $start *= $mul;
                        }
                        ?>
                        <span class="timediff">Vor <?php echo $text ?></span>
                        <?php
                    }

                    function tpl_item_before($title = "", $icon = "", $classapp = "", $id = "") {
                        ?>
                        <div class="well item <?php echo $classapp ?>" id="<?php echo $id ?>">
                            <?php if ($title != ""): ?>
                                <span class="item-header"><?php if ($icon != "") tpl_icon($icon) ?> <?php echo $title ?></span>
                                <hr/>
                            <?php endif ?>
                            <div class="item-content">
                                <?php
                            }

                            function tpl_item_before_form($form_attrs = array(), $title = "", $icon = "", $classapp = "", $id = "") {
                                $attr = "";
                                foreach ($form_attrs as $key => $val) {
                                    $attr .= ' ' . $key . '="' . $val . '"';
                                }
                                ?>
                                <div class="well item <?php echo $classapp ?>" id="<?php echo $id ?>">
                                    <form <?php echo $attr ?> method="POST">
                                        <?php if ($title != ""): ?>
                                            <span class="item-header"><?php if ($icon != "") tpl_icon($icon) ?> <?php echo $title ?></span>
                                            <hr/>
                                        <?php endif ?>
                                        <div class="item-content">
                                            <?php
                                        }

                                        function tpl_item_after() {
                                            ?>
                                        </div>
                                </div>
                                <?php
                            }

                            function tpl_item_after_send($title = "Senden", $name = "send", $onclick = "", $footerhtmlapp = "") {
                                ?>
                            </div>
                            <hr/>
                            <div class="item-footer">
                                <button class="btn" type="<?php echo $onclick == "" ? "submit" : "" ?>" name="<?php echo $name ?>" onclick="<?php echo $onclick ?>"><?php echo $title ?></button>
                                <?php echo $footerhtmlapp ?>
                            </div>
                            <?php echo $onclick == "" ? "</form>" : "" ?>
                        </div>
                        <?php
                    }

                    function tpl_item_after_send_anonymous($title1 = "Senden", $title2 = "Anonym senden", $onclick1 = "", $onclick2 = "") {
                        ?>
                    </div>
                    <hr/>
                    <div class="item-footer">
                        <button class="btn" type="<?php echo $onclick1 == "" ? "submit" : "" ?>" name="send" onclick="<?php echo $onclick1 ?>"><?php echo $title1 ?></button>
                        <button class="btn" type="<?php echo $onclick2 == "" ? "submit" : "" ?>" name="send_anonymous" onclick="<?php echo $onclick2 ?>"><?php echo $title2 ?></button>
                    </div>
                    <?php echo ($onclick1 == "" && $onclick2 == "") ? "</form>" : "" ?>
                </div>
                <?php
            }

            function tpl_add_js($code) {
                global $js;
                $js .= ($js != "" ? "\n" : "") . $code;
            }

            function tpl_impress() {
                global $env;
                tpl_before("impress");
                tpl_item_before();
                echo formatText($env->impress_text);
                tpl_item_after();
                tpl_after();
            }