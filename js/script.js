
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

rated_items = [];

function rating(id, rating) {
    fillStars(id, rating);
    ajax({
        data: {
            rating: rating,
            id: id
        },
        func: function(data) {
            $("#" + id + "rating .average").replaceWith(data["html"]);
            rated_items.push(id);
        },
        needs: ["html"]
    });
}

function fillStars(id, rating) {
    num = rating;
    $("#" + id + "rating .stars .star").each(function() {
        if (num > 0) {
            $(this).attr("class", "star selected");
        } else {
            $(this).attr("class", "star");
        }
        num--;
    });
}

function deleteItem(id) {
    var input = prompt("Wollen sie diesen Beitrag wirklich löschen? Dann geben sie bitte einen Grund an: ",
            this.item_delete_cause !== undefined ? this.item_delete_cause : "");
    if (input === null) {
        return;
    }
    if (input.length > 0) {
        this.item_delete_cause = input;
    }
    if (this.item_delete_cause !== undefined) {
        ajax({
            data: {
                "delete": 0,
                "id": id,
                "cause": this.item_delete_cause
            },
            func: function(data) {
                $("#" + data["id"]).remove();
            },
            needs: ["id"]
        });
    } else {
        deleteItem(id);
    }
}

var is_loading = false;
var phrase = "";
var last_item = null;
var chocolat_options = {};
var _page = 1;

if (window.rating_url !== undefined) {
    var rating_url2 = rating_url;
}

function loadItems() {
    if (this.lock === undefined || this.lock === false) {
        this.lock = true;
        $this = this;
        if (window._page < max_page) {
            /*if (chocolat_options != {}){
             last_item = $(".imagelist .item").last();
             } else {
             last_item = $(".content .item").last();
             }*/
            ajax({
                type: "GET",
                url: rating_url2,
                data: {
                    'page': window._page + 1,
                    'phrase': phrase,
                    'start_time': start_time
                },
                func: function(data) {
                    addLoadedItemsHTML(data["html"]);
                    $this.lock = false;
                }
            });
            window._page++;
        }
    }
}

function addLoadedItemsHTML(html, is_append) {
    if (arguments.length === 1) {
        is_append = true;
    }
    if (html !== "") {
        if (chocolat_options.length > 0) {
            if (is_append) {
                $('.imagelist').append(html);
            } else {
                $('.imagelist').prepend(html);
            }
            $('.imagelist a.item-content').Chocolat(chocolat_options);
        } else {
            // hmtl = removeElements(html, ".alert-info");
            html = $("<div>" + html + "</div>");
            html.find(".alert-info").remove();
            html = html.html();
            if (is_append) {
                $('.content').append(html);
            } else {
                $(".content").prepend(html);
            }
        }
    }
//last_item.focus();
}

jQuery.fn.reverse = [].reverse;

function search(_phrase, no_timeout) {
    this.time_buffer = window.search_time_buffer;
    phrase = _phrase;
    page = 1;
    var time_diff = 0;
    if (this.last_search_time !== undefined) {
        time_diff = (new Date()).getTime() - this.last_search_time;
    }
    if (this.last_search_time === undefined || time_diff >= this.time_buffer) {
        ajax({
            type: "GET",
            data: {
                'page': "1",
                'phrase': phrase,
                'ajax': true
            },
            func: function(data) {
                $(".content-item").remove();
                addLoadedItemsHTML(data["html"]);
                window.start_time = getTime();
            }
        });
        this.last_search_time = (new Date()).getTime();
    } else {
        if (time_diff <= this.time_buffer || no_timeout === false) {
            if (this.timeout !== undefined) {
                clearTimeout(this.timeout);
            }
            this.timeout = window.setTimeout("search('" + _phrase + "', true)", time_diff);
        }
    }
}
function initBottomLoad() {
    if (window.max_page !== undefined) {
        $(window).bottom({
            proximity: 0.2
        }).bind('bottom', function() {
            loadItems();
        });
    }
}
setTimeout("initBottomLoad()", 300);

