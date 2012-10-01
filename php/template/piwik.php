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
 * Outputs the Piwik Javscript Tracker code
 * 
 * @param string $site_id piwik id of the website
 * @param string $url url of the website
 * @param array $lines array of addional code lines added to the tracker code
 * @param boolean $with_init also output the tracker init code?
 * @param string $document_title title of the current document
 */
function tpl_piwik_js_tracker_code($site_id, $url, $lines, $with_init = true, $document_title = "") {
    ?>
    <!-- Piwik -->
    <? if ($with_init): ?>
        <script type="text/javascript">
            var pkBaseURL = "<?php echo $url ?>";
            document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
            try {
                piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", <?php echo $site_id ?>);
                piwikTracker.setCustomVariable(1, "User mode", "<?php echo tpl_usermode_to_text(Auth::getUserMode()) ?>", "visit");
    <? endif ?>
    <? if (!$with_init) echo "<script>\n   try {\n" ?>
    <? if ($with_init && Auth::getUser() != null): ?>
            piwikTracker.setCustomVariable(2, "Math course", "<? $user = Auth::getUser(); echo $user->getMathCourse() ?>", "visit");
    <? endif; ?>
    <?php
    foreach ($lines as $line)
        echo $line . "\n"
        ?>
    <? if ($with_init): ?>
            piwikTracker.enableLinkTracking();
            piwikTracker.setDocumentTitle("<?= $document_title ?>");
            piwikTracker.trackPageView();
    <? endif ?>
    } catch( err ) {}
    </script><noscript><p><img src="<?php echo $url ?>piwik.php?idsite=<?php echo $site_id ?>" style="border:0" alt="" /></p></noscript>
    <!-- End Piwik Tracking Code -->
    <?php
}