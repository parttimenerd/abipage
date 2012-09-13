
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

function rating(id, rating){
    fillStars(id, rating);
    ajax({
        data: $.param({
            rating: rating, 
            id: id
        }),
        func: function(data){
            $("#" + id + "rating .average").replaceWith(data["html"]);
            rated_items.push(id);
        },
        needs: ["html"]
    });
}

function fillStars(id, rating){
    num = rating;
    $("#" + id + "rating .stars .star").each(function(){
        if (num > 0){
            $(this).attr("class", "star selected");
        } else {
            $(this).attr("class", "star");
        }
        num--;
    })
}

function deleteItem(id){
    if (confirm("Wollen sie diesen Beitrag wirklich löschen?")){
        ajax({
            data: {
                "delete" : 0, 
                "id": id
            },
            func: function(data){
                $("#" + data["id"]).remove();
            },
            needs: ["id"]
        });
    }
}

var is_loading = false;
var phrase = "";
var last_item = null;
var chocolat_options = {};
var page = 1;

if (window.rating_url !== undefined){
    var rating_url2 = rating_url;
}

function loadItems(){
    if (page < max_page && is_loading == false){
        is_loading = true;
        page++;
        /*if (chocolat_options != {}){
			last_item = $(".imagelist .item").last();
		} else {
			last_item = $(".content .item").last();
		}*/
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: $.param({
                'page': page, 
                'sort': sort_str, 
                'phrase': phrase
            }),
            success: function(html){
                addLoadedItemsHTML(html);
            },
            error: function(html){
                addLoadedItemsHTML(html.responseText);
            }
        });
    }
}

function addLoadedItemsHTML(html, is_append){
    if (arguments.length == 1){
        is_append = true;
    }
    if (html != ""){
        if (chocolat_options.length > 0){
            if(is_append){
                $('.imagelist').append(html);
            } else {
                $('.imagelist').prepend(html);
            }
            $('.imagelist a.item-content').Chocolat(chocolat_options);
        } else {
            if (is_append){
                $('.content').append(html);
            } else {
                $(".content").prepend(html);
            }
        }
    }
//last_item.focus();
}


jQuery.fn.reverse = [].reverse;

function search(_phrase){        
    phrase = _phrase;
    page = 1;
    ajax({
        type: "GET",
        data: {
            'page': "1",
            'phrase': phrase
        },
        func: function(data){
            $(".content-item").remove();
            addLoadedItemsHTML(data["html"]);
        },
    });
}

if (window.max_page !== undefined){
    $(window).bottom({
        proximity: 0.2
    }).bind('bottom', function(){
        loadItems();
    });
}

function updateActionsSidebar(){
    if ($(".sidebar").css("display") == "hidden")
        return;
    ajax({
        type: "GET",
        url: ajax_url + "/last_actions",
        data: {
            'id': last_action_id
        },
        func: function(data){
            $(".action_list_container .action_list_item:first").before(data["html"]);
            if (data["last_action_id"] !== undefined)
                last_action_id = data["last_action_id"];
        },
        needs: ["html", "last_action_id"]
    });     
}

if ($(".action_list_container").length > 0)
    setInterval("updateActionsSidebar()", auto_update_interval);

/*function loadNew(){
    //var get_items = window.first_item_id !== undefined;
    //var get_actions = window.first_action_id !== undefined;
    //addHTML(element, html_to_add, is_append);
    //if (get_items || get_actions){
    if (window.page !== undefined && !is_loading){
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: $.param({
                'page': 1, 
                'sort': sort_str, 
                'phrase': phrase
            }),
            success: function(html){
                addLoadedItemsHTML(html, false);
            },
            error: function(html){
                addLoadedItemsHTML(html.responseText, false);
            }
        });
    }
    if ($(".sidebar").length != 0 && window.actions_url !== undefined && !is_loading){
        $.ajax({
            url: actions_url,
            success: function(html){
                addActionsHTMLItems(html);
            },
            error: function(html){
                addActionsHTMLItems(html.responseText);
            }
        });
    }
}

setInterval("loadNew()", interval);*/

