<?

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
 * Handles the preferences page
 */
class PreferencesHandler extends ToroHandler {

    /**
     * Contains the preferences, organized in arrays to simply their presentation on the page
     * @var array
     */
    private $pref_vals_visu = array();

    /**
     * Contains the preferences in a flat array
     * @var array
     */
    private $pref_vals = array();

    /**
     * Fills the pref-vals_visu and the pref_vals array with the current preferences
     */
    public function __construct() {
        parent::__construct();
        $this->pref_vals_visu = array(
            "Seitentitel und Co." => array("id" => "main_pref", "open" => true, "rows" => array(
                    "title" => array("default" => "XXX Abitur " . (intval(date("Y")) + 1), "label" => "Titel"),
                    "title_sep" => array("default" => " | ", "label" => "Seitentitelteiler"),
                    "subtitle" => array("default" => "Abizeitungsseite", "label" => "Untertitel/Slogan"),
                    "favicon" => array("default" => "favicon.ico", "label" => "Favicon der Seite"),
                    "is_under_construction" => array("default" => "false", "label" => "Ist die Seite gerade Wartungsmaßnahmen unterzogen?", "type" => "checkbox"),
                    "registration_enabled" => array("default" => "true", "label" => "Können sich neue Benutzer registrieren?", "type" => "checkbox")
                )),
            "Seitenuntertitel" => array("mode" => "table", "rows" => array(
                    "fourothree_subtitle" => array("default" => '"To protect those who are not able to protect themselves is a duty which every one owes to society." - Lord Macnaghten', "label" => "403-Seite"),
                    "fourofour_subtitle" => array("default" => '"Alles auf Erden lässt sich finden, wenn man nur zu suchen sich nicht verdrießen lässt." - Philemon, Fragmente', "label" => "404-Seite"),
                    "fiveoo_subtitle" => array("default" => '', "label" => "500-Seite"),
                    "images_subtitle" => array("default" => "Schöne, witzige oder sonst wie interessante Bilder", "label" => "Bilderseite"),
                    "quotes_subtitle" => array("default" => "Schöne, komische oder sonstwie erwähnenswerte Zitate von Lehrern und Schülern", "label" => "Zitateseite"),
                    "rumors_subtitle" => array("default" => "Gerüchte, Halbwahrheiten und pointierte Fakten", "label" => "Stimmt es...-Seite"),
                    "userall_subtitle" => array("default" => '"We must not contradict, but instruct him that contradicts us; for a madman is not cured by another running mad also." - Antisthenes', "label" => "Schülerlistemseite"),
                    "polls_subtitle" => array("default" => 'Umfragen zu den wirklich wichtigen Themen des Schulalltags', "label" => "Umfragenseite"),
                    "stats_subtitle" => array("default" => '"Musik ist die versteckte arithmetische Tätigkeit der Seele, die sich nicht dessen bewußt ist, daß sie rechnet." - Leibniz', "label" => "Statistikseite-Untertitel"),
                    "uc_subtitle" => array("default" => '"Oft neutralisieren sich zwei große Eigenschaften und ergeben als Reinertrag eine mittelmäßige Leidenschaft." - Théodore Jouffroy, Das grüne Heft', "label" => "Steckbriefseite"),
                    "all_user_page_results_page_subtitle" => array("default" => '"Everything is as important as everything else." - John Lennon', "label" => "Gesammelte Benutzseite"),
                    "usermanagement_subtitle" => array("default" => '"Alle Leute sind entweder charmant oder langweilig. Ich ergreife Partei für die Charmanten." - Oscar Wilde', "label" => "Benutzerverwaltungsseite"),
                    "teacherlist_subtitle" => array("default" => '"Wo Menschlichkeit geboten ist, steh&apos; nicht zurück - selbst hinter deinem Lehrer." - Konfuzius', "label" => "Lehrerlisteseite"),
                    "dashboard_subtitle" => array("default" => '"Herrschaft ist im Alltag primär: Verwaltung." - Max Weber', "label" => "Dashboard"),
                    "uc_management_subtitle" => array("default" => '"Everything is as important as everything else." - John Lennon', "label" => "Steckbriefverwaltungsseite"),
                    "polls_management_subtitle" => array("default" => '"Life is what happens to you, while you&apos;re busy making other plans" - John Lennon', "label" => "Umfragenverwaltungsseite"),
                    "preferences_subtitle" => array("default" => '"There are no problems, only solutions." - John Lennon', "label" => "Diese Seite"),
                    "userpreferences_subtitle" => array("default" => '', "label" => "Einstellungenseite jedes Benutzers"),
                    "impress_subtitle" => array("default" => '', "label" => "Impressumsseite"),
                    "privacy_subtitle" => array("default" => '', "label" => "Datenschutzseite"),
                    "terms_of_use_subtitle" => array("default" => '', "label" => "Nutzungsbedingungenseite"),
                    "news_subtitle" => array("default" => '', "label" => "Nachrichtenseite"),
                    "news_write_subtitle" => array("default" => '', "label" => "Nachrichtschreibenseite"),
                    "actions_subtitle" => array("default" => '', "label" => "Aktionenseite")
                )),
            "Seiteninhalte" => array("mode" => "table-list", "rows" => array(
                    "mainpage_text" => array(
                        "default" => "Dies ist die Abizeitungswebsite der (zuk&uuml;nftigen) Abiturienten.",
                        "label" => "Text auf der Hauptseite (Markdown formatiert)",
                        "type" => "textarea"
                    ),
                    "impress_text" => array(
                        "default" => "##Verantwortlich für diesen Webauftritt
[Vorname] [Nachname]
[Straße] .[Hausnummer]
[Ort]
[Kontaktmöglichkeit (z.B.E-Mail-Adresse)]
##Haftungsausschluss
Wir weisen hiermit ausdrücklich darauf hin, dass wir auf die Gestaltung und den Inhalt externer Links keinen Einfluss haben und für diese deshalb keine Haftung übernehmen. Für die verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich. Ebenso übernehmen wir keine Haftung für von Benutzern eingestellte Inhalte, diese sind für die Inhalte selbst verantwortlich. ",
                        "label" => "Text im Impressum",
                        "type" => "textarea"
                    ),
                    "terms_of_use" => array(
                        "default" => "Die gemeinschaftliche Verwendung dieser Seite ist nur möglich, wenn sich alle an bestimmte Regeln hallten: 
Es ist verboten in Beiträgen, die auf den verschiedenen Unterseiten verfasst werden, Personen
- zu beleidigen,
- zu verunglimpfen (z.B. mit unbegründeten Behauptungen),
- abzubilden ohne deren Zustimmung,
- zu bedrohen und
- zu mobben.
Außerdem ist es verboten Beiträge zu schreiben, die den Ruf einer Person schädigen, wie es auch nicht erlaubt ist, Bilder hochzuladen, die diesem Ziel dienlich sind.
Hierbei sollte aber beachtet werden, dass ein Verstoß erst vorliegt, wenn das Verbot extrem übertreten wird, d.h. Stimmt es...-Beiträge wie z.B. '..., dass X nie im Unterricht war', sofern die Aussage im wesentlichen der Wahrheit entspricht.
Solch ein Verstoß führt (abhänging von seiner Schwere) zu Ermahnungen und/oder auf der Sperrung des Benutzerkontos des Übertretenden. Für Durchführung und Aufhebung einer solcher Sperre sind die Moderatoren und Admins dieser Seite verantwortlich.
Ein weitere und auch durchaus wichtige Regel ist, dass diese Seite besonnen und vernünftig benutzt werden sollte von allen beteiligten. Das bedeutet, dass es verboten ist die Seite zu spammen, d.h. irrelevante Bilder hochzuladen und Beiträge der selben Art zu schreiben. Irrelevant ist ein Beitrag, wenn sein einziger Zweck im Vollmüllen der Seite besteht, gar keinen Mehrwert für die anderen Benutzer dieser Seite darstellt oder eine Copyright-Verletzung ist.
Bei einem Verstoß gegen diese Regel kommt es zu den schon vorher genannten Konsequenzen. Wenn sich solche Verstöße häufen behallten sich die Administratoren und Moderatoren dieser Seite außerdem vor, diese Seite zu sperren.
Allgemein behalten sich die genannten Personengruppen vor, Beiträge die gegen die genannten Regeln verstoßen ohne Vorwarnung zu löschen.

Das Copyright an den Beiträgen geht mit dem Hochladen bzw. Schreiben auf dieser Seite an die Gruppe der Moderatoren und Administratoren dieser Seite über, welche dieses dann wiederum an den zuständingen Schülerausschuss übertragen.",
                        "label" => "Nutzungsbedingungen",
                        "type" => "textarea"
                    ),
                    "privacy_policy" => array(
                        "default" => "<p>Diese Seite speichert die persönlichen Daten welche bei der Registrierung angegeben wurden. Diese Daten werden nicht an Dritte weitergegeben, sofern diese Dritten nicht einen Schülerausschuss repräsentieren. Es werden außerdem noch die Seitenaufrufe zu statistischen Zwecken mit der Software Piwik protokolliert, hierbei wird durch ihren Browser, die benutzte Browserversion, das verwendete Betriebssystem,
                            die Referrer URL (die zuvor besuchte Seite), der Hostname des zugreifenden Rechners (IP Adresse) und Uhrzeit der Serveranfrage an uns übermittelt. Die Seitenaufrufstatistiken können nicht mit ihren anderen Daten verknüft werden, dies ist nur bis auf die Ebene eines Mathekurses und des Benutzerranges möglich. Außerdem werden ihre Aktionen zu dem angegeben Zweck protokolliert. Desweiteren wird bei jedem Benutzer direkt noch der Zeitpunkt des letzten Zugriffs gespeichert.</p>
<p>Zur Anmeldung werden Cookies gespeichert, dies ist für die Funktionalität der Seite unbendingt notwendig. Cookies sind kleine Textdateien, die auf Ihrem Rechner abgelegt werden  und die Ihr Browser speichert. Cookies richten auf Ihrem Rechner keinen  Schaden an und enthalten keine Viren. Cookies können es auch ermöglichen, sie nach Verlassen der Website wiederzuerkennen um Sie ohne Passworteingabe gleich auf die Webseite weiterleiten.</p>
<p>Wenn Sie zu den Daten, die auf dieser Seite von ihnen gepseichert Auskunft erhalten möchten oder allgemein Fragen zum Datenschutz dieser Seite haben, kontaktieren sie bitte die im Impressum genannte Adresse.</p>",
                        "label" => "Datenschutz-Seitentext",
                        "type" => "textarea"
                    ))),
            "Forum" => array("id" => "forum_prefs", "rows" => array(
                    "has_forum" => array("default" => "false", "label" => "Befindet sich ein SMF-Forum in einem Unterpfad des Seitenordners?", "type" => "checkbox"),
                    "forum_path" => array("default" => "smf", "label" => "Pfad des SMF-Forum-Hauptordners relativ zum Seitenhauptordner"),
                    "forum_url" => array("default" => URL . "/smf", "label" => "Url des SMF-Forums")
                )),
            "Wiki" => array("rows" => array(
                    "has_wiki" => array("default" => "false", "label" => "Existiert ein Mediawiki?", "type" => "checkbox"),
                    "wiki_path" => array("default" => "mediawiki", "label" => "Pfad des Mediawiki-Hauptordners relativ zum Seitenhauptordner"),
                    "wiki_url" => array("default" => URL . "/mediawiki", "label" => "Url des Mediawikis")
                )),
            "Bilder" => array("rows" => array(
                    "max_upload_pic_size" => array("default" => "8", "label" => "Maximalgröße eines hochzuladenden Bildes in MiB"),
                    "pic_width" => array("default" => "2400", "label" => "Breite der Bilder in Pixel"),
                    "pic_quality" => array("default" => "85", "label" => "Bildqualität in %"),
                    "pic_format" => array("default" => "jpg", "label" => "Bildformat"),
                    "resize_original_image" => array("default" => "false", "type" => "checkbox", "label" => "Originalbild auf die Bilderbreite verkleinern?"),
                    "thumbnail_width" => array("default" => "800", "label" => "Breite der Vorschaubilder"),
                    "max_uploads_size" => array("default" => "1000", "label" => "Maximale Gesammtgröße der hochgeladenden Bilder in MiB"),
                    "upload_path" => array("default" => "uploads", "label" => "Order in welchem die hochgeladenen Bilder gespeichert werden")
                )),
            "Piwik" => array("id" => "piwik_prefs", "rows" => array(
                    "has_piwik" => array("default" => "false", "label" => "Wird Piwik als Webanalysis Werkzeug verwendet?
Wenn ja, sollte Piwik installiert sein und diese Website hinzugefügt worden sein.", "type" => "checkbox"),
                    "piwik_site_id" => array("default" => "", "label" => "Piwik-Seiten-ID"),
                    "piwik_url" => array("default" => URL . "/piwik/", "label" => "Piwik URL"),
                    "piwik_token_auth" => array("default" => "", "label" => "Piwik token_auth-Wert, findbar im API Menu der Piwik-Installation dort: '&token_auth=[token_auth-Wert]'")
                )),
            "Nachrichten" => array("id" => "news_prefs", "rows" => array(
                    "news_enabled" => array("default" => "true", "label" => "Ist die einfache Nachrichtenseite aktiviert?", "type" => "checkbox"),
                    "number_of_news_shown_at_the_home_page" => array("default" => "1", "label" => "Anzahl der Nachrichten, die auf der Hauptseite angezeigt werden", "type" => "number")
                )),
            "Weitere Einstellungen" => array("id" => "more_prefs", "rows" => array(
                    "wysiwyg_editor_enabled" => array("default" => "false", "type" => "checkbox", "label" => "Wird ein visueller Editor auf dieser und der 'Nachrichten schreiben'-Seite verwendet?"),
                    "items_per_page" => array("default" => "30", "label" => "Angezeigte Einträge pro Seite"),
                    "images_per_page" => array("default" => "5", "label" => "Angezeigte Bilder pro Seite"),
                    "footer_appendix" => array("default" => "", "label" => "Code, der nach dem Footer eingefügt wird", "type" => "codearea"),
                    "showed_actions" => array("default" => "12", "label" => "Angezeigte Aktionen in der Seitenleiste"),
                    "stats_open" => array("default" => "false", "label" => "Ist die Statitikseite auch für normale Benutzer sichtbar?", "type" => "checkbox"),
                    "images_editable" => array("default" => "true", "label" => "Können Bilder hinzugefügt werden?", "type" => "checkbox"),
                    "quotes_editable" => array("default" => "true", "label" => "Können Zitate hinzugefügt werden?", "type" => "checkbox"),
                    "rumors_editable" => array("default" => "true", "label" => "Können Stimmt es... Beiträge geschrieben werden?", "type" => "checkbox"),
                    "user_comments_editable" => array("default" => "true", "label" => "Können Benutzerkommentare geschrieben werden?", "type" => "checkbox"),
                    "user_characteristics_editable" => array("default" => "false", "label" => "Kann der eigene Steckbrief bearbeitet werden?", "type" => "checkbox"),
                    "user_polls_open" => array("default" => "false", "label" => "Ist die Umfragenseite sichtbar?", "type" => "checkbox"),
                    "system_mail_adress" => array("default" => "", "label" => "Mailadresse der Seite"),
                    "response_allowed" => array("default" => "true", "label" => "Können Kommentare zu Stimmt es...-Beiträgen und Zitaten geschrieben werden?", "type" => "checkbox"),
                    "show_logs" => array("default" => "false", "label" => "Werden die Loggingnachrichten den Admins angezeigt?", "type" => "checkbox"),
                    "auto_update_interval" => array("default" => "15000", "label" => "Pause zwischen zwei Aktualisierungsvorgängen von z.B. der Aktionenspalte in Millisekunden", "type" => "number"),
                    "search_update_interval" => array("default" => "300", "label" => "Puffer zwischen zwei Suchabfragen während der Benutzer tippt in Millisekunden", "type" => "number"),
//                    "time_zone_offset" => array("default" => "2", "label" => "Zeitzonenverschiebung (im Vergleich zur UTC) in Stunden", "type" => "number"),
                    "userpolls_result_length" => array("default" => "3", "label" => "Anzahl der Personen die pro Umfrage in der Ergebnisliste angezeigt werden"),
                    "results_viewable" => array("default" => "false", "label" => "Kann sich ein Benutzer (mindestens vom Rang eines Editors), die Ergebnisse anzeigen lassen", "type" => "checkbox"),
                    "windows_8_tile_image" => array("default" => "", "label" => "Windows 8 Kachelbild"),
                    "windows_8_tile_color" => array("default" => "#333333", "label" => "Windows 8 Kachelfarbe", "type" => "color")
                ),
//                "Ergebnisanzeige" => array("id" => "result_pages", "rows" => array(
//                       "quote_template" => array(
//                        "default" => "",
//                        "label" => "Zitatseiten-Template",
//                        "type" => "textarea"
//                    ),
//                    "usercharacteristics_page_teplate" => array(
//                        "default" => "",
//                        "label" => "Zitatseiten-Template",
//                        "type" => "textarea"
//                    )
            //))
        ));
        $this->loadDefaultVals();
        $this->updatePrefVisuArr();
        foreach ($this->pref_vals_visu as $key => $arr) {
            if (isset($arr["rows"]))
                $arr = $arr["rows"];
            foreach ($arr as $key2 => $arr2) {
                $this->pref_vals[$key2] = $arr2;
            }
        }
    }

    /**
     * Returns the default value of the preference
     * 
     * @param String $var preference name
     * @return mixed default value
     */
    public function getDefault($var) {
        $val = $this->pref_vals[$var]["default"];
        if ($val == "false" || $val == "true") {
            $val = $val == "true";
        }
        return $val;
    }

    /**
     * Has the preference a default value?
     * @param String $var preference name
     * @return boolean
     */
    public function hasDefault($var) {
        return isset($this->pref_vals[$var]);
    }

    public function get() {
        $this->loadDefaultVals();
        $this->updatePrefVisuArr();
        tpl_preferences($this->pref_vals_visu);
    }

    public function post() {
        if (Auth::canModifyPreferences()) {
            foreach ($this->pref_vals as $key => $value) {
                if (isset($value["type"]) && $value["type"] == "checkbox") {
                    $this->pref_vals[$key]["default"] = isset($_POST[$key]) ? "true" : "false";
                } else {
                    if (isset($_POST[$key])) {
                        $this->pref_vals[$key]["default"] = str_replace('\'', "'", str_replace('\"', "&quot;", $_POST[$key]));
                    }
                }
            }
            $this->updateDB();
            global $env;
            $env = new Environment();
            $this->get();
            if ($env->has_piwik) {
                PiwikHelper::setup();
            }
        } else {
            tpl_404();
        }
    }

    /**
     * Updates the pref_val_visu array with the current values of the pref_vals array
     */
    public function updatePrefVisuArr() {
        foreach ($this->pref_vals as $key => $value) {
            $this->setPrefVisuVal($key, $value);
        }
    }

    /**
     * Set the value of the preference in the pref_visu_vals array
     * 
     * @param string $name preference name
     * @param mixed $value new value
     */
    private function setPrefVisuVal($name, $value) {
        foreach ($this->pref_vals_visu as $key => $arr) {
            if (isset($arr["rows"])) {
                $arr = $arr["rows"];
                $rows = true;
            } else {
                $rows = false;
            }
            foreach ($arr as $key2 => $arr2) {
                if ($key2 == $name) {
                    if ($rows) {
                        $this->pref_vals_visu[$key]["rows"][$key2]["default"] = $value["default"];
                    } else {
                        $this->pref_vals_visu[$key][$key2]["default"] = $value["default"];
                    }
                }
            }
        }
    }

    /**
     * Updates the database with the new preference values
     */
    public function updateDB() {
        $db = Database::getConnection();
        foreach ($this->pref_vals as $key => $value) {
            $value["default"] = str_replace("'", "&apos;", str_replace('"', "&quot;", $value["default"]));
            $res = $db->query("SELECT * FROM " . DB_PREFIX . "preferences WHERE `key`='" . $key . "'");
            if ($res->num_rows > 0) {
                $db->query("UPDATE " . DB_PREFIX . "preferences SET value='" . $db->real_escape_string($value["default"]) . "' WHERE `key`='" . $key . "'");
            } else {
                $db->query("INSERT INTO " . DB_PREFIX . "preferences(`key`, value) VALUES('$key', '" . $db->real_escape_string($value["default"]) . "')");
            }
        }
    }

    /**
     * Loads the default values of the preferences from the database into the pref_vals array
     */
    private function loadDefaultVals() {
        $db = Database::getConnection();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "preferences");
        while ($arr = $res->fetch_array()) {
            if ($arr["value"] != "" && isset($this->pref_vals[$arr["key"]])) {
                if (isset($this->pref_vals[$arr["key"]]["type"]) && $this->pref_vals[$arr["key"]]["type"] == "checkbox") {
                    $this->pref_vals[$arr["key"]]["default"] = $arr["value"] == "true";
                } else {
                    $this->pref_vals[$arr["key"]]["default"] = $arr["value"];
                }
            }
        }
    }

}