if ($("#drop_area") !== null && $("#drop_area").length !== 0) {
    $('.imagelist a.item-content').Chocolat(chocolat_options);

    /* based on http://robertnyman.com/2010/12/16/utilizing-the-html5-file-api-to-choose-upload-preview-and-see-progress-for-multiple-files/,
     http://www.tutorials.de/content/1065-echter-ajax-datei-upload.html	*/

    var dropArea = $(".item-send")[0],
            file = null,
            file_content = "",
            file_type = "",
            droparea_html = "",
            droparea_onclick = null,
            progress = $(".item-send .progress"),
            progressbar = $(".item-send .progress .bar");

    function handleFiles(files) {
        if (!files.length)
            return;

        var li, fileInfo;

        file = files[0];


        if (file.size < max_file_size && (/image/i).test(file.type)) {
            droparea_html = $("#drop_area").html();
            droparea_onclick = $("#drop_area").attr("onclick");
            $("#drop_area").attr("onclick", "");
            $("#drop_area").html("");
            $("#drop-image-area .hide_when_unused").attr("style", "display: block");
            if (typeof FileReader !== "undefined") {
                $("#drop_area").html('<img />');
                img = $("#drop_area img");
                reader = new FileReader();
                reader.onload = (function(theImg) {
                    return function(evt) {
                        theImg.attr("src", evt.target.result);
                        $("#file_input").val(file);
                        file_content = evt.target.result;
                        $(".item-send .descr").focus();
                        file_type = file.type;
                    };
                }(img));
                reader.readAsDataURL(file);
            }
        }
    }

    dropArea.ondragenter = function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        return false;
    };

    dropArea.ondragover = function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        return false;
    };

    dropArea.ondrop = function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        handleFiles(evt.dataTransfer.files);
        return false;
    };

    var img_id;

    function byteToMiB(bytes, accuracy) {
        var ac = Math.pow(10, accuracy);
        return Math.round(bytes / 1048567 * ac) / ac;
    }

    function uploadImage() {
        if (file !== null) {
            if (window.XMLHttpRequest) {
                var xhr = new XMLHttpRequest();
            } else {
                var xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
            progress.attr("style", "display: inline");
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    percent = Math.round((e.loaded * 100) / e.total);
                    progress.attr("style", "display: inline");
                    progressbar.attr("style", "width: " + percent + "%");
                    if (percent < 90) {
                        progressbar.html(byteToMiB(e.loaded, 2) + "MiB von " + byteToMiB(e.total, 2) + "MiB hochgeladen");
                    } else {
                        progress.attr("style", "display: inline");
                        progressbar.html("Erzeugung von Vorschaubildern...");
                        progressbar.attr("style", "width: 100%");
                    }
                }
            };

            xhr.open("post", rating_url2, true);
            xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
            var fd = new FormData();
            // Set appropriate headers
            //xhr.setRequestHeader("Content-Type", "multipart/form-data");
            //xhr.setRequestHeader("description", $(".descr").val() == "" ? "" : $(".descr").serialize().split("=", 2)[1]);
            //xhr.setRequestHeader("send", "");
            //xhr.setRequestHeader("X-File-Name", "uploaded_file");
            //xhr.setRequestHeader("X-File-Size", file.size);
            //xhr.setRequestHeader("X-File-Type", file.type);
            fd.append("uploaded_file", file);
            fd.append("description", $(".descr").val());
            fd.append("category", $("input.img_category").val());
            fd.append("access_key", access_key);
            fd.append("rotation", "");
            // Send the file (doh)
            progress.attr("style", "display: visible");
            xhr.send(fd);

            xhr.onreadystatechange = function() {
                if (xhr.status === 200 && xhr.readyState === 4) {
                    var json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (err) {
                        console.log(xhr.responseText);
                        console.log(err);
                    }
                    if (json === null)
                        return;
                    if (json["logs"] !== undefined)
                        add_log_object(json["logs"]);
                    if (json["data"] !== undefined && json["data"]["html"] !== undefined) {
                        $(".item-send").after(json["data"]["html"]);
                        $(".item-send .descr").val("");
                        $("#drop_area").html(droparea_html);
                        $("#drop_area").attr("onclick", droparea_onclick);
                        progress.attr("style", "display: none");
                        progressbar.attr("style", "width: 0%");
                        $("#drop-image-area .hide_when_unused").attr("style", "");
                        file = null;
                        file_content = "";
                        $('.imagelist a.item-content').Chocolat(chocolat_options);
                    }
                }
            };
        }
    }
}

