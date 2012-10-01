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
 * JavaScript code to append to the JS code in the footer 
 * @var string
 */
$js = "";
/**
 * Has the page to be echoed with the actions sidebar?
 * @var boolean
 */
$has_sidebar = false;
/**
 * Must the footer include the visual editor sources?
 * @var boolean
 */
$editor_needed = false;

/**
 * Outputs the header of the page
 * 
 * @global Environment $env
 * @global KeyValueStore $store
 * @global boolean $has_sidebar has the page to be echoed a sidebar?
 * @param string $class css class of the content of the page, has also to be it's url part (i.e. 'user' for the user page)
 * @param string $title title of the page, only used when no title is stored for the current css class
 * @param string $subtitle subtitle of the page, only used when no title is stored for the current css class
 * @param mixed $subnav false or null if the page needs a search bar, else an array("phrase" => ...)
 * @param boolean $sidebar has the page to be echoed with the actions sidebar?
 * @param string $header_icon the icon shown in the title (e.g. 'owl' for the 404 page owl)
 */
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
            $menus["polls"] = array("Umfragen", $env->polls_subtitle);
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
                $meta_dropdown["user_characteristics/edit"] = array("Steckbriefverwaltung", $env->uc_management_subtitle);
                $meta_dropdown["polls/edit"] = array("Umfragenverwaltung", $env->polls_management_subtitle);
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
            <script>window.jQuery || document.write('<script src="<?php echo tpl_url("js/lib/jquery-1.7.2.js") ?>"><\/script>')</script>
            <link rel = "shortcut icon" href = "<?php echo tpl_url($env->favicon) ?>"/>
            <!--Le HTML5 shim, for IE6-8 support of HTML5 elements-->
            <!--[if lt IE 9]>
            <script src = "http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
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
//            tpl_html5_please()
            ?>
            <!--[if lt IE 10]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a>.</p><![endif]-->
            <header class="jumbotron subhead" id="overview">
                <h1><? if ($header_icon != "") tpl_icon($header_icon, "", "", "header-icon") ?><?php echo $title ?></h1>
                <p class="lead">
                    <?php echo $subtitle ?></p>
                <?php
                global $has_sidebar;
                if (($subnav && count($subnav) == 4) || $sidebar) {
                    $has_sidebar = true;
                    tpl_subnav($subnav["phrase"]);
                } /* else if ($class == "user"){ //HACK
                  $has_sidebar = true;
                  tpl_no_subnav();
                  } */ else {
                    $has_sidebar = $subnav == true;
                    tpl_no_subnav();
                }
            }

            /**
             * Outputs the footer of the page
             * 
             * @global Environment $env
             * @global string $js JavaScript code to be appended to JS code in the footer
             * @global boolean $has_sidebar has the page to be echoed with the actions sidebar?
             * @global boolean $editor_needed needs the page to include the visual editor sources
             */
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
    <script>
    $(".tablesorter").ready(function(){
        $(".tablesorter").tablesorter();
    });
                $("body").ready(function(){
    <? if (Auth::canViewLogs()): ?>
                                                   add_log_object(<?= json_encode(logArray()) ?>);
    <? endif ?>
                $(".tablesorter").tablesorter();
    <?php echo $js ?>
                });
                max_file_size = <?= $env->max_upload_pic_size * 1048576 ?>;
    </script>
    <?= str_replace("&apos;", "'", str_replace("&quot;", '"', $env->footer_appendix)); ?>
    <?php if ($env->has_piwik) PiwikHelper::echoJSTrackerCode(true, $document_title) ?>
    <? if (defined("UNMINIFIED_SOURCE") && UNMINIFIED_SOURCE !== false): ?>
        <script src="<?php echo tpl_url("js/libs/handlebars-1.0.0.beta.6.js") ?>"></script>
        <script src="<?php echo tpl_url("js/libs/bootstrap.js") ?>"></script>
        <? if ($editor_needed): ?>
            <script src="<?php echo tpl_url("js/libs/jquery.wysiwyg.js") ?>"></script>
        <? endif ?>
        <script src="<?php echo tpl_url("js/plugins.js") ?>"></script>
        <script src="<?php echo tpl_url("js/application.js") ?>"></script>
        <!--<script src="<?php echo tpl_url("js/libs/modernizr-2.5.3.js") ?>"></script>-->
        <script src="<?php echo tpl_url("js/script.js") ?>"></script>
    <? else: ?>
        <? if ($editor_needed): ?>
            <script src="<?php echo tpl_url("js/min/jquery.wysiwyg.min.js") ?>"></script>
        <? endif ?>
        <script src="<?php echo tpl_url("js/min/scripts.min.js") ?>"></script>
    <? endif ?>
    </body>
    </html>
    <?php
}

