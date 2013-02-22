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
 * Outputs the image page
 * 
 * @global Environment $env
 * @param array $rucis array of RatableUserContentItems
 * @param int $page current page number
 * @param int $pages number of image pages, that can be shown
 * @param string $phrase search phrase, written in to the searchbar
 * @param boolean $as_page output as page?
 */
function tpl_image_list($rucis, $page, $pages, $phrase = "", $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_update_with_js();
        tpl_before("images", null, null, array("url_part" => "images", "page" => $page, "pagecount" => $pages, "phrase" => $phrase, "auto_search_forbidden" => true));
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
    <? if ($pages != -1): ?>
            window.max_page = pagecount = <?php echo $pages ?>;
    <? endif ?>
    <?php echo $phrase == "" ? "" : 'var phrase = "' . $phrase . '";' ?>
        var chocolat_options = {};</script>
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

/**
 * Outputs the upload image form
 * 
 * @global Environment $env
 * @param boolean $with_descr allow the user to type in an description and a category for the uploaded page
 */
function tpl_image_upload_item($with_descr = true) {//"enctype" => "multipart/form-data"
    global $env;
    //tpl_item_before_form(array("id" => "file_upload", "enctype" => "multipart/form-data"), "Bild hochladen", "camera", "item-send");
    tpl_item_before("Bild hochladen", "camera", "item-send", "drop-image-area");
    ?>     
    <div id="drop_area" onclick="$('#file_input').click()">
        <p><span>Bild hier ablegen</span><br/>
            <span class="sub">oder zum Auswählen hier klicken</span></p><br/>
        Das Bild darf maximal <?= $env->max_upload_pic_size ?>MB groß sein und sollte die Dateiendung .png, .jpg, .jpeg, .bmp oder .gif haben.</p>
    </div>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $env->max_upload_pic_size * 1048576 ?>"/>
    <input type="file" id="file_input" multiple accept="image/*" style="display:none" onchange="handleFiles(this.files)">
    <?php
    if ($with_descr) {
        ?>
        <hr class="hide_when_unused"/>
        <textarea class="descr hide_when_unused" name="description" placeholder="Kurze, aussagekräftige Bildbeschreibung" require="on"></textarea>
        <input class="img_category hide_when_unused" name="category" list="category_list" placeholder='Kategorie in die dieses Bild einsortiert wird, z.B. "Studienfahrt Barcelona"'/>
        <?
        $list = new ImageList();
        tpl_datalist("category_list", $list->getCategories());
        ?>
        <?php
    }
    ?>		
    <?php
    tpl_item_after_send("Hochladen", "send", "uploadImage()", "<div class='progress'>
    <div class='bar' style=\"width: 0%;\"></div>
</div>", "hide_when_unused");
}

/**
 * Outputs an image item, with description, category and capture date
 * 
 * @global Environment $env
 * @param RatableUserContentItem $ruci item to be echoed
 */
function tpl_image_item(RatableUserContentItem $ruci) {
    global $env;
    tpl_item_before("", "", "content-item", $ruci->id);
    $imgfile = $ruci->id . '.' . $ruci->format;
    ?>
    <a class="item-content" href="<?php echo tpl_url($env->upload_path . '/' . $imgfile) ?>" title="<?= date("d.m.y H:i", $ruci->capture_time) ?>: <?= $ruci->category != "" ? $ruci->category : str_replace('\r\n', " ", $ruci->description) ?>">
        <img src="<?= tpl_url($env->upload_path . '/thumbs/' . $imgfile) ?>"/>
    </a><br/>
    <div class="img_descr">
        <div class="descr_text">
            <?= str_replace('&lt;br/>', "", formatText($ruci->description)) ?>
        </div>
        <? if ($ruci->capture_time > 10): ?>
            <div class="descr_ctime"> Aufnahmedatum: <?= date("d.m.y H:i", $ruci->capture_time) ?> </div>
        <? endif; ?>
        <? if ($ruci->category != ""): ?>
            <div class="descr_category"> Kategorie: <a href="javascript:search('<?= $ruci->category ?>')"><?= $ruci->category ?></a> </div>
        <? endif; ?>
    </div>
    <?
    tpl_item_after_ruc($ruci);
}

/**
 * Outputs the quote page
 * 
 * @global Environment $env
 * @param array $rucis array of RatableUserContentItems
 * @param int $page current page number
 * @param int $pages number of image pages, that can be shown
 * @param string $phrase search phrase, written in to the searchbar
 * @param boolean $as_page output as page?
 */
function tpl_quote_list($rucis, $page, $pages, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_update_with_js();
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
    <? if ($pages != -1): ?>
            window.max_page = pagecount = <?php echo $pages ?>;
    <? endif ?>
    <? if ($pages == -1): ?>
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

/**
 * Outputs the write quote form
 */
function tpl_write_quote_item() {
    tpl_item_before("Zitat hinzufügen", "pencil", "item-send item-quote-send");
    ?>
    <input type="text" placeholder="Zitierter Lehrer oder Schüler" name="person" class="teacher_typeahead" list="teacher_datalist" required="on" pattern="([A-ZÄÖÜ.]([a-zßäöü.](-[a-zßäöüA-ZÄÖÜ.])?)+ ?){1,3}"/>
    <? tpl_datalist("teacher_datalist", array_merge(Teacher::getNameList(), User::getNameList())) ?>
    <textarea name="text" placeholder="Zitat" require="on"></textarea>
    <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_send_anonymous("Hinzufügen", "Anonym hinzufügen", "sendQuote(false, -1, '')", "sendQuote(true, -1, '')");
}

/**
 * Outputs the quote response item handlebars template code
 * 
 * @param string $id id of the template
 */
function tpl_write_quote_response_item_hbs($id = "item-response-template") {
    ?><div id="<?= $id ?>" type="text/x-handlebars-template"><?
        tpl_item_before("", "", "item-send item-quote-send");
        ?>
        <input type="hidden" name="person" class="teacher_typeahead" value="{{teacher}}" />
        <textarea name="text" placeholder="Zitat" require="on"> </textarea>
        <input type="hidden" name="response_to" value="-1" />
        <?
        tpl_item_after_buttons(array("{{button_answer_title}}" => array("onclick" => "sendQuote(false, {{response_to}}, '{{teacher}}')"), "{{button_answer_ano_title}}" => array("onclick" => "sendQuote(true, {{response_to}}, '{{teacher}}')"), "Schließen" => array("onclick" => "responseToItem({{response_to}})")));
        ?></div><?
}

/**
 * Outputs a quote item
 * 
 * @param RatableUserContentItem $ruci quote item
 */
function tpl_quote_item(RatableUserContentItem $ruci) {
    if ($ruci->isResponse()) {
        tpl_item_before("", "", "content-item", $ruci->id, "", "");
    } else {
        tpl_item_before($ruci->person, "speech_bubbles", "content-item", $ruci->id, "javascript:search('$ruci->person')", 'Nach Zitaten von/mit "' . $ruci->person . '" suchen');
    }
    echo formatText($ruci->text, false);
    tpl_item_after_ruc($ruci);
}

/**
 * Outputs the rumor page
 * 
 * @global Environment $env
 * @param array $rucis array of RatableUserContentItems
 * @param int $page current page number
 * @param int $pages number of image pages, that can be shown
 * @param string $phrase search phrase, written in to the searchbar
 * @param boolean $as_page output as page?
 */
function tpl_rumor_list($rucis, $page, $pages, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_update_with_js();
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
    <? if ($pages != -1): ?>
            window.max_page = pagecount = <?php echo $pages ?>;
    <? endif ?>
    <? if ($pages == -1): ?>
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

/**
 * Outputs the write rumor form
 */
function tpl_write_rumor_item() {
    tpl_item_before("Beitrag schreiben", "pencil", "item-send item-rumor-send");
    ?>
    <textarea name="text" placeholder="…, dass " require="on">…, dass </textarea>
    <input type="hidden" name="response_to" value="-1"/>
    <?php
    tpl_item_after_send_anonymous("Absenden", "Anonym absenden", "sendRumor(false, -1)", "sendRumor(true, -1)");
}

/**
 * Outputs the rumor response item handlebars template code
 * 
 * @param string $id id of the template
 */
function tpl_write_rumor_response_item_hbs($id = "item-response-template") {
    ?><div id="<?= $id ?>" type="text/x-handlebars-template"><?
        tpl_item_before("", "", "item-send item-rumor-send");
        ?>
        <textarea name="text" placeholder="..., dass " require = "on"> ..., dass </textarea>
        <input type="hidden" name="response_to" value="-1" />
        <?
        tpl_item_after_buttons(array("{{button_answer_title}}" => array("onclick" => "sendRumor(false, {{response_to}})"), "{{button_answer_ano_title}}" => array("onclick" => "sendRumor(true, {{response_to}})"), "Schließen" => array("onclick" => "responseToItem({{response_to}})")));
        ?></div><?
    }

    /**
     * Outputs a rumor item
     * 
     * @param RatableUserContentItem $ruci rumor item
     */
    function tpl_rumor_item(RatableUserContentItem $ruci) {
        tpl_item_before("", "", "content-item", $ruci->id);
        echo formatText($ruci->text, false);
        tpl_item_after_ruc($ruci);
    }

    /**
     * Outputs the html code closing a RatableUserContent item container with an footer
     * 
     * @param RatableUserContentItem $ruci
     */
    function tpl_item_after_ruc(RatableUserContentItem $ruci) {
        ?>
    </div>
    <hr/>
    <div class="item-footer <?php echo Auth::isModerator() ? "deletable" : '' ?>">
        <ul class="ruc_footer">
            <li class="time_span_li"><?php tpl_time_span($ruci->time, true) ?></li>
            <li class="rating_li"><?php tpl_rating($ruci) ?></li>
            <li class="user_span_li 
                <? if ($ruci->canHaveResponsesButton()) echo "visible-desktop" ?>">
                    <?php tpl_user_span($ruci->userid, true, $ruci->isAnonymous()) ?>
            </li>
            <? if ($ruci->canHaveResponsesButton()): ?>
                <li class="response_to_span_li"><? tpl_item_response_to_span($ruci) ?></li>
            <? endif ?>
            <? if ($ruci->isDeletable()): ?>
                <li class="delete_span_li hidden-phone"><? tpl_item_delete_span($ruci) ?></li>
            <? endif ?>
        </ul>
    </div>
    </div>
    <?
    if ($ruci->canHaveResponses())
        tpl_response_to_div($ruci);
}

/**
 * Outputs the responses container of the RatableUserContentItem
 * 
 * @param RatableUserContentItem $ruci
 */
function tpl_response_to_div(RatableUserContentItem $ruci) {
    ?>
    <div id="responses_to_<?= $ruci->id ?>" class="responses" to="<?= $ruci->id ?>">
        <?
        if (!empty($ruci->responses)) {
            $arr = $ruci->responses;
            $count = count($arr);
            for ($index = 0; $index < $count; $index++) {
                $item = $arr[$index];
                if ($index == $count - 1) {
                    $item->setMakeResponseToID($ruci->id);
                }
                call_user_func($item->getTplFunctionName(), $item);
            }
        }
        ?>
        <div class="add_response_container" to="<?= $ruci->id ?>"></div>
    </div>
    <?
}

/**
 * Outputs the item reponse span with an response button of this item
 * 
 * @param RatableUserContentItem $ruci item
 */
function tpl_item_response_to_span(RatableUserContentItem $ruci) {
    ?>
    <span class="response_to_item"><button class="btn" onclick="responseToItem('<?= $ruci->getMakeResponseToID() ?>', '<?= $ruci->hasPersonVal() ? $ruci->person : '' ?>')"><?php tpl_icon("speech_bubbles", "Antworten") ?> Antworten</span>
    <?
}

/**
 * Outputs the delete span of the item
 * 
 * @param RatableUserContentItem $ruci item
 */
function tpl_item_delete_span(RatableUserContentItem $ruci) {
    ?>
    <button class="del_item btn" onclick="deleteItem('<?= $ruci->id ?>')"><?php tpl_icon("delete", "Löschen") ?></button>
    <?php
}

/**
 * Outputs the rating span of this item
 * 
 * @param RatableUserContentItem $ruci item
 */
function tpl_rating(RatableUserContentItem $ruci) {
    $can_rate = !Auth::isSameUser($ruci->userid);
    ?>
    <span id="<?php echo $ruci->id ?>rating" class="rating">
        <?php if ($can_rate) { ?>
            <span class="stars">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="star ' . ($i <= $ruci->own_rating ? "selected" : '') . '" onclick="rating(' . $ruci->id . ', ' . $i . ')">&#9733;</span>';
                }
                ?>
            </span>
            <?php
        }
        $show_av = !$can_rate || is_numeric($ruci->own_rating);
        tpl_average($show_av ? $ruci->rating : -1, $show_av ? $ruci->rating_count : -1, $ruci->data);
        ?>
    </span>
    <?php
}

/**
 * Outputs the average rating span
 * 
 * @param int $rating current average rating
 * @param int $count number of ratings
 * @param array $data unused addional data
 */
function tpl_average($rating, $count = -1, $data = array()) {
    ?>
    <span class="average hidden-phone">
        <? if ($rating != -1): ?>
            [<span class="num" title="<? printf("Ø Bewertung: %1.3f; Bewertungen: %2.d", $rating, $count) ?>"><? printf("Ø%1.1f, x%2.d", $rating, $count) ?></span>]
        <? endif; ?>
    </span>
    <?php
}