function sendQuote(is_anonymous, response_to, person) {
    var ele_str;
    if (response_to && response_to != -1)
        ele_str = "#responses_to_" + response_to + " .item-quote-send ";
    else
        ele_str = ".content > .item-quote-send ";
    var data = {
        'person': person == '' ? $(ele_str + "input[name=person]").val() : person,
        'text': $(ele_str + "textarea").val(),
        'response_to': response_to
    };
    if (is_anonymous) {
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data) {
            if (response_to == -1) {
                $(".content > .item-quote-send").after(data["html"]);
                $(".content > .item-quote-send textarea").val("");
                $(".content > .item-quote-send input").val("");
            } else {
                $("#responses_to_" + response_to + " .add_response_container").before(data["html"]);
                $("#responses_to_" + response_to + " .item-quote-send textarea").val("");
            }
        },
        needs: ["html"]
    });
}

function sendRumor(is_anonymous, response_to) {
    var data = {
        'response_to': response_to
    };
    data["text"] = response_to == -1 ? $(".content > .item-rumor-send textarea").val() : $("#responses_to_" + response_to + " .item-rumor-send textarea").val();
    if (is_anonymous) {
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data) {
            if (response_to == -1) {
                $(".content > .item-rumor-send").after(data["html"]);
                $(".content > .item-rumor-send textarea").val("..., dass");
            } else {
                $("#responses_to_" + response_to + " .item-rumor-send").before(data["html"]);
                $("#responses_to_" + response_to + " .item-rumor-send textarea").val("..., dass");
            }
        },
        needs: ["html"]
    });
}

function responseToItem(id, person) {
    var container = $("#responses_to_" + id + " .add_response_container");
    if (container) {
        if (window.item_response_template === undefined) {
            window.item_response_template = Handlebars.compile($("#item-response-template").html());
        }
        if (container.children(".item-send").length == 0) {
            var html = "";
            var ele = $("#" + id);
            if (person != "") { // Send quote item
                html = item_response_template({
                    response_to: id,
                    teacher: person,
                    button_answer_title: "Hinzufügen",
                    button_answer_ano_title: "Anonym hinzufügen"
                });
            } else {
                html = item_response_template({
                    response_to: id,
                    button_answer_title: "Hinzufügen",
                    button_answer_ano_title: "Anonym hinzufügen"
                });
            }
            container.html(html);
        } else {
            container.html("");
        }
    }
}

function editItem(id, args) {
    var container = $("#" + id + "_edit_item_container");
    if (container) {
        if (window.item_edit_template === undefined) {
            window.item_edit_template = Handlebars.compile($("#item-edit-template").html());
        }
        var html = "";
        var obj = {"id": id};
        for (var i = 0; i < args.length; i++) {
            var key = args[i];
            obj[key] = $.trim($("#" + id + "_" + key).html());
        }
        html = item_edit_template(obj);
        container.html(html);
        var submitEle = $("#" + id + "_edit_modal .submit-button");
        $("#" + id + "_edit_modal input").keydown(function(event) {
            if (event.which === 13) {
                submitEle.click();
                event.preventDefault();
            }
        })
        $('#' + id + "_edit_modal").modal('show');
    } else {
        container.html("");
    }
}

function submitEditItem(id, args) {
    var editarr = {};
    for (var i = 0; i < args.length; i++) {
        var key = args[i];
        editarr[key] = $.trim($("#" + id + "_em_" + key).val());
    }
    editarr["id"] = id;
    ajax({
        data: {
            edit: editarr
        },
        success: function(data) {
            if (data["logs"] !== undefined) {
                add_log_object(data["logs"]);
            }
            for (var key in editarr) {
                if (key !== "id") {
                    $("#" + id + "_" + key).html(editarr[key]);
                }
                if (key === "category") {
                    $("#" + id + "_" + key).attr("href", "javascript:search('" + editarr[key] + "')");
                }
            }
            $('#' + id + '_edit_modal').modal('hide');
        }
    });
}

function scrollToTop() {
//$('body').animate({scrollTop:0}, 'slow');
}

//$("input[title!='']").tooltip({placement: 'left'});

function userCommentNotify(id) {
    var action = $("#" + id).hasClass("notified_as_bad") ? "unnotify" : "notify";
    ajax({
        data: {
            action: action,
            id: id
        },
        func: function(data) {
            var msg = data["msg"];
            var id = data["id"];
            if (msg != "notified") {
                $("#" + id).addClass("notified_as_bad");
                $("#" + id + " .notify").attr("title", "Positiv bewerten");
            } else if (msg != "unnotified") {
                $("#" + id).removeClass("notified_as_bad");
                $("#" + id + " .notify").attr("title", "Negativ bewerten");
            }
        },
        needs: ["id", "msg"]
    });
}