if ($("#drop_area").length != 0){
    $('.imagelist a.item-content').Chocolat(chocolat_options);
    
    /* based on http://robertnyman.com/2010/12/16/utilizing-the-html5-file-api-to-choose-upload-preview-and-see-progress-for-multiple-files/,
				http://www.tutorials.de/content/1065-echter-ajax-datei-upload.html	*/

    var dropArea = $(".item-send")[0],
    file = null,
    file_content = "",
    droparea_html = "",
    progress = $(".item-send .progress");
    progressbar = $(".item-send .progress .bar");
	
    function traverseFiles (files) {
        var li,
        fileInfo;
		
        file = files[0]
		
		
        if (file.size < 3500000 && (/image/i).test(file.type)){
            droparea_html = $("#drop_area").html();
            $("#drop_area").html("");
            /*
				If the file is an image and the web browser supports FileReader,
				present a preview in the file list
				 
			*/
            if (typeof FileReader !== "undefined") {
                $("#drop_area").html('<img />');
                img = $("#drop_area img");
                reader = new FileReader();
                reader.onload = (function (theImg) {
                    return function (evt) {
                        theImg.attr("src", evt.target.result);
                        $("#file_input").val(file);
                        file_content = evt.target.result;
                        $(".item-send .descr").focus();
                    };
                }(img));
                reader.readAsDataURL(file);
            }
        }
    }
	
    dropArea.ondragenter = function () {
        return false;
    };
	
    dropArea.ondragover = function () {
        return false;
    };
	
    dropArea.ondrop = function (evt) {
        traverseFiles(evt.dataTransfer.files);
        return false;
    };
	
    var img_id;
	
    function byteToMiB(bytes, accuracy){
        var ac = Math.pow(10, accuracy);
        return Math.round(bytes / 1048567 * ac) / ac;
    }
	
    function uploadImage(){
        if(file != null){
            if (window.XMLHttpRequest){
                var xhr = new XMLHttpRequest();
            } else {
                var xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
			
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    percent = Math.round((e.loaded * 100) / e.total); 
                    progressbar.attr("style", "width: " + percent + "%; visibility: visible");
                    if (percent < 100){
                        progressbar.html(byteToMiB(e.loaded, 2) + "MiB von " + byteToMiB(e.total, 2) + "MiB hochgeladen");
                    } else {
                        progressbar.html("Erzeugung von Vorschaubildern...");
                    }
                }
            };
			
            xhr.open("post", rating_url2, true);
            xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
            // Set appropriate headers
            //xhr.setRequestHeader("Content-Type", "multipart/form-data");
            //xhr.setRequestHeader("description", $(".descr").val() == "" ? "" : $(".descr").serialize().split("=", 2)[1]);
            //xhr.setRequestHeader("send", "");
            //xhr.setRequestHeader("X-File-Name", "uploaded_file");
            //xhr.setRequestHeader("X-File-Size", file.size);
            //xhr.setRequestHeader("X-File-Type", file.type);
            var fd = new FormData;
            fd.append("uploaded_file", file);
            fd.append("description", $(".descr").val());
            fd.append("access_key", access_key);
            // Send the file (doh)
            progress.attr("style", "display: visible");
            xhr.send(fd);

            xhr.onreadystatechange = function(){
                if (xhr.status == 200){
//                    var json = JSON.parse(xhr.responseText);
//                    if (json != null)
//                        return;
//                    if (json["logs"] !== undefined)
//                        add_log_object(json["logs"]);
//                    if (json["data"] !== undefined && json["data"]["html"] !== undefined){
                        $(".item-send").after(json["data"]["html"]);
                        $(".item-send .descr").val("");
                        $("#drop_area").html(droparea_html);
                        progress.attr("style", "visibility: hidden");
                        progressbar.attr("style", "width: 0%");
                        file = null;
                        file_content = ""; 
//                    }
                }
            };
        }
    }
}