/**
 * Outputs the end of the header and the begin of the content container and according to the $has_sidebar global the actions sidebar
 * 
 * @global boolean $has_sidebar has the page to be echoed with the actions sidebar?
 * @global Environment $env
 */
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

            /**
             * Outputs the end of the header and the begin of the content container with an search sidebar and according to the $has_sidebar global the actions sidebar
             * 
             * @global boolean $has_sidebar has the page to be echoed with the actions sidebar?
             * @global Environment $env
             * @param string $phrase the search phrase
             */
            function tpl_subnav($phrase) {
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

                    /**
                     * Outputs the item container header, if one of the paramters is "" this paramter will be ignored and the corresponding html will not be echoed
                     * 
                     * @param string $title title of the item, if empty string, no header is echoed
                     * @param string $icon title icon
                     * @param string $classapp css class append to the conainer css class attribute
                     * @param string $id id of item container
                     * @param string $link link of the title
                     * @param string $link_title title of the link of the title
                     */
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

                            /**
                             * Outputs the item container header, if one of the paramters is "" this paramter will be ignored and the corresponding html will not be echoed
                             * 
                             * @param array $form_attrs attributes added to the form tag, array(name => value), i.e 'method' => 'POST'
                             * @param string $title title of the item, if empty string, no header is echoed
                             * @param string $icon title icon
                             * @param string $classapp css class append to the conainer css class attribute
                             * @param string $id id of item container
                             */
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

                                        /**
                                         * Outputs the html code closing the item container
                                         */
                                        function tpl_item_after() {
                                            ?>
                                        </div>
                                </div>
                                <?php
                            }

                            /**
                             * Outputs the html code closing the form item container
                             * 
                             * @param array $args array of buttons (name => array("text" => ..., ["classapp" => ..., "type" => ..., "icon" => ..., "title" => ...])
                             */
                            function tpl_item_after_form($args) {
                                ?>
                            </div>
                            <hr/>
                            <div class="item-footer">
                                <? foreach ($args as $name => $arr): ?>
                                    <button class="btn <?= isset($arr["classapp"]) ? $arr["classapp"] : "" ?>" type="<?= isset($arr["type"]) ? $arr["type"] : "submit" ?>" name="<?= $name ?>" title="<?= isset($arr["title"]) ? $arr["title"] : "" ?>">
                                        <? if (isset($arr["icon"])) tpl_icon($arr["icon"]) ?><?= $arr["text"] ?>
                                    </button>
                                <? endforeach; ?>
                            </div>
                            </form>
                        </div>
                        <?php
                    }

                    /**
                     * Outputs the html code closing the item container with buttons in the footer
                     * 
                     * @param array $args array of buttons (name => array("text" => ..., ["classapp" => ..., "type" => ..., "icon" => ..., "title" => ...])
                     */
                    function tpl_item_after_buttons($args) {
                        ?>
                    </div>
                    <hr/>
                    <div class="item-footer">
                        <? foreach ($args as $text => $arr): ?>
                            <button class="btn <?= isset($arr["classapp"]) ? $arr["classapp"] : "" ?>" title="<?= isset($arr["title"]) ? $arr["title"] : "" ?>" onclick="<?= isset($arr["onclick"]) ? $arr["onclick"] : "" ?>">
                                <? if (isset($arr["icon"])) tpl_icon($arr["icon"]) ?><?= $text ?>
                            </button>
                        <? endforeach; ?>
                    </div>
                    </form>
                </div>
                <?php
            }

            /**
             * Outputs the html code closing the item container with one send button in the footer
             * 
             * @param string $title text on the button
             * @param string $name name attribute of the button
             * @param string $onclick JS code in the onclick attribute of the button, if empty string, a closing form tag is added and the the type attribute of the button will be "submit"
             * @param string $footerhtmlapp html echoed after the button in the item footer
             * @param string $classapp css class appended to class attribute of the footer container tag
             */
            function tpl_item_after_send($title = "Senden", $name = "send", $onclick = "", $footerhtmlapp = "", $classapp = "") {
                ?>
            </div>
            <hr class="<?= $classapp ?>"/>
            <div class="item-footer <?= $classapp ?>">
                <button class="btn" <?php echo $onclick == "" ? 'type="submit"' : "" ?> name="<?php echo $name ?>" onclick="<?php echo $onclick ?>"><?php echo $title ?></button>
                <?php echo $footerhtmlapp ?>
            </div>
            <?php echo $onclick == "" ? "</form>" : "" ?>
        </div>
        <?php
    }

    /**
     * Outputs the html code closing the item container with two buttons in the footer, if both oncklick parameters are empty strings, a closing form tag will be added
     * and the the type attribute of the button with en empty onclick attribute will be "submit"
     * 
     * @param string $title1 text on the first button
     * @param string $title2 text on the second button
     * @param string $onclick1 JS code in the onclick attribute of the first button
     * @param string $onclick2 JS code in the onclick attribute of the second button
     */
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

/**
 * Add the JavaScript Code to the global js variable and append an semicolon if needed
 * 
 * @global string $js JS code to append to the JS code in the footer 
 * @param string $code JS code
 */
function tpl_add_js($code) {
    global $js;
    $js .= ($js != "" ? "\n" : "") . $code . (substr($code, strlen($code) - 1) != ';' ? ';' : '');
}

/**
 * Outputs the impress page 
 * 
 * @global Environment $env
 */
function tpl_impress() {
    global $env;
    tpl_before("impress");
    tpl_item_before();
    echo formatText($env->impress_text);
    tpl_item_after();
    tpl_after();
}

/**
 * Outputs the privacy policy page 
 * 
 * @global Environment $env
 */
function tpl_privacy_policy() {
    global $env;
    tpl_before("privacy");
    tpl_item_before();
    echo formatText($env->privacy_policy);
    tpl_item_after();
    tpl_after();
}

/**
 * Outputs the terms of use page
 * 
 * @global Environment $env
 */
function tpl_terms_of_use() {
    global $env;
    tpl_before("terms_of_use");
    tpl_item_before();
    echo formatText($env->terms_of_use);
    tpl_item_after();
    tpl_after();
}

/*
 * TODO doesn't work
 */

function tpl_html5_please() {
    ?>
    <div id="h5p-message"></div>
    <script async>
    Modernizr.html5please = function(opts){ var passes = true; var features = opts.features.split('+'); var feat; for (var i = -1, len = features.length; ++i < len; ){ feat = features[i]; if (Modernizr[feat] === undefined) window.console && console.warn('Modernizr.' + feat + ' test not found'); if (Modernizr[feat] === false) passes = false; } if (passes){ opts.yep && opts.yep(); return passes; } Modernizr.html5please.cb = opts.nope; var script = document.createElement('script'); var ref = document.getElementsByTagName('script')[0]; var url = 'http://api.html5please.com/' + features.join('+') + '.json?callback=Modernizr.html5please.cb' + (opts.options ? ('&' + opts.options) : '') + '&html'; script.src = url; ref.parentNode.insertBefore(script, ref); return false; }; Modernizr.html5please({ features: "svg-css+svg-img+css-transitions+fontface+form-validation+forms+datalist+filereader", options: "texticon", yep: function(){ /* put your own initApp() here */ }, nope: function(a){ document.getElementById("h5p-message").innerHTML=a.html; } })
    </script>
    <?
}

/**
 * Outputs the noscript container, witch shows a warning to users who haven't enabled JavaScript
 */
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