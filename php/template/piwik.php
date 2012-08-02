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

function tpl_piwik_js_tracker_code($site_id, $url, $lines, $with_init = true) {
    ?>
    <!-- Piwik -->
    <? if ($with_init): ?>
        <script type="text/javascript">
            var pkBaseURL = "<?php echo $url ?>";
            document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
    <? endif ?>
    <script type="text/javascript">
        try {
            piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", <?php echo $site_id ?>);
            piwikTracker.setCustomVariable(1, "User mode", "<?php echo tpl_usermode_to_text(Auth::getUserMode()) ?>", "visit");
    <? if ($with_init) echo "<script>" ?>
    <? if (Auth::getUser() != null): ?>
                piwikTracker.setCustomVariable(2, "Math course", "<?= Auth::getUser()->getMathCourse() ?>", "visit");
    <? endif; ?>
    <?php
    foreach ($lines as $line)
        echo $line . "\n"
        ?>
                piwikTracker.enableLinkTracking();
                piwikTracker.trackPageView();
            } catch( err ) {}
    </script><noscript><p><img src="<?php echo $url ?>piwik.php?idsite=<?php echo $site_id ?>" style="border:0" alt="" /></p></noscript>
    <!-- End Piwik Tracking Code -->
    <?php
}