function sendUserComment(is_anonymous) {
    var data = {
        'text': $("#textarea").val()
    };
    if (is_anonymous) {
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data) {
            $(".write_comment textarea").val("");
            if (data["html"] !== "")
                $(".write_comment").after(data["html"]);
        }
    });
}

function setResultMode(view_results) {
    $.ajax({
        type: "POST",
        url: ajax_url + "/result_mode",
        data: {
            value: view_results
        },
        success: function() {
            location.reload();
        }
    });
}

function updateTimespans() {
    $(".timediff[time]").each(function() {
        var ele = $(this);
        ele.html(timespanText(getTime() - Number(ele.attr("time"))));
    });
}

function getTime() {
    var now = new Date();
    return Math.round(now.getTime() / 1000) - reference_time_diff_seconds;
}

function timespanText(timediff) {
    var text = "";
    var arr = [
        [1, 60, ["Sekunde", "n", "einer"]],
        [60, 3600, ["Minute", "n", "einer"]],
        [3600, 86400, ["Stunde", "n", "einer"]],
        [86400, 2626560, ["Tag", "en", "einem"]],
        [2626560, 31518720, ["Monat", "en", "einem"]],
        [31518720, 1E10, ["Jahr", "en", "einem"]]
    ];
    for (var i = 0; i < arr.length; i++) {
        var steparr = arr[i];
        if (steparr[1] > timediff) {
            var value = Math.floor(timediff / steparr[0]);
            text = (value === 1 ? steparr[2][2] : value) + " " + (value === 1 ? steparr[2][0] : (steparr[2][0] + steparr[2][1]));
            break;
        }
    }
    return 'Vor ' + text;
}

window.setInterval("updateTimespans()", 5000);

function deleteUserComment(id) {
    var input = prompt("Wollen sie diesen Kommentar wirklich löschen? Dann geben sie bitte einen Grund an: ",
            this.item_delete_cause !== undefined ? this.item_delete_cause : "");
    if (input === null) {
        return;
    }
    if (input.length > 0) {
        this.item_delete_cause = input;
    }
    if (this.item_delete_cause !== undefined) {
        ajax({
            data: {
                "deleteComment": id,
                "cause": this.item_delete_cause
            },
            func: function(data) {
                $("#" + data["id"]).remove();
            },
            needs: ["id"]
        });
    } else {
        deleteUserComment(id);
    }
}

function ajax(args) {
    $this = this;
    if (this.last_time_updated === undefined) {
        this.last_time_updated = (new Date()).getTime();
        this.last_time_updated_loc = getTime();
    }
    var func = function(resp) {
        if (resp === null) {
            args["no_return"](null);
            return;
        }
        if (resp["logs"] !== undefined)
            add_log_object(resp["logs"]);
        if (resp["data"] !== undefined) {
            if (resp["data"]["ext"] !== undefined) {
                $this.last_time_updated = (new Date()).getTime();
                $this.last_time_updated_loc = getTime();
                if (resp["data"]["ext"].length !== 0) {
                    update(resp["data"]["ext"]);
                }
            }
            var ok = true;
            for (var i = 0; i < args["needs"].length; i++) {
                var need_data = resp["data"][args["needs"][i]];
                if (need_data === undefined || need_data === null || need_data === "") {
                    ok = false;
                    args["func"](false);
                    return;
                }
            }
            if (ok)
                args["func"](resp["data"]);
        }
    };
    args = $.extend({
        type: "POST",
        dataType: "json",
        func: function(data) {

        },
        success: func,
        error: function(resp, textStatus) {
            console.error(textStatus, resp.responseText);
            func($.parseJSON(resp.responseText));
            $this.use_ajax_url = true;
        },
        no_return: function() {

        },
        func: function() {

        },
                needs: []
    }, args);
    args["data"] = $.extend({
        url: $(".item-send").length == 0 ? ajax_url : "",
        access_key: access_key,
        last_time_updated: this.last_time_updated_loc,
        ajax: true
    }, args["data"]);
    if (args["data"] !== undefined && typeof(args["data"]) !== "string")
        args["data"] = $.param(args["data"]);
    if (args["only_update"] === true) {
        if (window.auto_update_interval !== undefined
                && Number(this.last_time_updated) + Number(auto_update_interval) < (new Date()).getTime()) {
            $.ajax(args);
        }
    } else {
        $.ajax(args);
    }
}

