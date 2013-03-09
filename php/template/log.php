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
 * Outputs the log container and the handlebars template code
 */
function tpl_log_container() {
    ?>
    <div id="log" class="resizable" style="left: 0px">
        <div id="log_container">
        </div>
    </div>
    <?
    tpl_log_hbs_template();
}

/**
 * Outputs the handlebars template code
 */
function tpl_log_hbs_template() {
    ?>
    <script id="log_table_template" type="text/x-handlebars-template">
        <button type="button" class="btn" style="width: 100%" data-toggle="collapse" data-parent="#log_container" href="#{{id}}">
            Log {{now "%T,%L"}} [Database: {{s_to_ms data.db_queries.time}}ms; Overall: {{s_to_ms data.time}}ms; Logs: {{length data.logs}}; Queries: {{length data.db_queries.queries}}]
        </button>
        <div id="{{id}}" class="log_container accordion-body collapse" data-parent="#log_container">
            {{#with data}}
            <h3>Zeiten</h3>
            <div id="time_log_{{../id}}">
                <table class="table table-striped time-log">
                    <thead>
                        <tr>
                            <th class="sort" data-sort="tl_message">Message</th>
                            <th class="sort" data-sort="tl_time">Time in ms</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        {{#each time_logs}}
                        <tr>
                            <td class="tl_message">{{msg}}</td>
                            <td class="time tl_time">{{s_to_ms duration}}</td>
                        </tr>
                        {{/each}}
                    </tbody>
                    <tr>
                        <td>Overall</td>
                        <td class="time">{{s_to_ms time}}</td>
                    </tr>
                </table>
            </div>
            {{#any logs}}
            <h3>Logmeldungen</h3>
            <div id="log_messages_table_{{../../id}}">
                <input class="search" placeholder="Suche" onkeyup="log_messages_table_{{../../id}}.fuzzySearch($(this).val())" autocomplete="off"/>
                <table class="table table-striped log">
                    <thead>
                        <tr>
                            <th class="sort" data-sort="lmt_time">Time in ms</th>
                            <th class="sort" data-sort="lmt_type">Typ</th>
                            <th class="sort" data-sort="lmt_message">Message</th>
                            <th class="hidden-phone sort" data-sort="lmt_caller">Call</th>
                            <th class="hidden-phone sort" data-sort="lmt_caller_file">File</th>
                            <th class="hidden-phone sort" data-sort="lmt_caller_file lmt_caller_line">Line</th>
                            <th class="visible-desktop sort" data-sort="lmt_caller_caller">Call call</th>
                            <th class="visible-desktop sort" data-sort="lmt_caller_caller_file">File</th>
                            <th class="visible-desktop sort" data-sort="lmt_caller_caller_file lmt_caller_caller_line">Line</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        {{#each logs}}
                        <tr class=".{{parent_type}}">
                            <td class="time lmt_time">{{s_to_ms time}}</td>
                            <td class="lmt_type">{{type_str}}</td>
                            <td class="lmt_message">{{msg}}</td>
                            {{#any backtrace}}
                                {{#withFirst backtrace}}
                                <td class="hidden-phone lmt_caller">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                                <td class="hidden-phone lmt_caller_file">{{file}}</td>
                                <td class="hidden-phone lmt_caller_line">{{line}}</td>
                                {{/withFirst}}
                                {{#with backtrace.[1]}}
                                <td class="visible-desktop lmt_caller_caller">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                                <td class="visible-desktop lmt_caller_caller_file">{{file}}</td>
                                <td class="visible-desktop lmt_caller_caller_line">{{line}}</td>
                                {{/with}}
                            {{/any}}
                            {{#empty backtrace}}
                                <td class="hidden-phone lmt_caller"></td>
                                <td class="hidden-phone lmt_caller_file"></td>
                                <td class="hidden-phone lmt_caller_line"></td>
                                <td class="visible-desktop lmt_caller_caller"></td>
                                <td class="visible-desktop lmt_caller_caller_file"></td>
                                <td class="visible-desktop lmt_caller_caller_line"></td>
                            {{/empty}}
                        </tr>
                        {{/each}}
                    </tbody>
                </table>
            </div>
            {{/any}}
            <h3>Datenbankabfragen</h3>
            <div id="database_query_table_{{../id}}">
                <input class="search" placeholder="Suche" onkeyup="database_query_table_{{../id}}.fuzzySearch($(this).val())" autocomplete="off"/>
                <table class="table table-striped db-log">
                    <thead>
                        <tr>
                            <th class="sort" data-sort="dbt_num"></th>
                            <th class="sort" data-sort="dbt_query_text">Query</th>
                            <th class="sort" data-sort="dbt_time">Time in ms</th>
                            <th class="sort" data-sort="dbt_time">%</th>
                            <td class="hidden-phone sort" data-sort="dbt_caller">Caller</td>
                            <td class="visible-desktop" data-sort="dbt_caller_caller">Caller caller</td>
                        </tr>
                    </thead>
                    <tbody class="list">
                     {{#each db_queries.queries}}
                        <tr>
                            <td class="dbt_num">{{num}}}</td>
                            <td class="dbt_query_text">{{query}}</td>
                            <td class="time dbt_time">{{s_to_ms time}}</td>
                            <td class="dbt_time">{{perc}}</td>
                            {{#withFirst backtrace}}
                            <td class="hidden-phone dbt_caller">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                            {{/withFirst}}
                            {{#with backtrace.[1]}}
                            <td class="visible-desktop dbt_caller_caller">{{class}}{{type}}{{function}}({{join args ", "}})</td>
                            {{/with}}
                        </tr>
                      {{/each}}
                    </tbody>
                    <tr>
                        <td></td>
                        <td>Overall</td>
                        <td class="time">{{s_to_ms db_queries.time}}</td>
                        <td></td>
                        <td class="hidden-phone"></td>
                        <td class="visible-desktop"></td>
                    </tr>
                </table>
            </div>
            {{/with}}
        </div>
    </script>
    <?
}