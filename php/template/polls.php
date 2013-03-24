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
 * Outputs an array of polls as the polls page
 * 
 * @param array $polls polls array([typestr] => [array of polls])
 * @param boolean $show_results show poll results?
 */
function tpl_polls($polls, $show_results = false) {
    tpl_before("polls");
    if (!$show_results)
        tpl_item_before();
    $first = true;
    ?>
    <form method="POST">
        <ul class="nav nav-tabs" id="poll_tabs">
            <?
            foreach ($polls as $type => $arr) {
                if (!empty($arr)) {
                    $typestr = Poll::getStringRepOfType($type);
                    echo '<li><a data-toggle="tab" href="#' . str_replace(" ", "_", $typestr) . '">' . $typestr . '</a></li>';
                    $first = false;
                }
            }
            $first = true;
            ?>
        </ul>
        <div class="tab-content">
            <?
            foreach ($polls as $type => $arr) {
                if (!empty($arr)) {
                    $typestr = Poll::getStringRepOfType($type);
                    echo '<div style="margin-right: 10px; font-size: 1.1em;" class="tab-pane' . ($first ? ' active' : "") . '" id="' . str_replace(" ", "_", $typestr) . '">';
                    foreach ($arr as $poll) {
                        if ($show_results) {
                            tpl_poll_result($poll);
                        } else {
                            tpl_poll($poll);
                        }
                    }
                    echo '</div>';
                    $first = false;
                }
            }
            ?>
        </div>
        <?
        if (!$show_results) {
            ?><button class="btn" name="submit" type="submit">Speichern</button>
        </form> <?
    }
    tpl_after();
}

/**
 * Outputs an array of polls with their results as the polls page, alias of tpl_polls($polls, true)
 * 
 * @param array $polls polls array([typestr] => [array of polls])
 */
function tpl_poll_results($polls) {
    tpl_polls($polls, true);
}

/**
 * Outputs a poll
 * 
 * @param Poll $poll
 * @return null
 */
function tpl_poll(Poll $poll) {
    if ($poll == null)
        return;
    ?><span class="poll_container"><span class="poll_question"><?= $poll->getQuestion() ?></span>
        <span class="poll_answer">
            <?
            $datalist_id = $poll->getID() . "_datalist";
            switch ($poll->getType()) {
                case Poll::TEACHER_TYPE:
                    ?><input type="text" name="<?= $poll->getID() ?>" value="<?= $poll->getUserAnswerString() ?>" class="teacher_typeahead" list="<?= $datalist_id ?>"/>
                    <datalist id="<?= $datalist_id ?>"></datalist>
                    <?
                    tpl_teacher_datalist();
                    tpl_add_js('$("#' . $datalist_id . '").html(teacher_datalist)');
                    break;
                case Poll::USER_TYPE:
                    ?><input type="text" name="<?= $poll->getID() ?>" value="<?= $poll->getUserAnswerString() ?>" class="user_typeahead" list="<?= $datalist_id ?>"/>
                    <datalist id="<?= $datalist_id ?>"></datalist>
                    <?
                    tpl_user_datalist();
                    tpl_add_js('$("#' . $datalist_id . '").html(user_datalist)');
                    break;
                default:
                    ?><input type="number" name="_<?= $poll->getID() ?>" value="<?= $poll->getUserAnswerString() ?>" pattern="[0-9]+(\.[0-9]+)?" onkeyup="$('#poll_<?= $poll->getID() ?>').val(this.value)"/><?
                    ?><input type="hidden" name="<?= $poll->getID() ?>" id="poll_<?= $poll->getID() ?>" pattern="[0-9]+(\.[0-9]+)?"/><?
                    break;
            }
            ?>
        </span></span><?
}

function tpl_teacher_datalist() {
    global $html_app;
    if (!isset($html_app["teacher_datalist_div"])) {
        ob_start();
        tpl_datalist("teacher_datalist", Teacher::getNameList());
        $html_app["teacher_datalist_div"] = ob_get_clean();
        tpl_add_js('var teacher_datalist = $("#teacher_datalist").html();');
    }
}

function tpl_user_datalist() {
    global $html_app;
    if (!isset($html_app["user_datalist_div"])) {
        ob_start();
        tpl_datalist("user_datalist", User::getNameList());
        $html_app["user_datalist_div"] = ob_get_clean();
        tpl_add_js('var user_datalist = $("#user_datalist").html();');
    }
}

/**
 * Outputs the result of a poll as an content item
 * 
 * @param Poll $poll
 * @return null
 */
function tpl_poll_result(Poll $poll) {
    if ($poll == null)
        return;
    tpl_item_before($poll->getQuestion(), "", "poll_result");
    ?>
    <span class="poll_answer">
        <?
        $data = $poll->getData();
        if (!empty($data)) {
            echo "<ol>";
            foreach ($data["answers"] as $answer_arr) {
                echo '<li><span class="poll_string_answer">' . ($poll->answerToString($answer_arr["answer"])) . ' </span><br/>
                    <span class="poll_answer_info ">[' . $answer_arr["count"] . 'x, ' . round($answer_arr["perc"]) . '%]</span></li>';
            }
            echo "</ol>";
            if ($poll->getType() == Poll::NUMBER_TYPE) {
                echo 'Ø: ' . $data["avg"] . '<br/>';
            }
            if (isset($data["number_of_answers"])) {
                $num = $data["number_of_answers"];
                if ($num == 1) {
                    echo "Diese Frage wurde einmal beantwortet.";
                } else if ($num > 1) {
                    echo "Diese Frage wurde " . $num . " mal beantwortet.";
                }
            }
        }
        ?></span><?
    tpl_item_after();
}