function update(ext_data) {
    if (ext_data["actions"] !== undefined) {
        if ($(".action_list_container").size() !== 0) {
            $(".action_list_container ul").html(ext_data["actions"] + $(".action_list_container ul").html());
        } else if ($(".action_list_container").size !== 0) {
            $(".action_list_container").html(ext_data["actions"] + $(".action_list_container").html());
        }
    }
    if (ext_data["items"] !== undefined) {
        if ($(".item-send").size() !== 0) {
            $(".item-send").after(ext_data["items"]);
        } else if ($(".action_list_container").size() !== 0) {
            $(".action_list_container").prepend(ext_data["items"]);
        }
    }
}

var weird_counter = 23426;

Handlebars.registerHelper("debug", function(optionalValue) {
    console.log("Current Context");
    console.log("====================");
    console.log(this);

    if (optionalValue) {
        console.log("Value");
        console.log("====================");
        console.log(optionalValue);
    }
});

function add_log_object(object) {
    weird_counter++;
    if (window.log_table_template_hbs === undefined)
        window.log_table_template_hbs = Handlebars.compile($("#log_table_template").html());
    $("#log").append(log_table_template_hbs({
        id: weird_counter,
        data: object
    }));
    var options = {valueNames: ["dbt_num", "dbt_query_text", "dbt_time", "dbt_caller",
            "dbt_caller_caller"], plugins: [['fuzzySearch']]};
    window["database_query_table_" + weird_counter] = new List("database_query_table_" + weird_counter, options);
    options = {valueNames: ["lmt_time", "lmt_time", "lmt_time", "lmt_message",
            "lmt_caller_file", "lmt_caller_line", "lmt_caller_caller", "lmt_caller_caller_file",
            "lmt_caller_caller_line", "lmt_type"], plugins: [['fuzzySearch']]};
    window["log_messages_table_" + weird_counter] = new List("log_messages_table_" + weird_counter, options);
    options = {valueNames: ["tl_message", "tl_time"], plugins: [['fuzzySearch']]};
    window["time_log_" + weird_counter] = new List("time_log_" + weird_counter, options);
}

var time_precision = 3;

Handlebars.registerHelper("s_to_ms", function(value) {
    var precision = Math.pow(10, time_precision);
    return Math.round(value * 1000 * precision) / precision;
});

function deletePoll(id, real_del) {
    if (real_del) {
        $("#" + id).replaceWith('<input type="hidden" name="' + $id + '"/><input type="hidden" name="' + $id + '_action" value="delete"/>');
    } else {
        $("#" + id).remove();
    }
}

function addPoll(type, ele) {
    weird_counter++;
    if (window.edit_poll_item_template === undefined) {
        window.edit_poll_item_template = Handlebars.compile($("#edit_poll_item_template").html());
    }
    $(ele).before(edit_poll_item_template({
        id: weird_counter,
        type: type
    }));
}

function testPasswordInput(idOfInput, idOfResult, idOfSubmit) {
    var ele = $("#" + idOfInput);
    var res = testPassword(ele.val(), idOfResult, idOfSubmit);
    if (res <= 10) {
        ele.attr("class", "error");
    } else if (res > 10 && res < 25) {
        ele.attr("class", "warning");
    } else {
        ele.attr("class", "success");
    }
}

setTimeout(function() {
    $(function() {
        $('#poll_tabs a:first').tab('show');
    });
}, 300);

var user_list;
$("body").ready(function() {
    $("#user_table").ready(function() {
        var options = {valueNames: ["id", "first_name", "last_name", "math_course",
                "math_teacher", "user_mode", "activated", "visible", "comments_moderated",
                "unanswered_ucquestions", "mail_adress", "is_selected", "written_items",
                "ratings", "ratings_written_items_sum"], plugins: [['fuzzySearch']]};
        window.user_table_list = new List("user_table", options);
    });
    $("#not_reviewed_comments_table").ready(function() {
        var options = {valueNames: ["comment_id", "comment_text", "commented_user", "commenting_user", "comment_date"],
            plugins: [['fuzzySearch']]};
        window.not_reviewed_comments_table_list = new List("not_reviewed_comments_table", options);
    });
    $("#quote_stats_table").ready(function() {
        var options = {valueNames: ["qst_gender", "qst_name", "qst_number", "qst_rating"],
            plugins: [['fuzzySearch']]};
        window.quote_stats_table_list = new List("quote_stats_table", options);
    });
    $("#rumor_stats_table").ready(function() {
        var options = {valueNames: ["rst_gender", "rst_name", "rst_number", "rst_rating"],
            plugins: [['fuzzySearch']]};
        window.rumor_stats_table_list = new List("rumor_stats_table", options);
    });
});