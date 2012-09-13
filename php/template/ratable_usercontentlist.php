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

function tpl_image_list($rucis, $page, $pages, $phrase = "", $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_before("images", null, null, array("url_part" => "images", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
        echo '<div class="imagelist">';
    }
    if ($page == 1 && $as_page && $env->images_editable) {
        tpl_image_upload_item();
    }
    foreach ($rucis as $ruci)
        tpl_image_item($ruci);
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('images') ?>";
    <?php if ($as_page) echo 'var page = ' . $page . ';' ?>
        var max_page = pagecount = <?php echo $pages ?>;
    <?php echo $phrase == "" ? "" : 'var phrase = "' . $phrase . '";' ?>
        var chocolat_options = {
            leftImg: '<?php echo tpl_url('img/chocolat/left.gif') ?>',
            rightImg: '<?php echo tpl_url('img/chocolat/right.gif') ?>',
            loadingImg: '<?php echo tpl_url('img/chocolat/loading.gif') ?>',
            closeImg: '<?php echo tpl_url('img/chocolat/close.gif') ?>'
        };
    </script>
    <?php
    if ($as_page) {
        ?>
        </div>
        <?php
        tpl_after();
    } else {
        PiwikHelper::echoJSTrackerCode(false);
    }
}

function tpl_image_upload_item($with_descr = true) {//"enctype" => "multipart/form-data"
    //tpl_item_before_form(array("id" => "file_upload", "enctype" => "multipart/form-data"), "Bild hochladen", "camera", "item-send");
    tpl_item_before("Bild hochladen", "upload_image", "item-send");
    ?>     
    <div id="drop_area">
        <p><span>Bild hier ablegen.</span><br/>
            Das Bild darf maximal 3MB groß sein und sollte die Dateiendung .png, .jpg, .jpeg, .bmp oder .gif haben.</p>
    </div>	
    <input type="hidden" name="MAX_FILE_SIZE" value="3000000"/>
    <!--<input name="uploaded_file" id="file_input" type="file"/>-->
    <?php
    if ($with_descr) {
        ?>
        <hr/>
        <textarea name="description" class="descr" placeholder="Kurze, aussagekräftige Bildbeschreibung" require="on"></textarea>
        <?php
    }
    ?>		
    <?php
    tpl_item_after_send("Hochladen", "send", "uploadImage()", "<div class='progress'>
    <div class='bar' style=\"width: 0%;\"></div>
</div>");
}

function tpl_image_item(RatableUserContentItem $ruci) {
    global $env;
    tpl_item_before("", "", "content-item", $ruci->id);
    $imgfile = $ruci->id . '.' . $ruci->format;
    ?>
    <a class="item-content" href="<?php echo tpl_url($env->upload_path . '/' . $imgfile) ?>" title="<?= str_replace('\r\n', " ", $ruci->description) ?>">
        <img src="<?php echo tpl_url($env->upload_path . '/thumbs/' . $imgfile) ?>"/>
    </a><br/>
    <?
    echo str_replace('&lt;br/>', "", formatText($ruci->description));
    tpl_item_after_ruc($ruci);
}

function tpl_quote_list($rucis, $page, $pages, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_before("quotes", null, null, array("url_part" => "quotes", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
    }
    if ($page == 1 && $as_page && $env->quotes_editable) {
        tpl_write_quote_item();
    }
    foreach ($rucis as $ruci)
        tpl_quote_item($ruci);
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('quotes') ?>";
    <?php if ($as_page) echo 'var page = ' . $page . ';' ?>
    <? if ($pages == -1): ?>
            var max_page = <?php echo $pages ?>;
            var phrase = "<?php echo $phrase ?>";
    <? endif; ?>
    </script>
    <?php
    if ($as_page) {
        tpl_write_quote_response_item_hbs();
        tpl_after();
    } else {
        PiwikHelper::echoJSTrackerCode(false);
    }
}

function tpl_write_quote_item() {
    tpl_item_before("Zitat hinzufügen", "pencil", "item-send item-quote-send");
    ?>
    <input type="text" placeholder="Zitierter Lehrer" name="person" class="teacher_typeahead" list="teacher_datalist" required="on" pattern="([A-ZÄÖÜ.]([a-zßäöü.](-[a-zßäöüA-ZÄÖÜ.])?)+ ?){1,3}"/>
    <? tpl_datalist("teacher_datalist", TeacherList::getTeacherNameList()) ?>
    <textarea name="text" placeholder="Zitat" require="on"></textarea>
    <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_send_anonymous("Hinzufügen", "Anonym hinzufügen", "sendQuote(false, -1, '')", "sendQuote(true, -1, '')");
}

function tpl_write_quote_response_item_hbs($title = "item-response-template") {
    ?><script id="<?= $title ?>" type="text/x-handlebars-template"><?
    tpl_item_before("", "", "item-send item-quote-send");
    ?>
        <input type="hidden" name="person" class="teacher_typeahead" value="{{teacher}}"/>
        <textarea name="text" placeholder="Zitat" require="on"></textarea>
        <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_buttons(array("{{button_answer_title}}" => array("onclick" => "sendQuote(false, {{response_to}}, '{{teacher}}')"), "{{button_answer_ano_title}}" => array("onclick" => "sendQuote(true, {{response_to}}, '{{teacher}}')"), "Schließen" => array("onclick" => "responseToItem({{response_to}})")));
    ?></script><?
}

function tpl_quote_item(RatableUserContentItem $ruci) {
    tpl_item_before($ruci->person, "speech_bubbles", "content-item", $ruci->id, "javascript:search('$ruci->person')", 'Nach Zitaten von/mit "' . $ruci->person . '" suchen');
    echo formatText($ruci->text);
    tpl_item_after_ruc($ruci);
}

function tpl_rumor_list($rucis, $page, $pages, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_before("rumors", null, null, array("url_part" => "rumors", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
    }
    if ($page == 1 && $as_page && $env->rumors_editable) {
        tpl_write_rumor_item();
    }
    foreach ($rucis as $ruci)
        tpl_rumor_item($ruci);
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('rumors') ?>";
    <?php if ($as_page) echo 'var page = ' . $page . ';' ?>
    <? if ($pages == -1): ?>
            var max_page = <?php echo $pages ?>;
            var phrase = "<?php echo $phrase ?>";
    <? endif; ?>
    </script>
    <?
    if ($as_page) {
        tpl_write_rumor_response_item_hbs();
        tpl_after();
    } else {
        PiwikHelper::echoJSTrackerCode(false);
    }
}

function tpl_write_rumor_item() {
    tpl_item_before("Beitrag schreiben", "pencil", "item-send item-rumor-send");
    ?>
    <textarea name="text" placeholder="…, dass " require="on">…, dass </textarea>
    <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_send_anonymous("Absenden", "Anonym absenden", "sendRumor(false, -1)", "sendRumor(true, -1)");
}

function tpl_write_rumor_response_item_hbs($title = "item-response-template") {
    ?><script id="<?= $title ?>" type="text/x-handlebars-template"><?
    tpl_item_before("", "", "item-send item-rumor-send");
    ?>
        <textarea name="text" placeholder="..., dass " require="on">..., dass </textarea>
        <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_buttons(array("{{button_answer_title}}" => array("onclick" => "sendRumor(false, {{response_to}})"), "{{button_answer_ano_title}}" => array("onclick" => "sendRumor(true, {{response_to}})"), "Schließen" => array("onclick" => "responseToItem({{response_to}})")));
    ?></script><?
}

function tpl_rumor_item(RatableUserContentItem $ruci) {
    tpl_item_before("", "", "content-item", $ruci->id);
    echo formatText($ruci->text);
    tpl_item_after_ruc($ruci);
}

function tpl_item_after_ruc(RatableUserContentItem $ruci) {
    ?>
    </div>
    <hr/>
    <div class="item-footer <?php echo Auth::isModerator() ? "deletable" : '' ?>">
        <ul>
            <li class="time_span_li"><?php tpl_time_span($ruci->time) ?></li>
            <li class="rating_li"><?php tpl_rating($ruci->id, $ruci->own_rating, $ruci->userid, $ruci->rating, $ruci->rating_count, $ruci->data) ?></li>
            <li class="user_span_li"><?php tpl_user_span($ruci->isAnonymous() ? $ruci->userid : -1, true) ?></li>
            <? if ($ruci->canHaveResponses()): ?>
                <li class="response_to_span_li"><? tpl_item_response_to_span($ruci) ?></li>
            <? endif ?>
            <? if ($ruci->isDeletable()): ?>
                <li class="delete_span_li"><? tpl_item_delete_span($ruci) ?></li>
            <? endif ?>
        </ul>
    </div>
    </div>
    <?
    if ($ruci->canHaveResponses())
        tpl_response_to_div($ruci);
}

function tpl_response_to_div(RatableUserContentItem $ruci) {
    ?>
    <div id="responses_to_<?= $ruci->id ?>" class="responses" to="<?= $ruci->id ?>">
        <?
        if (!empty($ruci->responses))
            tpl_quote_list($ruci->responses, -1, -1, -1, "", false);
        ?>
        <div class="add_response_container" to="<?= $ruci->id ?>"></div>
    </div>
    <?
}

function tpl_item_response_to_span(RatableUserContentItem $ruci) {
    ?>
    <span class="response_to_item"><button class="btn" onclick="responseToItem('<?= $ruci->id ?>', '<?= $ruci->hasPersonVal() ? $ruci->person : '' ?>')"><?php tpl_icon("speech_bubbles", "Antworten") ?> Antworten</span>
    <?
}

function tpl_item_delete_span(RatableUserContentItem $ruci) {
    ?>
    <span class="del_item"><?php tpl_icon("delete", "Löschen", "deleteItem('" . $ruci->id . "')") ?></span>
    <?php
}

function tpl_rating($id, $own, $senduser, $average_rating, $rating_count, $data = array()) {
    $can_rate = (is_numeric($senduser) ? $senduser : $senduser->getID()) != Auth::getUserID();
    ?>
    <span id="<?php echo $id ?>rating" class="rating">
        <?php if ($can_rate) { ?>
            <span class="stars">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="star ' . ($i <= $own ? "selected" : '') . '" onclick="rating(' . $id . ', ' . $i . ')">&#9733;</span>';
                }
                ?>
            </span>
            <?php
        }
        $show_av = !$can_rate || is_numeric($own);
        tpl_average($show_av ? $average_rating : -1, $show_av ? $rating_count : -1, $data);
        ?>
    </span>
    <?php
}

function tpl_average($rating, $count = -1, $data = array()) {
    ?>
    <span class="average">
        <? if ($rating != -1): ?>
            [<span class="num" title="<? printf("Ø Bewertung: %1.3f; Bewertungen: %2.d", $rating, $count) ?>"><? printf("Ø%1.1f, x%2.d", $rating, $count) ?></span>]
        <? endif; ?>
    </span>
    <?php
}