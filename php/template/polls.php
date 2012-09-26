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
        tpl_item_before_form(array("method" => "POST"));
    $first = true;
    ?>
    <form method="POST">
        <ul class="nav nav-tabs" id="poll_tabs">
            <?
            foreach ($polls as $type => $arr) {
                if (!empty($arr)) {
                    $typestr = Poll::getStringRepOfType($type);
                    echo '<li' . ($first ? ' class="active"' : "") . ' data-toggle="tab"><a href="#' . str_replace(" ", "_", $typestr) . '">' . $typestr . '</a></li>';
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
                    echo '<div class="tab-pane' . ($first ? ' active' : "") . '" id="' . str_replace(" ", "_", $typestr) . '">';
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
            ?><button class="btn" name="submit" type="submit">Absenden</button>
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
            switch ($poll->getType()) {
                case Poll::TEACHER_TYPE:
                case Poll::USER_TYPE:
                    ?><input type="text" name="<?= $poll->getID() ?>" class="teacher_typeahead" list="<?= $poll->getID() ?>_datalist" required="on"/><?
            tpl_datalist($poll->getID() . "_datalist", Poll::TEACHER_TYPE ? Teacher::getNameList() : User::getNameList());
            break;
        default:
                    ?><input type="number" required="on" name="<?= $this->id ?>" value="<?= $poll->getUserAnswer() != null ? $poll->getUserAnswer() : "" ?>"/><?
            break;
    }
            ?>
        </span></span><?
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
                echo "<li>" . ($poll->getType() == Poll::TEACHER_TYPE ? Teacher::getByID($answer_arr["answer"])->getNameStr() : User::getByID($answer_arr["answer"])->getName()) . ' [' . $answer_arr["count"] . 'x, ' . $answer_arr["perc"] . ']</li>';
            }
            echo "</ol>";
            if ($poll->getType() == Poll::NUMBER_TYPE) {
                echo 'Ø: ' . $data["avg"];
            }
        }
        ?></span><?
    tpl_item_after();
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
    <input type="hidden" name="<?= $id ?>_action" value="ediz"/>
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
    ?><script id="edit_poll_item_template" type="text/x-handlebars-template"><?
    tpl_item_before("", '{{type}}', "form-horizontal edit_poll_item");
    ?>
        <input type="hidden" name="{{id}}_action" value="add"/>  
        <input type="hidden" name="{{id}}_type" value="{{type}}"/>   
        <label for="{{id}}_question">Frage</label>
        <input type="text" id="{{id}}_question" name="{{id}}_question" placeholder="Frage?" required="on"/>
        <label for="{{id}}_position">Position</label>
        <input type="number" id="{{id}}_position" name="{{id}}_position" value="{{position}}"/>
        <button class="btn btn-warning" onclick="deletePoll('{{id}}', false)">Löschen</button>
    <? tpl_item_after(); ?>
    </script><?
}