function tpl_add_polls() {
    tpl_before("polls/add");
    tpl_item_before_form(array());
    ?>
    Typ der Hinzuzufügenden Umfragen: <? tpl_polltype_combobox("type") ?>
    <textarea name="text"></textarea>
    <?
    tpl_infobox("", "Die einzelnen Umfragenfragen müssen durch Zeilenumbrüch getrennt geschrieben werden.");
    tpl_item_after_form(array("submit" => array("text" => "Hinzufügen", "type" => "submit")));
    tpl_after();
}

function tpl_polltype_combobox($name) {
    $arr = array(
        Poll::TEACHER_TYPE,
        Poll::USER_TYPE,
        Poll::NUMBER_TYPE
    );
    ?>
    <select style="display: inline;" name="<?php echo $name ?>" class="poll_type_combobox">
        <?php foreach ($arr as $value): ?>
            <option value="<?= $value ?>">
                <?php echo Poll::getStringRepOfType($value) ?>
            </option>
        <?php endforeach ?>
    </select>
    <?php
}

/**
 * Outputs the edit polls of poll management page
 * 
 * @param array $polls polls to be edited, polls can also be added with in this page
 */
function tpl_edit_polls($polls) {
    tpl_before("polls/edit");
    $i_polls = $polls;
    ?>
    <form method="POST">
        <ul class="nav nav-tabs" id="poll_tabs">
            <?
            $first = true;
            foreach ($polls as $type => $polls) {
                $typestr = Poll::getStringRepOfType($type);
                echo '<li' . ($first ? ' class="active"' : "") . ' data-toggle="tab"><a href="#' . str_replace(" ", "_", $typestr) . '">' . $typestr . '</a></li>';
                $first = false;
            }
            ?>
        </ul>
        <div class = "tab-content">
            <?
            $first = true;
            foreach ($i_polls as $type => $polls) {
                tpl_edit_polls_pane($type, $polls, $first);
                $first = false;
            }
            ?>
        </div>
        <?
        tpl_edit_poll_hbs();
        ?><button class="btn" name="edit" type="submit">Fertig</button>
    </form> <?
    tpl_after();
}

/**
 * Output the edit polls pane of poll type
 * 
 * @param int $type type of the polls
 * @param array $polls polls to bew shown in this pane
 * @param boolean $active is this pane marked as active in the tab container
 */
function tpl_edit_polls_pane($type, $polls, $active = false) {
    $typestr = Poll::getStringRepOfType($type);
    ?>
    <div class="tab-pane<?= ($active ? ' active' : "") ?>" id="<?= str_replace(" ", "_", $typestr) ?>">
        <div class = "poll_edit_container">
            <?
            foreach ($polls as $poll)
                tpl_edit_poll($poll);
            ?>
        </div>
        <? tpl_item_before("") ?>
        <button class="btn" onclick="addPoll(<?= $type ?>, this)">Neue Frage</button>
        <? tpl_item_after() ?>
    </div>
    <?
}

/**
 * Outputs the poll
 * 
 * @param Poll $poll
 */
function tpl_edit_poll(Poll $poll) {
    $id = $poll->getID();
    tpl_item_before("", $id, "form-horizontal edit_poll_item");
    ?>
    <input type="hidden" name="<?= $id ?>" value="<?= $id ?>"/>
    <input type="hidden" name="<?= $id ?>_action" value="edit"/>
    <label for="<?= $id ?>_question">Frage</label>
    <input type="text" id="<?= $id ?>_question" name="<?= $id ?>_question" placeholder="Frage?" required="on"/>
    <label for="<?= $id ?>_position">Position</label>
    <input type="number" id="<?= $id ?>_position" name="<?= $id ?>_position" value="<?= $poll->getPosition() ?>"/>
    <button class="btn btn-warning" onclick="deletePoll('<?= $poll->getID ?>', true)">Löschen</button>
    <?
    tpl_item_after();
}

/**
 * Outputs the edit new poll handlebars template, needed to create new polls within the poll edit or management page
 */
function tpl_edit_poll_hbs() {
    ?><div id="edit_poll_item_template" type="text/x-handlebars-template"><?
    tpl_item_before("", '{{type}}', "form-horizontal edit_poll_item");
    ?>
        <input type="hidden" name="{{id}}" value="{{id}}"/>  
        <input type="hidden" name="{{id}}_action" value="add"/>  
        <input type="hidden" name="{{id}}_type" value="{{type}}"/>   
        <label for="{{id}}_question">Frage</label>
        <input type="text" id="{{id}}_question" name="{{id}}_question" placeholder="Frage?" required="on"/>
        <label for="{{id}}_position">Position</label>
        <input type="number" id="{{id}}_position" name="{{id}}_position" value="{{position}}"/>
        <button class="btn btn-warning" onclick="deletePoll('{{id}}', false)">Löschen</button>
        <? tpl_item_after(); ?>
    </div><?
}