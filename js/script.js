
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
    num = rating;
    $("#" + id + "rating stars star").each(function(ele){
        if (num > 0){
            ele.attr("css", "star selected");
        } else {
            ele.attr("css", "star");
        }
        num--;
    })
    $.ajax({
        type: "POST",
        url: rating_url2,
        data: "rating=" + rating,
        onsuccess: function(html){
            $("#" + id + "rating .average .num").html(html);
        }
    });
}

function deleteItem(id){
    if (confirm("Wollen sie diesen Beitrag wirklich löschen?")){
        var func = function(html){
            if (typeof(html) != "string")
                html = html.responseText;
            if (html != ""){
                var arr = html.split("|", 2);
                $("#" + arr[0]).remove();
                $(body).append(arr[1]);
            }
        }
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: "delete=0&id=" + id,
            success: func,
            error: func
        });
    }
}

var is_loading = false;
var sort_str = "";
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
                is_loading = false;
                addLoadedItemsHTML(html);
            },
            error: function(html){
                is_loading = false;
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
    if (!is_loading){
        phrase = _phrase;
        is_loading = true;
        page = 1;
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: $.param({
                'page': "1", 
                'sort': sort_str, 
                'phrase': phrase
            }),
            success: function(html){
                is_loading = false;
                $(".content-item").remove();
                addLoadedItemsHTML(html);
            },
            error: function(html){
                is_loading = false;
                $(".content-item").remove();
                addLoadedItemsHTML(html.responseText);
            }
        });
    }
}

if (window.max_page !== undefined){
    $(window).bottom({
        proximity: 0.2
    }).bind('bottom', function(){
        if(!is_loading) loadItems();
    });
}

function updateActionsSidebar(){
    if (!is_loading){
        var func = function(html){
            if (typeof(html) != "string")
                html = html.responseText;
            if (html == "")
                return;
            $(".sidebar .nav-list .action_list_item:first").before(html);
            $(".sidebar .nav-list .action_list_item").slice(showed_actions).each(function(){
                $(this).remove();
            });
            last_action_id = $(".sidebar .nav-list .action_list_item").attr("id").split("_")[1];
        }
        $.ajax({
            type: "GET",
            url: ajax_url + "/last_actions",
            data: $.param({
                'last_id': last_action_id, 
            }),
            success: func,
            error: func
        });
    }
     
}

var interval = 10000; //in ms

if (has_sidebar)
    setInterval("updateActionsSidebar()", interval);

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
                    progressbar.attr("style", "width: " + percent + "%");
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
            // Send the file (doh)
            progress.attr("style", "visibility: visible");
            xhr.send(fd);

            xhr.onreadystatechange = function(){
                if (xhr.readyState == 4 && xhr.status == 200){
                    $(".item-send").after(xhr.responseText);
                    $(".item-send .descr").val("");
                    $("#drop_area").html(droparea_html);
                    progress.attr("style", "visibility: hidden");
                    progressbar.attr("style", "width: 0%");
                    file = null;
                    file_content = ""; 
                }
            };
        }
    }
}

if ($(".item-quote-send").length != 0){
    function sendQuote(is_anonymous){
        var func = function(html){
            if (typeof(html) != "string")
                html = html.responseText;
            if (html != ""){
                $(".content .item-quote-send").after(html);
                $(".item-quote-send textarea").val("");
                $(".item-quote-send input").val("");
            }
            is_loading = false;
        }
        var data = {
            'person': $(".item-quote-send input").val(),
            'text': $(".item-quote-send textarea").val(),
        };
        if (is_anonymous){
            data['send_anonymous'] = '';
        } else {
            data['send'] = '';
        }
        is_loading = true;
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: $.param(data),
            success: func,
            error: func
        });
    }
}

if ($(".item-rumor-send").length != 0){
    function sendRumor(is_anonymous){
        var func = function(html){
            if (typeof(html) != "string")
                html = html.responseText;
            if (html != ""){
                $(".content .item-rumor-send").after(html);
                $(".item-rumor-send textarea").val("..., dass");
            }
            is_loading = false;
        }
        var data = {
            'text': $(".item-rumor-send textarea").val(),
        };
        if (is_anonymous){
            data['send_anonymous'] = '';
        } else {
            data['send'] = '';
        }
        is_loading = true;
        $.ajax({
            type: "POST",
            url: rating_url2,
            data: $.param(data),
            success: func,
            error: func
        });
    }
}

function scrollToTop(){
//$('body').animate({scrollTop:0}, 'slow');
}

//$("input[title!='']").tooltip({placement: 'left'});

function userCommentNotify(id){
    var func = function(html){
        if (typeof(html) != "string")
            html = html.responseText;
        if (html != ""){
            var arr = html.split("|", 2);
            var id = arr[0];
            if (arr[1] == "notified"){
                $("#" + id).addClass("notified_as_bad");
                $("#" + id + " .notify").html("+");
            } else if (arr[1] == "unnotified"){
                $("#" + id).removeClass("notified_as_bad");
                $("#" + id + " .notify").html("-");
            }
        }
    }
    var action = $("#" + id).hasClass("notified_as_bad") ? "notify" : "unnotify"; 
    $.ajax({
        type: "POST",
        url: rating_url2,
        data: $.param({
            action: action, 
            id: id
        }),
        success: func,
        error: func
    });
}

function sendUserComment(is_anonymous){
    var data = {
        'text': $("#textarea").val(),
    };
    if (is_anonymous){
        data['send_anonymous'] = '';
    } else {
        data['send'] = '';
    }
    is_loading = true;
    $.ajax({
        type: "POST",
        url: "",
        data: $.param(data),
        success: func,
        error: func
    });
}

function setResultMode(view_results){
    $.ajax({
        type: "POST",
        url: ajax_url + "/result_mode",
        data: $.param({value: view_results})
    });
}