function sendQuote(is_anonymous, response_to, person){
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
    if (is_anonymous){
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data){
            if (response_to == -1){
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

function sendRumor(is_anonymous, response_to){
    var data = {
        'response_to': response_to
    };
    data["text"] = response_to == -1 ? $(".content > .item-rumor-send textarea").val() : $("#responses_to_" + response_to + " .item-rumor-send textarea").val();
    if (is_anonymous){
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data){
            if (response_to == -1){
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

function responseToItem(id, person){
    var container = $("#responses_to_" + id + " .add_response_container");
    if (container){
        if (window.item_response_template === undefined){
            window.item_response_template = Handlebars.compile($("#item-response-template").html());
        }
        if (container.children(".item-send").length == 0){
            var html = "";
            var ele = $("#" + id);
            if (person != ""){ // Send quote item
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

function scrollToTop(){
//$('body').animate({scrollTop:0}, 'slow');
}

//$("input[title!='']").tooltip({placement: 'left'});

function userCommentNotify(id){
    var action = $("#" + id).hasClass("notified_as_bad") ? "unnotify" : "notify"; 
    ajax({
        data: {
            action: action, 
            id: id
        },
        func: function(data){
            var msg = data["msg"];
            var id = data["id"];
            if (msg != "notified"){
                $("#" + id).addClass("notified_as_bad");
                $("#" + id + " .notify").attr("title", "Positiv bewerten");
            } else if (msg != "unnotified"){
                $("#" + id).removeClass("notified_as_bad");
                $("#" + id + " .notify").attr("title", "Negativ bewerten");
            }
        },
        needs: ["id", "msg"]
    });
}

function sendUserComment(is_anonymous){
    var data = {
        'text': $("#textarea").val()
    };
    if (is_anonymous){
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    ajax({
        data: data,
        func: function(data){
            $(".write_comment textarea").val("");
            if (data["html"] != "")
                $(".write_comment").after(data["html"]);
        }
    });
}

function setResultMode(view_results){
    ajax({
        type: "POST",
        url: ajax_url + "/result_mode",
        data: {
            value: view_results
        }
    });
}

function updateTimespans(){
    $(".timediff").each(function(){
        var ele = $(this);
        ele.html(timespanText(getUTCUnixTime() - ele.attr("time")));
    });
}

function getUTCUnixTime(){
    var now = new Date();
    return Math.round(Date.UTC(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        now.getHours(),
        now.getMinutes()
        ) / 1000);
}

//TODO fix me
function timespanText(timediff){
    var text = "";
    var arr = [
    [1, 60, ["Sekunde", "n", "einer"]],
    [60, 3600, ["Minute", "n", "einer"]],
    [3600, 86400, ["Stunde", "n", "einer"]],
    [86400, 2626560, ["Tag", "en", "einem"]],
    [2626560, 31518720, ["Monat", "en", "einem"]],
    [31518720, 1E10, ["Jahr", "en", "einem"]]
    ];
    for (var i = 0; i < arr.length; i++){
        var steparr = arr[i];
        if (steparr[1] > timediff) {
            var value = Math.floor(timediff / steparr[0]);
            text = (value == 1 ? steparr[2][2] : value) + " " + (value == 1 ? steparr[2][0] : (steparr[2][0] + steparr[2][1]));
            break;
        }
    }
    return 'Vor ' + text;
}

//window.setInterval("updateTimespans()", 5000);

function deleteUserComment(id){
    ajax({
        data: {
            "deleteComment": id
        },
        func: function(data){
            $("#" + data["id"]).remove();
        },
        needs: ["id"]
    });
}

function ajax(args){
    var func = function(resp){
        if (resp == null){
            args["no_return"](null);
            return;
        }
        if (resp["logs"] !== undefined)
            add_log_object(resp["logs"]);
        if (resp["data"] !== undefined){
            var ok = true;
            for (var i = 0; i < args["needs"].length; i++) {
                var need_data = resp["data"][args["needs"][i]];
                if (need_data === undefined || need_data == null || need_data == ""){
                    ok = false;
                    args["func"](false);
                    return;
                }
            }
            if (ok)
                args["func"](resp["data"]);
        }
    }
    args = $.extend({
        type: "POST",
        dataType: "json",
        success: func,
        error: function(resp, textStatus){
            console.error(textStatus);
            func($.parseJSON(resp.responseText));
        },
        no_return: function(){
            
        },
        needs: []
    }, args);
    args["data"] = $.extend({
        access_key: access_key,
        ajax: true
    }, args["data"]);
    if (args["data"] !== undefined && args["data"] === Object(args["data"]))
        args["data"] = $.param(args["data"]);
    $.ajax(args);
}

var weird_counter = 23426;

function add_log_object(object){
    weird_counter++;
    if (window.log_table_template_hbs === undefined)
        window.log_table_template_hbs = Handlebars.compile($("#log_table_template").html());
    $("#log").append(log_table_template_hbs({
        id: weird_counter, 
        data: object
    }));
    $("#" + weird_counter + " .tablesorter").tablesorter();
}

var time_precision = 3;

Handlebars.registerHelper("s_to_ms", function(value){
    var precision = Math.pow(10, time_precision);
    return Math.round(value * 1000 * precision) / precision;
})