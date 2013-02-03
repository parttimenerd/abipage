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

/* user_characteristics(id INT AUTO_INCREMENT PRIMARY KEY, userid INT, type TINYINT, topic INT, text TEXT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics table: " . self::$db->error);
  user_characteristics_topics(id INT AUTO_INCREMENT PRIMARY KEY, type TINYINT, text TEXT, position INT, FULLTEXT(text)) ENGINE = MYISAM") or die("Can't create user_characteristics_topics table: " . self::$db->error); */

function tpl_usercharacteristics_result_page(User $user, array $itemarr, $as_page = true) {
    global $env;
    if (!$env->user_characteristics_editable){
       return; 
    }
    if ($as_page) {
        tpl_before("user_characteristics", $user->getName(), tpl_get_user_subtitle($user));
    }
    if ($user->hasNotAllUCQuestionsAnswered()){
        $number = $user->getNumberOfUCQuestionsToBeAnswered();
        if ($number == 1) {
            $text = $user->getName() . " muss noch eine Steckbrieffrage beantworten.";
        } else {
            $text = $user->getName() . " muss noch " . $number . " Steckbrieffragen beantworten.";
        }
        tpl_infobox("", $text);
    }
    foreach ($itemarr as $item) {
        tpl_usercharacteristics_result_item($item);
    }
    if ($as_page) {
        tpl_after();
    }
}

/**
 * 
 * @global Environment $env
 * @param UserCharacteristicsItem $item
 */
function tpl_usercharacteristics_result_item(UserCharacteristicsItem $item) {
    global $env;
    if ($item->getAnswer() == "") {
        return;
    }
    tpl_item_before($item->getTopic()->getText());
    switch ($item->getType()) {
        case UserCharacteristicsTopic::LONGTEXT_TYPE:
            echo $item->getAnswer();
            break;
        case UserCharacteristicsTopic::SHORTTEXT_TYPE:
            echo $item->getAnswer();
            break;
        case UserCharacteristicsTopic::NUMBER_TYPE:
            echo $item->getAnswer();
            break;
        case UserCharacteristicsTopic::PICTURE_TYPE:
            ?>
            <img src="<?= tpl_url($env->upload_path . '/' . $item->getAnswer()) ?>" class="uc_img"/><br/>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $env->max_upload_pic_size * 1048576 ?>"/>
            <?
            break;
    }
    tpl_item_after();
}

/**
 * 
 * @param array $topics
 */
function tpl_usercharacteristics_answer_page(array $topics) {
    tpl_before("user_characteristics");
    ?><form method="POST" class="uc_answer_form" enctype="multipart/form-data"><?
    foreach ($topics as $topic) {
        tpl_usercharacteristics_answer_topic($topic);
    }
    ?>
        <input name="submit" class="btn uc_submit" type="submit"/>
    </form>
    <?
    tpl_after();
}

function tpl_usercharacteristics_answer_topic(UserCharacteristicsTopic $topic) {
    global $env;
    tpl_item_before($topic->getText());
    $user_answer = $topic->getUserAnswerItem();
    if ($user_answer == null) {
        $user_answer = "";
    } else {
        $user_answer = $user_answer->getAnswer();
    }
    switch ($topic->getType()) {
        case UserCharacteristicsTopic::LONGTEXT_TYPE:
            ?><textarea name="<?= $topic->getID() ?>"><?= $user_answer ?></textarea><?
            break;
        case UserCharacteristicsTopic::SHORTTEXT_TYPE:
            ?><input name="<?= $topic->getID() ?>" value="<?= $user_answer ?>"></input><?
            break;
        case UserCharacteristicsTopic::NUMBER_TYPE:
            ?><input type="number" pattern="[0-9]+(\.[0-9]*)?" name="<?= $topic->getID() ?>" value="<?= $user_answer ?>" ></input><?
            break;
        case UserCharacteristicsTopic::PICTURE_TYPE:
            ?>
            <? if ($user_answer != ""): ?>
                <img src="<?= tpl_url($env->upload_path . '/' . $user_answer) ?>" class="uc_img"/><br/>
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= $env->max_upload_pic_size * 1048576 ?>"/>
                <hr/>
            <? endif ?>
            Neues Bild hochladen:<br/>
            <input type="file" accept="image/*" name="<?= $topic->getID() ?>"/>
            (Das Bild darf maximal <?= $env->max_upload_pic_size ?>MB groß sein und sollte die Dateiendung .png, .jpg, .jpeg, .bmp oder .gif haben.)
            <?
            break;
    }
    tpl_item_after();
}

/**
 * Shows an info field if the user hasn't answered all user characteristics questions.
 * 
 * @global Environment $env
 */
function tpl_uc_answer_info() {
    global $env;
    if (Auth::getUser()->hasNotAllUCQuestionsAnswered() && $env->user_characteristics_editable) {
        $number = Auth::getUser()->getNumberOfUCQuestionsToBeAnswered();
        if ($number == 1) {
            $text = "Es ist noch eine Steckbrieffrage unbeantwortet.";
        } else {
            $text = "Es sind noch " . $number . " Steckbrieffragen unbeantwortet.";
        }
        tpl_infobox("", $text, tpl_url("user_characteristics"));
    }
}
?>
