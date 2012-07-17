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

class ImagesHandler extends RatableUserContentHandler {

    public function __construct() {
        parent::__construct(new ImageList(), "tpl_image_list");
    }

    public function post_impl() {
        global $env;
        if (!empty($_FILES["uploaded_file"]) && ($_FILES['uploaded_file']['error'] == 0) && isset($_POST["description"])) {
            $id = $this->list->addImage($_POST["description"]);
            $env->uploadImage($id);
            return true;
        }
        return false;
    }

    public function processPhraseImpl($phrase) {
        $phrase = cleanInputText($phrase);
        $this->list->appendToWhereApp(" AND (MATCH(description) AGAINST('" . $phrase . "') OR description LIKE '%" . $phrase . "%')");
    }

}