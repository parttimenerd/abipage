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
$editor_needed = false;

function tpl_before($class = "", $title = "", $subtitle = "", $subnav = null, $sidebar = false, $header_icon = "") {
    global $env, $store;
    $usermenu = null;
    if (Auth::getUserMode() != User::NO_MODE) {
        $menus = array(
            "images" => array("Bilder", $env->images_subtitle),
            "quotes" => array("Zitate", $env->quotes_subtitle),
            "rumors" => array("Stimmt es...", $env->rumors_subtitle),
            "user/all" => array("Schüler", $env->userall_subtitle)
        );
        if ($env->news_enabled) {
            $meta_dropdown["news"] = array("Nachrichten", $env->news_subtitle);
            if (Auth::canWriteNews())
                $meta_dropdown["news/write"] = array("Nachricht schreiben", $env->news_write_subtitle);
        }
        if ($env->user_polls_open) {
            $menus["userpolls"] = array("Umfragen", $env->userpolls_subtitle);
        }
        $menus["actions"] = array("Aktionen", $env->actions_subtitle);
        if ($env->has_forum) {
            $menus[$env->forum_url] = array("Forum", "");
        }
        if ($env->has_wiki) {
            $menus[$env->wiki_url] = array("Wiki", "");
        }
        $menus["meta"] = array("head" => array("Meta", ""), "dropdown" => array());
        if ($env->stats_open || Auth::isModerator()) {
            $meta_dropdown["stats"] = array("Statistik", $env->stats_subtitle);
        }
        $userapp = array();
        $document_title = "";
        if (Auth::getUser() != null) {
            $user = Auth::getUser();
            $usermenu = array("head" => array("Me", tpl_get_user_subtitle($user)));
            if ($class == "user") {
                $title = $user->getName();
                $subtitle = tpl_get_user_subtitle($user);
            }
            $userapp["user/me"] = array("Benutzerseite", tpl_get_user_subtitle($user));
            $userapp["user/me/preferences"] = array("Einstellungen", $env->userpreferences_subtitle);
            if ($env->user_characteristics_editable) {
                $userapp["user_characteristics"] = array("Steckbrief", $env->uc_subtitle);
            }
            if (Auth::isModerator()) {
                $meta_dropdown["usermanagement"] = array("Benutzerverwaltung", $env->usermanagement_subtitle);
                $meta_dropdown["teacherlist"] = array("Lehrerliste", $env->teacherlist_subtitle);
                $meta_dropdown["admin"] = array("Dashboard", $env->dashboard_subtitle);
                $meta_dropdown["uc_management"] = array("Steckbriefverwaltung", $env->uc_management_subtitle);
                $meta_dropdown["up_management"] = array("Umfragenverwaltung", $env->up_management_subtitle);
            }
            if (Auth::canViewPreferencesPage()) {
                $meta_dropdown["preferences"] = array("Einstellungen", $env->preferences_subtitle);
            }
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
            "terms_of_use" => array("Nutzungsbedigungen", $env->terms_of_use_subtitle),
            "privacy" => array("Datenschutz", $env->privacy_subtitle),
            "impress" => array("Impressum", $env->impress_subtitle),
            "humans.txt" => array("humans.txt", "")
        );
    }
    $additional = array();
    $additional["terms_of_use"] = array("Nutzungsbedigungen", $env->terms_of_use_subtitle);
    $additional["privacy"] = array("Datenschutz", $env->privacy_subtitle);
    $additional["impress"] = array("Impressum", $env->impress_subtitle);
    $additional["humans.txt"] = array("humans.txt", "");
    $additional["fourothree"] = array("Zugriff verboten", $env->fourothree_subtitle);
    $additional["fourofour"] = array("Seite nicht gefunden", $env->fourofour_subtitle);
    if ($class != "") {
        $document_title = $class;
        if (isset($menus[$class])) {
            $arr = $menus[$class];
        } else if ($class == "user") {
            $arr = $usermenu;
        } else if (isset($menus["user"]["dropdown"][$class])) {
            $arr = $menus["user"]["dropdown"][$class];
            $document_title = "user/me/" . $class;
        } else if (isset($menus["meta"]["dropdown"][$class])) {
            $arr = $menus["meta"]["dropdown"][$class];
            $document_title = "meta/" . $class;
        } else if (isset($additional[$class])) {
            $arr = $additional[$class];
            $document_title = "meta/" . $class;
        } else {
            $arr = array("", "");
        }
        if ($title == "") {
            $title = $arr[0];
        }
        if ($subtitle == "") {
            $subtitle = $arr[1];
        }
        PiwikHelper::addCustomVariableJS(3, "Page name", $class, true);
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
            <link href="<?php echo tpl_url("css/project.min.css") ?>" rel="stylesheet"/>   
            <link href="<?php echo tpl_url("css/style.css") ?>" rel="stylesheet"/>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
            <script>window.jQuery || document.write('<script src="<?php echo tpl_url("js/libs/jquery-1.7.2.min.js") ?>"><\/script>')</script>
            <script src="<?php echo tpl_url("js/libs/modernizr-2.5.3.js") ?>"></script>
            <link rel="shortcut icon" href="<?php echo tpl_url($env->favicon) ?>"/>
            <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
            <!--[if lt IE 9]>
                <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
            <![endif]-->
            <link href='http://fonts.googleapis.com/css?family=PT+Sans|Josefin+Sans:400,700,italic,300|Just+Me+Again+Down+Here' rel='stylesheet' type='text/css'/>
            <!--
                Thanks for looking behind the surface of the code.
                Please visit the github repo of the CMS behind the website (https://github.com/parttimenerd/abipage) to find out more about the internals and help developing this program.
                The whole code is licensed under the GNU GPL, so you're able to use parts of it.
            
                The CMS is developed by some (currently one) nerds, please take a look at the humans.txt to find out more about them.
            -->
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
                                            <?php if ($key == "user" && $env->results_viewable && Auth::isEditor()): ?>
                                                <li>
                                                    <input id="result_mode" type="checkbox" onclick="setResultMode($(this).is(':checked'))"<?= $store->result_mode_ud ? 'checked="checked"' : "" ?>>Ergebnisse anzeigen</input>
                                                </li>
                                            <?php endif; ?>
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
            <?
            tpl_enable_javascript();
            tpl_html5_please()
            ?>
            <!--[if lt IE 10]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a>.</p><![endif]-->
            <header class="jumbotron subhead" id="overview">
                <h1><? if ($header_icon != "") tpl_icon($header_icon, "", "", "header-icon") ?><?php echo $title ?></h1>
                <p class="lead">
                    <?php echo $subtitle ?></p>
                <?php
                global $has_sidebar;
                if (($subnav != null && count($subnav) == 4) || $sidebar) {
                    $has_sidebar = true;
                    tpl_subnav($subnav["url_part"], $subnav["page"], $subnav["pagecount"], $subnav["phrase"]);
                } /* else if ($class == "user"){ //HACK
                  $has_sidebar = true;
                  tpl_no_subnav();
                  } */ else {
                    $has_sidebar = $subnav == true;
                    tpl_no_subnav();
                }
            }

            function tpl_after() {
                ?>         </div> <?php
                global $env, $js, $has_sidebar, $editor_needed;
                if (Auth::getUserMode() != User::NO_MODE && $has_sidebar) {
                    tpl_actions_sidebar();
                }
                ?>
    </div>
    <!-- Footer
    ================================================== -->
    <footer class="footer">
        <p>Powered by <a href="https://github.com/parttimenerd/abipage/">abipage</a>.
            Designed and built by Johannes Bechberger with <a href="http://twitter.github.com/bootstrap/">Twitter Bootstrap</a>.
            <a href="<?php echo tpl_url("humans.txt") ?>">humans.txt</a>
        <p><a href="<?= tpl_url("impress") ?>">Impressum</a>. 
            <a href="<?= tpl_url("terms_of_use") ?>">Nutzungsbedingungen</a>. 
            <a href="<?= tpl_url("privacy") ?>">Datenschutz</a>.</p>
    </footer>
    </div><!--/.container -->
    <div class="go_up">
        <a href="#" onclick="scrollToTop()">
            <?php tpl_icon("up", "Zum Seitenanfang") ?>
        </a>
    </div>
    </div><!-- /container -->
    <? if (Auth::canViewLogs()) tpl_log_container() ?>
    <div id="side_bar_helper_div"/>
    <div id="more_helper_div"/>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>
        var access_key = "<?= Auth::getAccessKey() ?>";
        var auto_update_interval = "<?= $env->auto_update_interval ?>";
        var ajax_url = "<?= tpl_url("ajax") ?>";
        var has_sidebar = <?= $has_sidebar ? "true" : "false" ?>;
    </script>
    <?= str_replace("&apos;", "'", str_replace("&quot;", '"', $env->footer_appendix)); ?>
    <script src="<?php echo tpl_url("js/libs/handlebars-1.0.0.beta.6.js") ?>"></script>
    <script src="<?php echo tpl_url("js/libs/bootstrap.min.js") ?>"></script>
    <script src="<?php echo tpl_url("js/plugins.js") ?>"></script>
    <script src="<?php echo tpl_url("js/script.js?5") ?>"></script>
    <script src="<?php echo tpl_url("js/application.js") ?>"></script>
    <? if ($editor_needed): ?>
        <script src="<?php echo tpl_url("js/libs/jquery.wysiwyg.js") ?>"></script>
        <link href="<?php echo tpl_url("css/jquery.wysiwyg.css") ?>" rel="stylesheet"/>
    <? endif ?>
    <?php if ($env->has_piwik) PiwikHelper::echoJSTrackerCode(true, $document_title) ?>
        <script>
    <?php echo $js ?>
    $(".tablesorter").ready(function(){
        $(".tablesorter").tablesorter();
    });
    <? if (Auth::canViewLogs()): ?>
        add_log_object(<?= json_encode(logArray()) ?>);
    <? endif ?>
    </script>
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
            <div class="<?php echo (Auth::getUserMode() != User::NO_MODE && $has_sidebar != false) ? "span9" : "span12" ?> content">
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
                    <div class="<?php echo (Auth::getUserMode() != User::NO_MODE && $has_sidebar) ? "span9 with_sidebar" : "span12 without_sidebar" ?> content">
                        <?php
                    }

                    function tpl_item_before($title = "", $icon = "", $classapp = "", $id = "", $link = "", $link_title = "") {
                        ?>
                        <div class="well item <?php echo $classapp ?>" id="<?php echo $id ?>" style="width: auto">
                            <?php if ($title != ""): ?>
                                <span class="item-header">
                                    <? if ($icon != "") tpl_icon($icon) ?>
                                    <? if ($link != "") echo "<a href=\"$link\"" . ($link_title != "" ? (" title='" . $link_title . "'") : "") . ">" ?>
                                    <?= $title ?>
                                    <? if ($link != "") echo "</a>" ?>
                                </span>
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
                                        <input type="hidden" name="access_key" value="<?= Auth::getAccessKey() ?>"/>
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

                            function tpl_item_after_form($args) {
                                ?>
                            </div>
                            <hr/>
                            <div class="item-footer">
                                <? foreach ($args as $name => $arr): ?>
                                    <button class="btn <?= isset($arr["classapp"]) ? $arr["classapp"] : "" ?> type="<?= isset($arr["type"]) ? $arr["type"] : "submit" ?>" name="<?= $name ?>" title="<?= isset($arr["title"]) ? $arr["title"] : "" ?>">
                                        <? if (isset($arr["icon"])) tpl_icon($arr["icon"]) ?><?= $arr["text"] ?>
                                    </button>
                                <? endforeach; ?>
                            </div>
                            </form>
                        </div>
                        <?php
                    }

                    function tpl_item_after_buttons($args) {
                        ?>
                    </div>
                    <hr/>
                    <div class="item-footer">
                        <? foreach ($args as $text => $arr): ?>
                            <button class="btn <?= isset($arr["classapp"]) ? $arr["classapp"] : "" ?> title="<?= isset($arr["title"]) ? $arr["title"] : "" ?>" onclick="<?= isset($arr["onclick"]) ? $arr["onclick"] : "" ?>">
                                <? if (isset($arr["icon"])) tpl_icon($arr["icon"]) ?><?= $text ?>
                            </button>
                        <? endforeach; ?>
                    </div>
                    </form>
                </div>
                <?php
            }

            function tpl_item_after_send($title = "Senden", $name = "send", $onclick = "", $footerhtmlapp = "") {
                ?>
            </div>
            <hr/>
            <div class="item-footer">
                <button class="btn" <?php echo $onclick == "" ? 'type="submit"' : "" ?> name="<?php echo $name ?>" onclick="<?php echo $onclick ?>"><?php echo $title ?></button>
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
        <button class="btn" <?php echo $onclick1 == "" ? 'type="submit"' : "" ?> name="send" onclick="<?php echo $onclick1 ?>"><?php echo $title1 ?></button>
        <button class="btn" <?php echo $onclick2 == "" ? 'type="submit"' : "" ?> name="send_anonymous" title="Wichtig: Für die Moderatoren und Admins ist der Name sichtbar" onclick="<?php echo $onclick2 ?>"><?php echo $title2 ?></button>
    </div>
    <?php echo ($onclick1 == "" && $onclick2 == "") ? "</form>" : "" ?>
    </div>
    <?php
}

function tpl_add_js($code) {
    global $js;
    $js .= ($js != "" ? "\n" : "") . $code . (substr($code, strlen($code) - 1) != ';' ? ';' : '');
}

function tpl_impress() {
    global $env;
    tpl_before("impress");
    tpl_item_before();
    echo formatText($env->impress_text);
    tpl_item_after();
    tpl_after();
}

function tpl_privacy_policy() {
    global $env;
    tpl_before("privacy");
    tpl_item_before();
    echo formatText($env->privacy_policy);
    tpl_item_after();
    tpl_after();
}

//TODO doesn't work
function tpl_html5_please() {
    ?>
    <div id="h5p-message"></div>
    <script async>
        //        Modernizr.html5please = function(opts){ var passes = true; var features = opts.features.split('+'); var feat; for (var i = -1, len = features.length; ++i < len; ){ feat = features[i]; if (Modernizr[feat] === undefined) window.console && console.warn('Modernizr.' + feat + ' test not found'); if (Modernizr[feat] === false) passes = false; } if (passes){ opts.yep && opts.yep(); return passes; } Modernizr.html5please.cb = opts.nope; var script = document.createElement('script'); var ref = document.getElementsByTagName('script')[0]; var url = 'http://api.html5please.com/' + features.join('+') + '.json?callback=Modernizr.html5please.cb' + (opts.options ? ('&' + opts.options) : '') + '&html'; script.src = url; ref.parentNode.insertBefore(script, ref); return false; }; Modernizr.html5please({ features: "svg-css+svg-img+css-transitions+fontface+form-validation+forms+datalist+filereader", options: "texticon", yep: function(){ /* put your own initApp() here */ }, nope: function(a){ document.getElementById("h5p-message").innerHTML=a.html; } })
    </script>
    <?
}

function tpl_enable_javascript() {
    ?>
    <noscript>
    <div class="alert alert-error">
        Um den vollen Funktionsumfang dieser Webseite zu erfahren, benötigen Sie JavaScript.
        Eine Anleitung wie Sie JavaScript in Ihrem Browser einschalten, befindet sich 
        <a href="http://www.enable-javascript.com/de/" target="_blank">hier</a>.
    </div>
    </noscript>
    <?
}