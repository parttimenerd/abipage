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

function tpl_log_container() {
    ?>
    <div id="log" class="resizable">
        <div id="log_container">
        </div>
    </div>
    <?
    tpl_log_hbs_template();
}

function tpl_log_hbs_template() {
    ?>
    <script id="log_table_template" type="text/x-handlebars-template">
        <button type="button" class="btn" style="width: 100%" data-toggle="collapse" data-parent="#log_container" href="#{{id}}">
            Log {{now "%T,%L"}} [Database: {{s_to_ms data.db_queries.time}}ms; Overall: {{s_to_ms data.time}}ms; Logs: {{length data.logs}}; Queries: {{length data.db_queries.queries}}]
        </button>
        <div id="{{id}}" class="accordion-body collapse" data-parent="#log_container">
            {{#with data}}
            <h3>Zeiten</h3>
            <table class="table table-striped time-log">
                <tr>
                    <th>Message</th>
                    <th>Time in ms</th>
                </tr>
                {{#each time_logs}}
                <tr>
                    <td>{{msg}}</td>
                    <td class="time">{{s_to_ms duration}}</td>
                </tr>
                {{/each}}
                <tr>
                    <td>Overall</td>
                    <td class="time">{{s_to_ms time}}</td>
                </tr>
            </table>
            {{#any logs}}
            <h3>Logmeldungen</h3>
            <table class="table tablesorter table-striped log">
                <thead>
                    <tr>
                        <th>Time in ms</th>
                        <th>Message</th>
                        <th>Call</th>
                        <th>File</th>
                        <th>Line</th>
                        <th>Call call</th>
                        <th>Line</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each logs}}
                    <tr class=".{{parent_type}}">
                        <td class="time">{{s_to_ms time}}</td>
                        <td>{{type_str}}</td>
                        <td>{{msg}}</td>
                        {{#withFirst backtrace}}
                        <td class="hidden-phone">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                        <td class="hidden-phone">{{file}}</td>
                        <td class="hidden-phone">{{line}}</td>
                        {{/withFirst}}
                        {{#with backtrace.[1]}}
                        <td class="visible-desktop">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                        <td class="visible-desktop">{{line}}</td>
                        {{/with}}
                    </tr>
                    {{/each}}
                </tbody>
            </table>
            {{/any}}
            <h3>Datenbankabfragen</h3>
            <table class="table tablesorter table-striped db-log">
                <thead>
                    <tr>
                        <th></th>
                        <th>Query</th>
                        <th>Time in ms</th>
                        <th>%</th>
                        <td class="hidden-phone">Caller</td>
                        <td class="visible-desktop">Caller caller</td>
                    </tr>
                </thead>
                {{#each db_queries.queries}}
                <tbody>
                    <tr>
                        <td>{{num}}}</td>
                        <td>{{query}}</td>
                        <td class="time">{{s_to_ms time}}</td>
                        <td>{{perc}}</td>
                        {{#withFirst backtrace}}
                        <td class="hidden-phone">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                        {{/withFirst}}
                        {{#with backtrace.[1]}}
                        <td class="visible-desktop">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                        {{/with}}
                    </tr>
                </tbody>
                {{/each}}
                <tr>
                    <td></td>
                    <td>Overall</td>
                    <td class="time">{{s_to_ms db_queries.time}}</td>
                    <td></td>
                </tr>
            </table>
            {{/with}}
        </div>
    </script>
    <?
}
