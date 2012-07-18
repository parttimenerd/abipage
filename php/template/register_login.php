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

function tpl_register() {
    global $env;
    tpl_before("register");
    tpl_item_before_form(array(), "", "", "register");
    ?>
    <input type="text" name="name" title="Name ([Vorname] [Nachname])" placeholder="Name ([Vorname] [Nachname])" autocomplete="on" required="on" pattern="([A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <input type="email" name="mail_adress" title="E-MailAdresse" placeholder="E-MailAdresse" autocomplete="on" required="on"/><br/>
    <input type="password" name="password" title="Passwort" placeholder="Passwort" autocomplete="on" required="on"/><br/>
    <input type="password" name="password_repeat" title="Passwort wiederholen" placeholder="Passwort wiederholen" autocomplete="on" required="on"/><br/>
    <input type="number" name="math_course" title="Mathekursnummer, z.B.: 1" placeholder="Mathekursnummer, z.B.: 1" min="1" max="20" required="on"/><br/>
    <input type="text" name="math_teacher" title='Mathelehrer, z.B. "Herr Müller"' placeholder='Mathelehrer, z.B. "Herr Müller"' required="on" pattern="([A-ZÄÖÜ]([a-zßäöü](-[a-zßäöüA-ZÄÖÜ])?)+ ?){2,3}"/><br/>
    <?php if ($env->has_forum): ?>
        <input type="checkbox" value="true" name="reg_in_forum" selected="selected"/> Automatisch auch im Forum registrieren<br/>
    <?php endif ?>
    <?php if ($env->has_wiki): ?>
        <input type="checkbox" value="true" name="reg_in_wiki" selected="selected"/> Automatisch auch im Wiki registrieren<br/>
    <?php endif ?>
    <?php
    tpl_infobox("", 'Sie stimmen mit der Registrierung den <a href="terms_of_use" target="_blank">Nutzungsbedingungen</a> zu.<br/>
                Außerdem werden <a href="https://de.wikipedia.org/wiki/Cookie" target="_blank">Cookies</a> auf ihrem Computer zwecks Anmeldung und Statistik gespeichert.');
    tpl_item_after_send("Registrieren", "register");
    tpl_item_after();
    tpl_after();
}

function tpl_welcome_wait_for_activation() {
    tpl_before("", "Willkommen");
    tpl_item_before();
    ?>
    Sie sind nun auf dieser Seite angemeldet.
    Bitte warten sie, bis sie von einem Moderator freigeschalten werden.
    <?php
    tpl_item_after();
    tpl_after();
}

function tpl_login() {
    global $env;
    tpl_before("login");
    tpl_item_before_form(array("action" => $env->url . "/login"), "", "", "login");
    ?>
    <input type="text" name="name" title="Name" placeholder="Name" autocomplete="on"/><br/>
    <input type="password" name="password" title="Passwort" placeholder="Passwort" autocomplete="on"/><br/>
    <?php
    tpl_infobox("", "Wenn sie noch nicht registriert sind, registrieren sich sich bitte <a href='register'>hier</a>.<br/>
			Wenn sie ihr Passwort vergessen haben, können sie ihr Passwort mir diesem <a href='forgot_password'>Link</a> zurücksetzen.
			Es wird bei der Registrierung ein Cookie auf dem Computer gespeichert um die Anmeldung zu ermöglichen.");
    tpl_item_after_send("Anmelden", "login");
    tpl_after();
}

function tpl_forgot_password() {
    tpl_before("", "Passwort vergessen");
    tpl_item_before_form(array(), "", "", "forgot_password");
    ?>
    <input type="text" name="name_or_email" title="Name oderE-Mail-Adresse" placeholder="Name oderE-Mail-Adresse"/><br/>
    <?php
    tpl_item_after_send();
    tpl_after();
}

function tpl_new_password($action_url) {
    tpl_before("", "Neues Passwort");
    tpl_item_before_form(array("action" => $action_url), "", "", "new_password");
    ?>
    <input type="password" name="pwd" placeholder="" title="Neues Passwort"/><br/>
    <input type="password" name="pwd_repeat" placeholder="" title="Neues Passwort wiederholen"/><br/>
    <?php
    tpl_item_after_send("Passwort setzen");
    tpl_after();
}

function tpl_terms_of_use() {
    global $env;
    tpl_before("terms_of_use");
    tpl_item_before();
    echo formatText($env->terms_of_use);
    tpl_item_after();
    tpl_after();
}