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

class PreferencesHandler extends ToroHandler {

    public $pref_vals = array();

    public function __construct() {
        parent::__construct();
        $this->pref_vals = array(
            "title" => array("default" => "XXX Abitur " . (intval(date("Y")) + 1), "label" => "Titel"),
            "title_sep" => array("default" => " | ", "label" => "Seitentitelteiler"),
            "subtitle" => array("default" => "Abizeitungsseite", "label" => "Untertitel/Slogan"),
            "favicon" => array("default" => "favicon.ico", "label" => "Favicon der Seite"),
            "is_under_construction" => array("default" => "false", "label" => "Ist die Seite gerade Watungsmaßnahmen unterzogen?"),
            "fourothree_subtitle" => array("default" => '"To protect those who are not able to protect themselves is a duty which every one owes to society." - Lord Macnaghten', "label" => "403-Seite-Untertitel"),
            "fourofour_subtitle" => array("default" => '"Alles auf Erden lässt sich finden, wenn man nur zu suchen sich nicht verdrießen lässt." - Philemon, Fragmente', "label" => "404-Seite-Untertitel"),
            "images_subtitle" => array("default" => "Schöne, witzige oder sonst wie interessante Bilder", "label" => "Bilderseite-Untertitel"),
            "quotes_subtitle" => array("default" => "Schöne, komische oder sonstwie erwähnenswerte Zitate von Lehrern", "label" => "Zitateseite-Untertitel"),
            "rumors_subtitle" => array("default" => "Gerüchte, Halbwahrheiten und pointierte Fakten", "label" => "Stimmt es...-Seite-Untertitel"),
            "userall_subtitle" => array("default" => '"We must not contradict, but instruct him that contradicts us; for a madman is not cured by another running mad also." - Antisthenes.', "label" => "Schülerlistemseite-Untertitel"),
            "userpolls_subtitle" => array("default" => 'Umfragen zu den wirklich wichtigen Themen des Schulalltags', "label" => "Umfragenseite-Untertitel"),
            "stats_subtitle" => array("default" => '"Musik ist die versteckte arithmetische Tätigkeit der Seele, die sich nicht dessen bewußt ist, daß sie rechnet." - Leibniz', "label" => "Statistikseite-Untertitel"),
            "uc_subtitle" => array("default" => '"Oft neutralisieren sich zwei große Eigenschaften und ergeben als Reinertrag eine mittelmäßige Leidenschaft." - Théodore Jouffroy, Das grüne Heft', "label" => "Steckbriefseite-Untertitel"),
            "usermanagement_subtitle" => array("default" => '"Alle Leute sind entweder charmant oder langweilig. Ich ergreife Partei für die Charmanten." - Oscar Wilde', "label" => "Benutzerverwaltungsseite-Untertitel"),
            "teacherlist_subtitle" => array("default" => '"Wo Menschlichkeit geboten ist, steh\' nicht zurück - selbst hinter deinem Lehrer." - Konfuzius', "label" => "Lehrerlisteseite-Untertitel"),
            "dashboard_subtitle" => array("default" => '"Herrschaft ist im Alltag primär: Verwaltung." - Max Weber', "label" => "Dashboard-Untertitel"),
            "uc_management_subtitle" => array("default" => '"Everything is as important as everything else." - John Lennon', "label" => "Steckbriefverwaltungsseite-Untertitel"),
            "up_management_subtitle" => array("default" => '"Life is what happens to you, while you\'re busy making other plans" - John Lennon', "label" => "Umfragenverwaltungsseite-Untertitel"),
            "preferences_subtitle" => array("default" => '"There are no problems, only solutions." - John Lennon', "label" => "Untertitel dieser Seite"),
            "userpreferences_subtitle" => array("default" => '', "label" => "Untertitel der Einstellungenseite jedes Benutzers"),
            "impress_subtitle" => array("default" => '', "label" => "Imressumsseite-Untertitel"),
            "terms_of_use_subtitle" => array("default" => '', "label" => "Nutzungsbedingungenseite-Untertitel"),
            "mainpage_text" => array(
                "default" => "Dies ist die Abitzeitungswebsite der (zuk&uuml;nftigen) Abiturienten.",
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
            "has_forum" => array("default" => "false", "label" => "Befindet sich ein SMF-Forum in einem Unterpfad des Seitenordners?", "type" => "checkbox"),
            "forum_path" => array("default" => "smf", "label" => "Pfad des SMF-Forum-Hauptordners relativ zum Seitenhauptordner"),
            "has_wiki" => array("default" => "false", "label" => "Existiert ein Mediawiki?", "type" => "checkbox"),
            "wiki_path" => array("default" => "mediawiki", "label" => "Pfad des Mediawiki-Hauptordners relativ zum Seitenhauptordner"),
            "pic_width" => array("default" => "2400", "label" => "Breite der Bilder in Pixel"),
            "pic_quality" => array("default" => "85", "label" => "Bildqualität in %"),
            "pic_format" => array("default" => "jpg", "label" => "Bildformat"),
            "thumbnail_width" => array("default" => "800", "label" => "Breite der Vorschaubilder"),
            "max_uploads_size" => array("default" => "1000", "label" => "Maximale Gesammtgröße der hochgeladenden Bilder in MiB"),
            "upload_path" => array("default" => "uploads", "label" => "Order in welchem die hochgeladenen Bilder gespeichert werden"),
            "items_per_page" => array("default" => "30", "label" => "Angezeigte Einträge pro Seite"),
            "images_per_page" => array("default" => "5", "label" => "Angezeigte Bilder pro Seite"),
            "has_piwik" => array("default" => "false", "label" => "Wird Piwik als Webanalysis Werkzeug verwendet?", "type" => "checkbox"),
            "piwik_tracking_code" => array("default" => "", "label" => "Piwik Tracking Code"),
            "footer_appendix" => array("default" => "", "label" => "Code, der nach dem Footer eingefügt wird", "type" => "textarea"),
            "show_userpolls" => array("default" => "false", "label" => "Wird die Umfragenseite angezeigt?", "type" => "checkbox"),
            "show_usercharacteristics" => array("default" => "false", "label" => "Wird die Steckbriefseite angezeigt?", "type" => "checkbox"),
            "news_enabled" => array("default" => "false", "label" => "Ist die einfache Nachrichtenseite aktiviert?", "type" => "checkbox"),
            "showed_actions" => array("default" => "12", "label" => "Angezeigte Aktionen in der Seitenleiste"),
            "user_polls_result_length" => array("default" => "3", "label" => "Anzahl der Personen die pro Umfrage in der Ergebnisliste angezeigt werden"),
            "open" => array("default" => "false", "label" => "Ist die Seite offen nach außen?", "type" => "checkbox"),
            "stats_open" => array("default" => "false", "label" => "Ist die Statitikseite auch für normale Benutzer sichtbar?", "type" => "checkbox"),
            "images_editable" => array("default" => "true", "label" => "Können Bilder hinzugefügt werden?", "type" => "checkbox"),
            "quotes_editable" => array("default" => "true", "label" => "Können Zitate hinzugefügt werden?", "type" => "checkbox"),
            "rumors_editable" => array("default" => "true", "label" => "Können Stimmt es... Beiträge geschrieben werden?", "type" => "checkbox"),
            "user_comments_editable" => array("default" => "true", "label" => "Können Benutzerkommentare geschrieben werden?", "type" => "checkbox"),
            "user_characteristics_editable" => array("default" => "false", "label" => "Kann der eigene Steckbrief bearbeitet werden?", "type" => "checkbox"),
            "user_polls_open" => array("default" => "false", "label" => "Ist die Umfragenseite sichtbar?", "type" => "checkbox"),
            "user_polls_editable" => array("default" => "false", "label" => "Ist die Umfragenbearbeitung möglich (wenn nicht werden die Ergebnisse angezeigt)?", "type" => "checkbox"),
            "user_page_open" => array("default" => "false", "label" => "Werden die Benutzerseiten im Abizeitungsstil angezeigt?")
        );
    }

    public function getDefault($var) {
        $val = $this->pref_vals[$var]["default"];
        if ($val == "false" || $val == "true") {
            $val = $val == "true";
        }
        return $val;
    }

    public function hasDefault($var) {
        return isset($this->pref_vals[$var]);
    }

    public function get() {
        global $env;
        if (Auth::isSuperAdmin() || $env == null) {
            $this->loadDefaultVals();
            tpl_preferences($this->pref_vals);
        } else {
            tpl_404();
        }
    }

    public function post() {
        if (Auth::isSuperAdmin()) {
            foreach ($this->pref_vals as $key => $value) {
                if (isset($value["type"]) && $value["type"] == "checkbox") {
                    $this->pref_vals[$key]["default"] = isset($_POST[$key]) ? "true" : "false";
                } else {
                    $this->pref_vals[$key]["default"] = $_POST[$key];
                }
            }
            $this->fillDBWithDefaultValues();
            global $env;
            $env = new Environment();
            $this->get();
        } else {
            tpl_404();
        }
    }

    public function fillDBWithDefaultValues() {
        $db = Database::getConnection();
        foreach ($this->pref_vals as $key => $value) {
            $res = $db->query("SELECT * FROM " . DB_PREFIX . "preferences WHERE `key`='" . $key . "'");
            if ($res->num_rows > 0) {
                $db->query("UPDATE " . DB_PREFIX . "preferences SET value='" . $db->real_escape_string($value["default"]) . "' WHERE `key`='" . $key . "'");
            } else {
                $db->query("INSERT INTO " . DB_PREFIX . "preferences(`key`, value) VALUES('$key', '" . $db->real_escape_string($value["default"]) . "')");
            }
        }
    }

    private function loadDefaultVals() {
        $db = Database::getConnection();
        $res = $db->query("SELECT * FROM " . DB_PREFIX . "preferences");
        while ($arr = $res->fetch_array()) {
            if ($arr["value"] != "") {
                $this->pref_vals[$arr["key"]]["default"] = $arr["value"];
            }
        }
    }

}