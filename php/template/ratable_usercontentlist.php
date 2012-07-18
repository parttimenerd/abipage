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

function tpl_image_list($images, $page, $pages, $sort_str = "", $phrase = "", $as_page = true) {
	global $env;
    if ($as_page) {
        tpl_before("images", null, null, array("url_part" => "images", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
		echo '<div class="imagelist">';
	}
    if ($page == 1 && $as_page && $env->images_editable) {
        tpl_image_upload_item();
    }
    foreach ($images as $img) {
        tpl_image_item($img["id"] . '.' . $img["format"], $img["id"], $img["description"], $img["userid"], $img["time"], $img["own_rating"], $img["rating"], true);
    }
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('images') ?>";
        <?php if($as_page) echo 'var page = ' . $page . ';' ?>
        var max_page = pagecount = <?php echo $pages ?>;
        <?php echo $sort_str == "" ? "" : 'var sort_str = "' . $sort_str . '";' ?>
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
    }
}

function tpl_image_upload_item($with_descr = true) {//"enctype" => "multipart/form-data"
    //tpl_item_before_form(array("id" => "file_upload", "enctype" => "multipart/form-data"), "Bild hochladen", "camera", "item-send");
	tpl_item_before("Bild hochladen", "camera", "item-send");
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
    tpl_item_after_send("Hochladen", "send", "uploadImage()", "<div class='progress' style='visibility: hidden'>
    <div class='bar' style=\"width: 0%;\"></div>
</div>");
}

function tpl_image_item($imgfile, $id, $descr, $senduser, $time, $own_rating, $average_rating, $show_name) {
    global $env;
    tpl_item_before("", "", "content-item", $id); 
    ?>
    <a class="item-content" href="<?php echo tpl_url($env->upload_path . '/' . $imgfile) ?>" title="<?php echo $descr = str_replace('\r\n', " ", formatText($descr)) ?>">
        <img src="<?php echo tpl_url($env->upload_path . '/thumbs/' . $imgfile) ?>"/>
    </a><br/>
    <?php
	//var_dump(formatText($descr), $descr);
	echo str_replace('&lt;br/>', "", formatText($descr)) ?>		
    <?php
    tpl_item_after_ruc($id, $time, $senduser, $own_rating, $average_rating, $show_name);
}

function tpl_quote_list($quotes, $page, $pages, $sort_str, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_before("quotes", null, null, array("url_part" => "quotes", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
    }
    if ($page == 1 && $as_page && $env->quotes_editable) {
        tpl_write_quote_item();
    }
    foreach ($quotes as $quote) {
        tpl_quote_item($quote["id"], $quote["person"], $quote["text"], $quote["userid"], $quote["time"], $quote["own_rating"], $quote["rating"], (!$quote["isanonymous"] || Auth::isAdmin()));
    }
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('quotes') ?>";
        <?php if($as_page) echo 'var page = ' . $page . ';' ?>
        var max_page = <?php echo $pages ?>;
        var sort_str = "<?php echo $sort_str ?>";
        var phrase = "<?php echo $phrase ?>";
    </script>
    <?php
    if ($as_page) {
        tpl_after();
    }
}

function tpl_write_quote_item() {
    tpl_item_before("Zitat hinzufügen", "pencil", "item-send item-quote-send");
    ?>
        <input type="text" placeholder="Zitierter Lehrer" name="person" class="teacher_typeahead" required="on" pattern="([A-ZÄÖÜ.]([a-zßäöü.](-[a-zßäöüA-ZÄÖÜ.])?)+ ?){1,3}"/>
        <textarea name="text" placeholder="Zitat" require="on"></textarea>
	<?php
	tpl_item_after_send_anonymous("Hinzufügen", "Anonym hinzufügen", "sendQuote(false)", "sendQuote(true)");
    tpl_add_js('var teacher_arr = ' . json_encode(TeacherList::getTeacherNameList()) . ';
	$(".teacher_typeahead").typeahead({source: teacher_arr});');
    //tpl_item_after();
}

function tpl_quote_item($id, $person, $text, $senduser, $time, $own_rating, $average_rating, $show_name) {
    //global $env;
    tpl_item_before($person, "speech_bubbles", "content-item", $id);
    echo formatText($text);
    tpl_item_after_ruc($id, $time, $senduser, $own_rating, $average_rating, $show_name);
}

function tpl_rumor_list($rumors, $page, $pages, $sort_str, $phrase, $as_page = true) {
    global $env;
    if ($as_page) {
        tpl_before("rumors", null, null, array("url_part" => "rumors", "page" => $page, "pagecount" => $pages, "phrase" => $phrase));
    }
    if ($page == 1 && $as_page && $env->rumors_editable) {
        tpl_write_rumor_item();
    }
    foreach ($rumors as $rumor) {
        tpl_rumor_item($rumor["id"], $rumor["text"], $rumor["userid"], $rumor["time"], $rumor["own_rating"], $rumor["rating"], (!$rumor["isanonymous"] || Auth::isAdmin()));
    }
    ?>
    <script>
        var rating_url = "<?php echo tpl_url('rumors') ?>";
        <?php if($as_page) echo 'var page = ' . $page . ';' ?>
        var max_page = <?php echo $pages ?>;
        var sort_str = "<?php echo $sort_str ?>";
        var phrase = "<?php echo $phrase ?>";
    </script>
    <?php
    if ($as_page) {
        tpl_after();
    }
}

function tpl_write_rumor_item() {
    tpl_item_before("Beitrag schreiben", "pencil", "item-rumor-send");
    ?>
        <textarea name="text" placeholder="..., dass" require="on">..., dass</textarea>
    <?php
    tpl_item_after_send_anonymous("Absenden", "Anonym absenden", "sendRumor(false)", "sendRumor(true)");
}

function tpl_rumor_item($id, $text, $senduser, $time, $own_rating, $average_rating, $show_name) {
//    global $env;
    tpl_item_before("", "", "content-item", $id);
    echo formatText($text);
    tpl_item_after_ruc($id, $time, $senduser, $own_rating, $average_rating, $show_name);
}

function tpl_item_after_ruc($id, $time, $user, $own_rating, $avrating, $show_name) {
    ?>
    </div>
	<hr/>
    <div class="item-footer <?php echo Auth::isAdmin() ? "deletable" : '' ?>">
        <?php tpl_time_span($time) ?>
        <?php tpl_rating($id, $own_rating, $user, $avrating) ?>
        <?php tpl_user_span($show_name ? $user : null) ?>
        <?php if (Auth::isAdmin()) tpl_item_delete_span($id) ?>
    </div>
    </div>
    <?php
}

function tpl_item_delete_span($id) {
    ?>
    <span class="del_item"><?php tpl_icon("delete", "Löschen", "deleteItem('" . $id . "')") ?></span>
    <?php
}

function tpl_rating($id, $own, $senduser, $average_rating) {
	$can_rate = (is_numeric($senduser) ? $senduser : $senduser->getID()) != Auth::getUserID();
	?>
    <span id="<?php echo $id ?>rating">
        <?php if ($can_rate) { ?>
            <span class="stars">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="star ' . ($i <= $own ? "selected" : '') . '" onclick="rating' . $id . ', ' . $i . ')">&star;</span>';
                }
                ?>
            </span>
        <?php }
		if (!$can_rate || is_numeric($own)): ?>
        <span class="average">[<span class="num" title="Durschnittliche Berwertung"><?php echo $average_rating ?></span>]</span>
		<?php endif ?>
	</span>
    <?php
}

/*
function tpl_page_back_forth($page, $pagecount, $url, $with_page_selector = false, $sort_str = "") {
    ?>
    <div class="page_back_forth">
        <?php if ($page > 1) { ?>
            <span class="<?php echo $with_page_selector ? "back_forth_wps" : "back_forth" ?> back_area">
                <a href="<?php echo $url . '/' . ($page - 1) ?>" class="button back_area_button">← Vorherige Seite</a>
            </span>
            <?php
        }
        if ($sort_str == "") {
            $sort_str = "time_desc";
        }
        if ($with_page_selector):
            ?>
            <span class="back_forth_wps page_selector_area">
                <form method="GET" action="<?php echo $url ?>">
                    <select style="display: inline;" name="page">
                        <?php
                        for ($i = 1; $i <= $pagecount; $i++) {
                            echo '<option value="' . $i . '"' . ($i == $page ? ' selected="selected"' : '') . '>Seite ' . $i . '</option>' . "\n";
                        }
                        ?>
                    </select> 
                    <select style="display: inline;" name="sort">
                        <?php
                        $arr = array(
                            "time_desc" => "↓Zeit",
                            "time_asc" => "↑Zeit",
                            "rating_desc" => "↓Bewertung",
                            "rating_asc" => "↑Bewertung"
                        );
                        foreach ($arr as $key => $value) {
                            echo '<option value="' . $key . '"' . ($key == $sort_str ? ' selected="selected"' : '') . '>' . $value . '</option>' . "\n";
                        }
                        ?>
                    </select>
                    <input type = "submit" value = "Gehe zu"/>
                </form>
            </span>
        <?php endif ?>
        ?>
        <?php if ($page < $pagecount): ?>
            <span class="<?php echo $with_page_selector ? "back_forth_wps" : "back_forth" ?> forth_area">
                <a href="<?php echo $url . '/' . ($page + 1) ?>" class="button forth_area_button">Nächste Seite →</a>
            </span>
        <?php endif ?>
    </div>
    <?php
}*/