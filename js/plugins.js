// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function f(){
    log.history = log.history || [];
    log.history.push(arguments);
    if(this.console) {
        var args = arguments, newarr;
        args.callee = args.callee.caller;
        newarr = [].slice.call(args);
        if (typeof console.log === 'object') log.apply.call(console.log, console, newarr); else console.log.apply(console, newarr);
        }
    };

    // make it safe to use console.log always
    (function(a){
        function b(){}
        for(var c="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),d;!!(d=c.pop());){
        a[d]=a[d]||b;
        }
    })
(function(){
    try{
    console.log();return window.console;
    }catch(a){
    return (window.console={});
    }
}());


// place any jQuery/helper plugins in here, instead of separate, slower script files.

/*
 * 
 * TableSorter 2.0 - Client-side table sorting with ease!
 * Version 2.0.5b
 * @requires jQuery v1.2.3
 * 
 * Copyright (c) 2007 Christian Bach
 * Examples and docs at: http://tablesorter.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * 
 */
/**
 * 
 * @description Create a sortable table with multi-column sorting capabilitys
 * 
 * @example $('table').tablesorter();
 * @desc Create a simple tablesorter interface.
 * 
 * @example $('table').tablesorter({ sortList:[[0,0],[1,0]] });
 * @desc Create a tablesorter interface and sort on the first and secound column column headers.
 * 
 * @example $('table').tablesorter({ headers: { 0: { sorter: false}, 1: {sorter: false} } });
 *          
 * @desc Create a tablesorter interface and disableing the first and second  column headers.
 *      
 * 
 * @example $('table').tablesorter({ headers: { 0: {sorter:"integer"}, 1: {sorter:"currency"} } });
 * 
 * @desc Create a tablesorter interface and set a column parser for the first
 *       and second column.
 * 
 * 
 * @param Object
 *            settings An object literal containing key/value pairs to provide
 *            optional settings.
 * 
 * 
 * @option String cssHeader (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead of the table. Default value:
 *         "header"
 * 
 * @option String cssAsc (optional) A string of the class name to be appended to
 *         sortable tr elements in the thead on a ascending sort. Default value:
 *         "headerSortUp"
 * 
 * @option String cssDesc (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead on a descending sort. Default
 *         value: "headerSortDown"
 * 
 * @option String sortInitialOrder (optional) A string of the inital sorting
 *         order can be asc or desc. Default value: "asc"
 * 
 * @option String sortMultisortKey (optional) A string of the multi-column sort
 *         key. Default value: "shiftKey"
 * 
 * @option String textExtraction (optional) A string of the text-extraction
 *         method to use. For complex html structures inside td cell set this
 *         option to "complex", on large tables the complex option can be slow.
 *         Default value: "simple"
 * 
 * @option Object headers (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 * 
 * @option Array sortList (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 * 
 * @option Array sortForce (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         prepended to user-selected rules. Default value: null
 * 
 * @option Boolean sortLocaleCompare (optional) Boolean flag indicating whatever
 *         to use String.localeCampare method or not. Default set to true.
 * 
 * 
 * @option Array sortAppend (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         appended to user-selected rules. Default value: null
 * 
 * @option Boolean widthFixed (optional) Boolean flag indicating if tablesorter
 *         should apply fixed widths to the table columns. This is usefull when
 *         using the pager companion plugin. This options requires the dimension
 *         jquery plugin. Default value: false
 * 
 * @option Boolean cancelSelection (optional) Boolean flag indicating if
 *         tablesorter should cancel selection of the table headers text.
 *         Default value: true
 * 
 * @option Boolean debug (optional) Boolean flag indicating if tablesorter
 *         should display debuging information usefull for development.
 * 
 * @type jQuery
 * 
 * @name tablesorter
 * 
 * @cat Plugins/Tablesorter
 * 
 * @author Christian Bach/christian.bach@polyester.se
 */

(function ($) {
    $.extend({
        tablesorter: new
        function () {

        var parsers = [],
        widgets = [];

        this.defaults = {
        cssHeader: "header",
        cssAsc: "headerSortUp",
        cssDesc: "headerSortDown",
        cssChildRow: "expand-child",
        sortInitialOrder: "asc",
        sortMultiSortKey: "shiftKey",
        sortForce: null,
        sortAppend: null,
        sortLocaleCompare: true,
        textExtraction: "simple",
        parsers: {}, 
    widgets: [],
        widgetZebra: {
        css: ["even", "odd"]
        }, 
    headers: {}, 
    widthFixed: false,
        cancelSelection: true,
        sortList: [],
        headerList: [],
        dateFormat: "us",
        decimal: '/\.|\,/g',
        onRenderHeader: null,
        selectorHeaders: 'thead th',
        debug: false
        };

        /* debuging utils */

        function benchmark(s, d) {
        log(s + "," + (new Date().getTime() - d.getTime()) + "ms");
        }

        this.benchmark = benchmark;

        function log(s) {
        if (typeof console != "undefined" && typeof console.debug != "undefined") {
        console.log(s);
        } else {
    alert(s);
}
}

/* parsers utils */

function buildParserCache(table, $headers) {

    if (table.config.debug) {
        var parsersDebug = "";
    }

    if (table.tBodies.length == 0) return; // In the case of empty tables
    var rows = table.tBodies[0].rows;

    if (rows[0]) {

        var list = [],
        cells = rows[0].cells,
        l = cells.length;

        for (var i = 0; i < l; i++) {

            var p = false;

            if ($.metadata && ($($headers[i]).metadata() && $($headers[i]).metadata().sorter)) {

                p = getParserById($($headers[i]).metadata().sorter);

            } else if ((table.config.headers[i] && table.config.headers[i].sorter)) {

                p = getParserById(table.config.headers[i].sorter);
            }
            if (!p) {

                p = detectParserForColumn(table, rows, -1, i);
            }

            if (table.config.debug) {
                parsersDebug += "column:" + i + " parser:" + p.id + "\n";
            }

            list.push(p);
        }
    }

    if (table.config.debug) {
        log(parsersDebug);
    }

    return list;
};

function detectParserForColumn(table, rows, rowIndex, cellIndex) {
    var l = parsers.length,
    node = false,
    nodeValue = false,
    keepLooking = true;
    while (nodeValue == '' && keepLooking) {
        rowIndex++;
        if (rows[rowIndex]) {
            node = getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex);
            nodeValue = trimAndGetNodeText(table.config, node);
            if (table.config.debug) {
                log('Checking if value was empty on row:' + rowIndex);
            }
        } else {
            keepLooking = false;
        }
    }
    for (var i = 1; i < l; i++) {
        if (parsers[i].is(nodeValue, table, node)) {
            return parsers[i];
        }
    }
    // 0 is always the generic parser (text)
    return parsers[0];
}

function getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex) {
    return rows[rowIndex].cells[cellIndex];
}

function trimAndGetNodeText(config, node) {
    return $.trim(getElementText(config, node));
}

function getParserById(name) {
    var l = parsers.length;
    for (var i = 0; i < l; i++) {
        if (parsers[i].id.toLowerCase() == name.toLowerCase()) {
            return parsers[i];
        }
    }
    return false;
}

/* utils */

function buildCache(table) {

    if (table.config.debug) {
        var cacheTime = new Date();
    }

    var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0,
    totalCells = (table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length) || 0,
    parsers = table.config.parsers,
    cache = {
        row: [],
        normalized: []
    };

    for (var i = 0; i < totalRows; ++i) {

        /** Add the table data to main data array */
        var c = $(table.tBodies[0].rows[i]),
        cols = [];

        // if this is a child row, add it to the last row's children and
        // continue to the next row
        if (c.hasClass(table.config.cssChildRow)) {
            cache.row[cache.row.length - 1] = cache.row[cache.row.length - 1].add(c);
            // go to the next for loop
            continue;
        }

        cache.row.push(c);

        for (var j = 0; j < totalCells; ++j) {
            cols.push(parsers[j].format(getElementText(table.config, c[0].cells[j]), table, c[0].cells[j]));
        }

        cols.push(cache.normalized.length); // add position for rowCache
        cache.normalized.push(cols);
        cols = null;
    };

    if (table.config.debug) {
        benchmark("Building cache for " + totalRows + " rows:", cacheTime);
    }

    return cache;
};

function getElementText(config, node) {

    var text = "";

    if (!node) return "";

    if (!config.supportsTextContent) config.supportsTextContent = node.textContent || false;

    if (config.textExtraction == "simple") {
        if (config.supportsTextContent) {
            text = node.textContent;
        } else {
            if (node.childNodes[0] && node.childNodes[0].hasChildNodes()) {
                text = node.childNodes[0].innerHTML;
            } else {
                text = node.innerHTML;
            }
        }
    } else {
        if (typeof(config.textExtraction) == "function") {
            text = config.textExtraction(node);
        } else {
            text = $(node).text() == "" ? $(node).val() : $(node).text();
        }
    }
    return text;
}

function appendToTable(table, cache) {

    if (table.config.debug) {
        var appendTime = new Date()
    }

    var c = cache,
    r = c.row,
    n = c.normalized,
    totalRows = n.length,
    checkCell = (n[0].length - 1),
    tableBody = $(table.tBodies[0]),
    rows = [];


    for (var i = 0; i < totalRows; i++) {
        var pos = n[i][checkCell];

        rows.push(r[pos]);

        if (!table.config.appender) {

            //var o = ;
            var l = r[pos].length;
            for (var j = 0; j < l; j++) {
                tableBody[0].appendChild(r[pos][j]);
            }

        // 
        }
    }



    if (table.config.appender) {

        table.config.appender(table, rows);
    }

    rows = null;

    if (table.config.debug) {
        benchmark("Rebuilt table:", appendTime);
    }

    // apply table widgets
    applyWidget(table);

    // trigger sortend
    setTimeout(function () {
        $(table).trigger("sortEnd");
    }, 0);

};

function buildHeaders(table) {

    if (table.config.debug) {
        var time = new Date();
    }

    var meta = ($.metadata) ? true : false;
                
    var header_index = computeTableHeaderCellIndexes(table);

    $tableHeaders = $(table.config.selectorHeaders, table).each(function (index) {

        this.column = header_index[this.parentNode.rowIndex + "-" + this.cellIndex];
        // this.column = index;
        this.order = formatSortingOrder(table.config.sortInitialOrder);
                    
					
        this.count = this.order;

        if (checkHeaderMetadata(this) || checkHeaderOptions(table, index)) this.sortDisabled = true;
        if (checkHeaderOptionsSortingLocked(table, index)) this.order = this.lockedOrder = checkHeaderOptionsSortingLocked(table, index);

        if (!this.sortDisabled) {
            var $th = $(this).addClass(table.config.cssHeader);
            if (table.config.onRenderHeader) table.config.onRenderHeader.apply($th);
        }

        // add cell to headerList
        table.config.headerList[index] = this;
    });

    if (table.config.debug) {
        benchmark("Built headers:", time);
        log($tableHeaders);
    }

    return $tableHeaders;

};

// from:
// http://www.javascripttoolbox.com/lib/table/examples.php
// http://www.javascripttoolbox.com/temp/table_cellindex.html


function computeTableHeaderCellIndexes(t) {
    var matrix = [];
    var lookup = {};
    var thead = t.getElementsByTagName('THEAD')[0];
    var trs = thead.getElementsByTagName('TR');

    for (var i = 0; i < trs.length; i++) {
        var cells = trs[i].cells;
        for (var j = 0; j < cells.length; j++) {
            var c = cells[j];

            var rowIndex = c.parentNode.rowIndex;
            var cellId = rowIndex + "-" + c.cellIndex;
            var rowSpan = c.rowSpan || 1;
            var colSpan = c.colSpan || 1
            var firstAvailCol;
            if (typeof(matrix[rowIndex]) == "undefined") {
                matrix[rowIndex] = [];
            }
            // Find first available column in the first row
            for (var k = 0; k < matrix[rowIndex].length + 1; k++) {
                if (typeof(matrix[rowIndex][k]) == "undefined") {
                    firstAvailCol = k;
                    break;
                }
            }
            lookup[cellId] = firstAvailCol;
            for (var k = rowIndex; k < rowIndex + rowSpan; k++) {
                if (typeof(matrix[k]) == "undefined") {
                    matrix[k] = [];
                }
                var matrixrow = matrix[k];
                for (var l = firstAvailCol; l < firstAvailCol + colSpan; l++) {
                    matrixrow[l] = "x";
                }
            }
        }
    }
    return lookup;
}

function checkCellColSpan(table, rows, row) {
    var arr = [],
    r = table.tHead.rows,
    c = r[row].cells;

    for (var i = 0; i < c.length; i++) {
        var cell = c[i];

        if (cell.colSpan > 1) {
            arr = arr.concat(checkCellColSpan(table, headerArr, row++));
        } else {
            if (table.tHead.length == 1 || (cell.rowSpan > 1 || !r[row + 1])) {
                arr.push(cell);
            }
        // headerArr[row] = (i+row);
        }
    }
    return arr;
};

function checkHeaderMetadata(cell) {
    if (($.metadata) && ($(cell).metadata().sorter === false)) {
        return true;
    };
    return false;
}

function checkHeaderOptions(table, i) {
    if ((table.config.headers[i]) && (table.config.headers[i].sorter === false)) {
        return true;
    };
    return false;
}
			
function checkHeaderOptionsSortingLocked(table, i) {
    if ((table.config.headers[i]) && (table.config.headers[i].lockedOrder)) return table.config.headers[i].lockedOrder;
    return false;
}
			
function applyWidget(table) {
    var c = table.config.widgets;
    var l = c.length;
    for (var i = 0; i < l; i++) {

        getWidgetById(c[i]).format(table);
    }

}

function getWidgetById(name) {
    var l = widgets.length;
    for (var i = 0; i < l; i++) {
        if (widgets[i].id.toLowerCase() == name.toLowerCase()) {
            return widgets[i];
        }
    }
};

function formatSortingOrder(v) {
    if (typeof(v) != "Number") {
        return (v.toLowerCase() == "desc") ? 1 : 0;
    } else {
        return (v == 1) ? 1 : 0;
    }
}

function isValueInArray(v, a) {
    var l = a.length;
    for (var i = 0; i < l; i++) {
        if (a[i][0] == v) {
            return true;
        }
    }
    return false;
}

function setHeadersCss(table, $headers, list, css) {
    // remove all header information
    $headers.removeClass(css[0]).removeClass(css[1]);

    var h = [];
    $headers.each(function (offset) {
        if (!this.sortDisabled) {
            h[this.column] = $(this);
        }
    });

    var l = list.length;
    for (var i = 0; i < l; i++) {
        h[list[i][0]].addClass(css[list[i][1]]);
    }
}

function fixColumnWidth(table, $headers) {
    var c = table.config;
    if (c.widthFixed) {
        var colgroup = $('<colgroup>');
        $("tr:first td", table.tBodies[0]).each(function () {
            colgroup.append($('<col>').css('width', $(this).width()));
        });
        $(table).prepend(colgroup);
    };
}

function updateHeaderSortCount(table, sortList) {
    var c = table.config,
    l = sortList.length;
    for (var i = 0; i < l; i++) {
        var s = sortList[i],
        o = c.headerList[s[0]];
        o.count = s[1];
        o.count++;
    }
}

/* sorting methods */

function multisort(table, sortList, cache) {

    if (table.config.debug) {
        var sortTime = new Date();
    }

    var dynamicExp = "var sortWrapper = function(a,b) {",
    l = sortList.length;

    // TODO: inline functions.
    for (var i = 0; i < l; i++) {

        var c = sortList[i][0];
        var order = sortList[i][1];
        // var s = (getCachedSortType(table.config.parsers,c) == "text") ?
        // ((order == 0) ? "sortText" : "sortTextDesc") : ((order == 0) ?
        // "sortNumeric" : "sortNumericDesc");
        // var s = (table.config.parsers[c].type == "text") ? ((order == 0)
        // ? makeSortText(c) : makeSortTextDesc(c)) : ((order == 0) ?
        // makeSortNumeric(c) : makeSortNumericDesc(c));
        var s = (table.config.parsers[c].type == "text") ? ((order == 0) ? makeSortFunction("text", "asc", c) : makeSortFunction("text", "desc", c)) : ((order == 0) ? makeSortFunction("numeric", "asc", c) : makeSortFunction("numeric", "desc", c));
        var e = "e" + i;

        dynamicExp += "var " + e + " = " + s; // + "(a[" + c + "],b[" + c
        // + "]); ";
        dynamicExp += "if(" + e + ") { return " + e + "; } ";
        dynamicExp += "else { ";

    }

    // if value is the same keep orignal order
    var orgOrderCol = cache.normalized[0].length - 1;
    dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";

    for (var i = 0; i < l; i++) {
        dynamicExp += "}; ";
    }

    dynamicExp += "return 0; ";
    dynamicExp += "}; ";

    if (table.config.debug) {
        benchmark("Evaling expression:" + dynamicExp, new Date());
    }

    eval(dynamicExp);

    cache.normalized.sort(sortWrapper);

    if (table.config.debug) {
        benchmark("Sorting on " + sortList.toString() + " and dir " + order + " time:", sortTime);
    }

    return cache;
};

function makeSortFunction(type, direction, index) {
    var a = "a[" + index + "]",
    b = "b[" + index + "]";
    if (type == 'text' && direction == 'asc') {
        return "(" + a + " == " + b + " ? 0 : (" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : (" + a + " < " + b + ") ? -1 : 1 )));";
    } else if (type == 'text' && direction == 'desc') {
        return "(" + a + " == " + b + " ? 0 : (" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : (" + b + " < " + a + ") ? -1 : 1 )));";
    } else if (type == 'numeric' && direction == 'asc') {
        return "(" + a + " === null && " + b + " === null) ? 0 :(" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : " + a + " - " + b + "));";
    } else if (type == 'numeric' && direction == 'desc') {
        return "(" + a + " === null && " + b + " === null) ? 0 :(" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : " + b + " - " + a + "));";
    }
};

function makeSortText(i) {
    return "((a[" + i + "] < b[" + i + "]) ? -1 : ((a[" + i + "] > b[" + i + "]) ? 1 : 0));";
};

function makeSortTextDesc(i) {
    return "((b[" + i + "] < a[" + i + "]) ? -1 : ((b[" + i + "] > a[" + i + "]) ? 1 : 0));";
};

function makeSortNumeric(i) {
    return "a[" + i + "]-b[" + i + "];";
};

function makeSortNumericDesc(i) {
    return "b[" + i + "]-a[" + i + "];";
};

function sortText(a, b) {
    if (table.config.sortLocaleCompare) return a.localeCompare(b);
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
};

function sortTextDesc(a, b) {
    if (table.config.sortLocaleCompare) return b.localeCompare(a);
    return ((b < a) ? -1 : ((b > a) ? 1 : 0));
};

function sortNumeric(a, b) {
    return a - b;
};

function sortNumericDesc(a, b) {
    return b - a;
};

function getCachedSortType(parsers, i) {
    return parsers[i].type;
}; /* public methods */
this.construct = function (settings) {
    return this.each(function () {
        // if no thead or tbody quit.
        if (!this.tHead || !this.tBodies) return;
        // declare
        var $this, $document, $headers, cache, config, shiftDown = 0,
        sortOrder;
        // new blank config object
        this.config = {};
        // merge and extend.
        config = $.extend(this.config, $.tablesorter.defaults, settings);
        // store common expression for speed
        $this = $(this);
        // save the settings where they read
        $.data(this, "tablesorter", config);
        // build headers
        $headers = buildHeaders(this);
        // try to auto detect column type, and store in tables config
        this.config.parsers = buildParserCache(this, $headers);
        // build the cache for the tbody cells
        cache = buildCache(this);
        // get the css class names, could be done else where.
        var sortCSS = [config.cssDesc, config.cssAsc];
        // fixate columns if the users supplies the fixedWidth option
        fixColumnWidth(this);
        // apply event handling to headers
        // this is to big, perhaps break it out?
        $headers.click(

            function (e) {
                var totalRows = ($this[0].tBodies[0] && $this[0].tBodies[0].rows.length) || 0;
                if (!this.sortDisabled && totalRows > 0) {
                    // Only call sortStart if sorting is
                    // enabled.
                    $this.trigger("sortStart");
                    // store exp, for speed
                    var $cell = $(this);
                    // get current column index
                    var i = this.column;
                    // get current column sort order
                    this.order = this.count++ % 2;
                    // always sort on the locked order.
                    if(this.lockedOrder) this.order = this.lockedOrder;
							
                    // user only whants to sort on one
                    // column
                    if (!e[config.sortMultiSortKey]) {
                        // flush the sort list
                        config.sortList = [];
                        if (config.sortForce != null) {
                            var a = config.sortForce;
                            for (var j = 0; j < a.length; j++) {
                                if (a[j][0] != i) {
                                    config.sortList.push(a[j]);
                                }
                            }
                        }
                        // add column to sort list
                        config.sortList.push([i, this.order]);
                    // multi column sorting
                    } else {
                        // the user has clicked on an all
                        // ready sortet column.
                        if (isValueInArray(i, config.sortList)) {
                            // revers the sorting direction
                            // for all tables.
                            for (var j = 0; j < config.sortList.length; j++) {
                                var s = config.sortList[j],
                                o = config.headerList[s[0]];
                                if (s[0] == i) {
                                    o.count = s[1];
                                    o.count++;
                                    s[1] = o.count % 2;
                                }
                            }
                        } else {
                            // add column to sort list array
                            config.sortList.push([i, this.order]);
                        }
                    };
                    setTimeout(function () {
                        // set css for headers
                        setHeadersCss($this[0], $headers, config.sortList, sortCSS);
                        appendToTable(
                            $this[0], multisort(
                                $this[0], config.sortList, cache)
                            );
                    }, 1);
                    // stop normal event by returning false
                    return false;
                }
            // cancel selection
            }).mousedown(function () {
            if (config.cancelSelection) {
                this.onselectstart = function () {
                    return false
                };
                return false;
            }
        });
        // apply easy methods that trigger binded events
        $this.bind("update", function () {
            var me = this;
            setTimeout(function () {
                // rebuild parsers.
                me.config.parsers = buildParserCache(
                    me, $headers);
                // rebuild the cache map
                cache = buildCache(me);
            }, 1);
        }).bind("updateCell", function (e, cell) {
            var config = this.config;
            // get position from the dom.
            var pos = [(cell.parentNode.rowIndex - 1), cell.cellIndex];
            // update cache
            cache.normalized[pos[0]][pos[1]] = config.parsers[pos[1]].format(
                getElementText(config, cell), cell);
        }).bind("sorton", function (e, list) {
            $(this).trigger("sortStart");
            config.sortList = list;
            // update and store the sortlist
            var sortList = config.sortList;
            // update header count index
            updateHeaderSortCount(this, sortList);
            // set css for headers
            setHeadersCss(this, $headers, sortList, sortCSS);
            // sort the table and append it to the dom
            appendToTable(this, multisort(this, sortList, cache));
        }).bind("appendCache", function () {
            appendToTable(this, cache);
        }).bind("applyWidgetId", function (e, id) {
            getWidgetById(id).format(this);
        }).bind("applyWidgets", function () {
            // apply widgets
            applyWidget(this);
        });
        if ($.metadata && ($(this).metadata() && $(this).metadata().sortlist)) {
            config.sortList = $(this).metadata().sortlist;
        }
        // if user has supplied a sort list to constructor.
        if (config.sortList.length > 0) {
            $this.trigger("sorton", [config.sortList]);
        }
        // apply widgets
        applyWidget(this);
    });
};
this.addParser = function (parser) {
    var l = parsers.length,
    a = true;
    for (var i = 0; i < l; i++) {
        if (parsers[i].id.toLowerCase() == parser.id.toLowerCase()) {
            a = false;
        }
    }
    if (a) {
        parsers.push(parser);
    };
};
this.addWidget = function (widget) {
    widgets.push(widget);
};
this.formatFloat = function (s) {
    var i = parseFloat(s);
    return (isNaN(i)) ? 0 : i;
};
this.formatInt = function (s) {
    var i = parseInt(s);
    return (isNaN(i)) ? 0 : i;
};
this.isDigit = function (s, config) {
    // replace all an wanted chars and match.
    return /^[-+]?\d*$/.test($.trim(s.replace(/[,.']/g, '')));
};
this.clearTableBody = function (table) {
    if ($.browser.msie) {
        function empty() {
            while (this.firstChild)
                this.removeChild(this.firstChild);
        }
        empty.apply(table.tBodies[0]);
    } else {
        table.tBodies[0].innerHTML = "";
    }
};
}
});

// extend plugin scope
$.fn.extend({
    tablesorter: $.tablesorter.construct
});

// make shortcut
var ts = $.tablesorter;

// add default parsers
ts.addParser({
    id: "text",
    is: function (s) {
        return true;
    }, 
    format: function (s) {
        return $.trim(s.toLocaleLowerCase());
    }, 
    type: "text"
});

ts.addParser({
    id: "digit",
    is: function (s, table) {
        var c = table.config;
        return $.tablesorter.isDigit(s, c);
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat(s);
    }, 
    type: "numeric"
});

ts.addParser({
    id: "currency",
    is: function (s) {
        return /^[£$€?.]/.test(s);
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat(s.replace(new RegExp(/[£$€]/g), ""));
    }, 
    type: "numeric"
});

ts.addParser({
    id: "ipAddress",
    is: function (s) {
        return /^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);
    }, 
    format: function (s) {
        var a = s.split("."),
        r = "",
        l = a.length;
        for (var i = 0; i < l; i++) {
            var item = a[i];
            if (item.length == 2) {
                r += "0" + item;
            } else {
                r += item;
            }
        }
        return $.tablesorter.formatFloat(r);
    }, 
    type: "numeric"
});

ts.addParser({
    id: "url",
    is: function (s) {
        return /^(https?|ftp|file):\/\/$/.test(s);
    }, 
    format: function (s) {
        return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//), ''));
    }, 
    type: "text"
});

ts.addParser({
    id: "isoDate",
    is: function (s) {
        return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat((s != "") ? new Date(s.replace(
            new RegExp(/-/g), "/")).getTime() : "0");
    }, 
    type: "numeric"
});

ts.addParser({
    id: "percent",
    is: function (s) {
        return /\%$/.test($.trim(s));
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g), ""));
    }, 
    type: "numeric"
});

ts.addParser({
    id: "usLongDate",
    is: function (s) {
        return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat(new Date(s).getTime());
    }, 
    type: "numeric"
});

ts.addParser({
    id: "shortDate",
    is: function (s) {
        return /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);
    }, 
    format: function (s, table) {
        var c = table.config;
        s = s.replace(/\-/g, "/");
        if (c.dateFormat == "us") {
            // reformat the string in ISO format
            s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$1/$2");
        } else if (c.dateFormat == "uk") {
            // reformat the string in ISO format
            s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
        } else if (c.dateFormat == "dd/mm/yy" || c.dateFormat == "dd-mm-yy") {
            s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, "$1/$2/$3");
        }
        return $.tablesorter.formatFloat(new Date(s).getTime());
    }, 
    type: "numeric"
});
ts.addParser({
    id: "time",
    is: function (s) {
        return /^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);
    }, 
    format: function (s) {
        return $.tablesorter.formatFloat(new Date("2000/01/01 " + s).getTime());
    }, 
    type: "numeric"
});
ts.addParser({
    id: "metadata",
    is: function (s) {
        return false;
    }, 
    format: function (s, table, cell) {
        var c = table.config,
        p = (!c.parserMetadataName) ? 'sortValue' : c.parserMetadataName;
        return $(cell).metadata()[p];
    }, 
    type: "numeric"
});
// add default widgets
ts.addWidget({
    id: "zebra",
    format: function (table) {
        if (table.config.debug) {
            var time = new Date();
        }
        var $tr, row = -1,
        odd;
        // loop through the visible rows
        $("tr:visible", table.tBodies[0]).each(function (i) {
            $tr = $(this);
            // style children rows the same way the parent
            // row was styled
            if (!$tr.hasClass(table.config.cssChildRow)) row++;
            odd = (row % 2 == 0);
            $tr.removeClass(
                table.config.widgetZebra.css[odd ? 0 : 1]).addClass(
                table.config.widgetZebra.css[odd ? 1 : 0])
        });
        if (table.config.debug) {
            $.tablesorter.benchmark("Applying Zebra widget", time);
        }
    }
});
})(jQuery);

/*
 Inspired by the lightbox plugin adapted to jquery by Leandro Vieira Pinho (http://leandrovieira.com)
 
 @author  : Nicolas Turlais : nicolas-at-insipi.de
 @version : V0.3 - January 2012
 @license : Licensed under CCAttribution-ShareAlike
 @website : http://chocolat.insipi.de
 
*/
(function($) {
    images = new Array();
    var calls = 0;
    $.fn.Chocolat = function(settings) {
        settings = $.extend({
            container:					$('body'),
            displayAsALink:				false,
            linkImages:					true,
            linksContainer:				'Choco_links_container',				
            overlayOpacity:				0.9,
            overlayColor:				'#fff',
            fadeInOverlayduration:		500,
            fadeInImageduration:		500,
            fadeOutImageduration:		500,
            vache:						true,					
            separator1:					' | ',						
            separator2:					'/',						
            leftImg:					'images/left.gif',	
            rightImg:					'images/right.gif',	
            closeImg:					'images/close.gif',		
            loadingImg:					'images/loading.gif',		
            currentImage:				0,						
            setIndex:					0,
            setTitle:					'',
            lastImage:					0
        },settings);
		
        calls++;
        settings.setIndex = calls;
        images[settings.setIndex] = new Array();
		
        //images:
        this.each(function(index){
			
            if(index == 0 && settings.linkImages){
                if(settings.setTitle == ''){
                    settings.setTitle = isSet($(this).attr('rel'), ' ');
                }
            }
            $(this).each(function() {
                images[settings.setIndex]['displayAsALink'] = settings.displayAsALink;
                images[settings.setIndex][index] = new Array();
                images[settings.setIndex][index]['adress'] = isSet($(this).attr('href'), ' ');
                images[settings.setIndex][index]['caption'] = isSet($(this).attr('title'), ' ');
                if(!settings.displayAsALink){
                    $(this).unbind('click').bind('click', {
                        id: settings.setIndex, 
                        nom : settings.setTitle, 
                        i : index
                    }, _initialise);
                }
            })

        });
		
        //setIndex:
        for(var i = 0; i < images[settings.setIndex].length; i++)
        {
            if(images[settings.setIndex]['displayAsALink']){
                if($('#'+settings.linksContainer).size() == 0){
                    this.filter(":first").before('<ul id="'+settings.linksContainer+'"></ul>');
                }
                $('#'+settings.linksContainer).append('<li><a href="#" id="Choco_numsetIndex_'+settings.setIndex+'" class="Choco_link">'+settings.setTitle+'</a></li>');
                e = this.parent();
                $(this).remove();
                if($.trim(e.html()) == ""){//If parent empty : remove it
                    e.remove();
                }
                return $('#Choco_numsetIndex_'+settings.setIndex).unbind('click').bind('click', {
                    id: settings.setIndex, 
                    nom : settings.setTitle, 
                    i : settings.currentImage
                    }, _initialise);
            }
        }
		
        function _initialise(event) {
			
            settings.currentImage = event.data.i;
            settings.setIndex = event.data.id;
            settings.setTitle = event.data.nom;
            settings.lastImage = images[settings.setIndex].length - 1;
            showChocolat();
            return false;
        }
        function _interface(){
            //html
            clear();
            settings.container.append('<div id="Choco_overlay"></div><div id="Choco_content"><div id="Choco_close"></div><div id="Choco_loading"></div><div id="Choco_container_photo"><img id="Choco_bigImage" src="" /></div><div id="Choco_container_description"><span id="Choco_container_title"></span><span id="Choco_container_via"></span></div><div id="Choco_left_arrow" class="Choco_arrows"></div><div id="Choco_right_arrow" class="Choco_arrows"></div></div>');	
            $('#Choco_left_arrow').css('background-image', 'url('+settings.leftImg+')');  
            $('#Choco_right_arrow').css('background-image', 'url('+settings.rightImg+')');  
            $('#Choco_close').css('background-image', 'url('+settings.closeImg+')'); 
            $('#Choco_loading').css('background-image', 'url('+settings.loadingImg+')'); 
            if(settings.container.get(0).nodeName.toLowerCase() !== 'body'){
                settings.container.css({
                    'position':'relative',
                    'overflow':'hidden',
                    'line-height':'normal'
                });//yes, yes
                $('#Choco_content').css('position','relative');
                $('#Choco_overlay').css('position', 'absolute');
            }
            //events
            $(document).unbind('keydown').bind('keydown', function(e){
                switch(e.keyCode){
                    case 37:
                        changePageChocolat(-1);
                        break;
                    case 39:
                        changePageChocolat(1);
                        break;
                    case 27:
                        close();
                        break;
                };
            });
            if(settings.vache){
                $('#Choco_overlay').click(function(){
                    close();
                    return false;
                });
            }
            $('#Choco_left_arrow').unbind('click').bind('click', function(){
                changePageChocolat(-1);
                return false;
            });
            $('#Choco_right_arrow').unbind('click').bind('click', function(){
                changePageChocolat(1);
                return false;
            });
            $('#Choco_close').unbind('click').bind('click', function(){
                close();
                return false;
            });
            $(window).resize(function() {
                load(settings.currentImage,true);
            });
	
        }
        function showChocolat(){	
            _interface();
            load(settings.currentImage, false);
            $('#Choco_overlay').css({
                'background-color' : settings.overlayColor, 
                'opacity' : settings.overlayOpacity
                }).fadeIn(settings.fadeInOverlayduration);
            $('#Choco_content').fadeIn(settings.fadeInImageduration,function(){});
			
        }
        function load(image,resize){
            settings.currentImage = image;
            $('#Choco_loading').fadeIn(settings.fadeInImageduration);
            var imgPreloader = new Image();
            imgPreloader.onload = function(){
                $('#Choco_bigImage').attr('src',images[settings.setIndex][settings.currentImage]['adress']);
                var ajustees = iWantThePerfectImageSize(imgPreloader.height,imgPreloader.width);
                ChoColat(ajustees['hauteur'],ajustees['largeur'],resize);
                $('#Choco_loading').stop().fadeOut(settings.fadeOutImageduration);
            };
            imgPreloader.src = images[settings.setIndex][settings.currentImage]['adress'];
            preload();
            upadteDescription();
        }
        function changePageChocolat(signe){
            if(!settings.linkImages)
            {
                return false;
            }
            else if(settings.currentImage == 0 && signe == -1)
            {
                return false;
            }
            else if(settings.currentImage == settings.lastImage && signe == 1){
                return false;
            }
            else{

                $('#Choco_container_description').fadeTo(settings.fadeOutImageduration,0);
                $('#Choco_bigImage').fadeTo(settings.fadeOutImageduration, 0, function(){
                    load(settings.currentImage + parseInt(signe), false);
                });

            }
        }
        function ChoColat(hauteur_image,largeur_image,resize){

            if(resize){
                $('#Choco_container_photo, #Choco_content, #Choco_bigImage').stop(true,false).css({
                    'overflow':'visible'
                });
                $('#Choco_bigImage').animate({
                    'height' : hauteur_image+'px',
                    'width' : largeur_image+'px',
                },settings.fadeInImageduration);
            }
            $('#Choco_container_photo').animate({
                'height' : hauteur_image,
                'width' : largeur_image
            },settings.fadeInImageduration);
            $('#Choco_content').animate({
                'height' : hauteur_image,
                'width' : largeur_image,
                'marginLeft' : -largeur_image/2,
                'marginTop' : -(hauteur_image)/2
            },settings.fadeInImageduration, 'swing', function(){
                $('#Choco_bigImage').fadeTo(settings.fadeInImageduration, 1).height(hauteur_image).width(largeur_image).fadeIn(settings.fadeInImageduration);
                if(!resize)
                {
                    arrowsManaging();
                    $('#Choco_container_description').fadeTo(settings.fadeInImageduration,1);
                    $('#Choco_close').fadeIn(settings.fadeInImageduration);
                }
            }).
            css('overflow', 'visible');
        }
        function arrowsManaging(){
            if(settings.linkImages){
                var what = new Array('Choco_right_arrow','Choco_left_arrow');
                for(var i=0; i < what.length; i++){
                    hide = false;
                    if(what[i] == 'Choco_right_arrow' && settings.currentImage == settings.lastImage){
                        hide = true;
                        $('#'+what[i]).fadeOut(300);
                    }
                    else if(what[i] == 'Choco_left_arrow' && settings.currentImage == 0){
                        hide = true;
                        $('#'+what[i]).fadeOut(300);
                    }
                    if(!hide){
                        $('#'+what[i]).fadeIn(settings.fadeOutImageduration);
                    }
                }
            }
        }
        function preload(){
            if(settings.currentImage !== settings.lastImage){
                i = new Image;
                z = settings.currentImage + 1;
                i.src = images[settings.setIndex][z]['adress'];
            }
        }
        function upadteDescription(){
            var current = settings.currentImage + 1;
            var last = settings.lastImage + 1;
            $('#Choco_container_title').html(images[settings.setIndex][settings.currentImage]['caption']);
            $('#Choco_container_via').html(settings.setTitle+settings.separator1+current +settings.separator2+last);
        }
        function isSet(variable,defaultValue){
            if (variable === undefined) {
                return defaultValue;
            }
            else{
                return variable;
            }
        }
        function iWantThePerfectImageSize(himg,limg){
            //28% = 14% + 14% margin
            //51px height of description + close
            var lblock = limg + (limg*28/100);
            var hblock = himg + 51;
            var k = limg/himg;
            var kk = himg/limg;
            if(settings.container.get(0).nodeName.toLowerCase() == 'body'){
                windowHeight = $(window).height();
                windowWidth = $(window).width();
            }
            else{
                windowHeight = settings.container.height();
                windowWidth = settings.container.width();
            }
            notFitting = true;
            while (notFitting){
                var lblock = limg + (limg*28/100);
                var hblock = himg + 51;
                if(lblock > windowWidth){
                    limg = windowWidth*100/128;
						
                    himg = kk * limg;
                }else if(hblock > windowHeight){
                    himg = (windowHeight - 51);
                    limg = k * himg;
                }else{
                    notFitting = false;
                };
            };
            return {
                largeur:limg,
                hauteur:himg
            };

        }
        function clear(){
            $('#Choco_overlay').remove()
            $('#Choco_content').remove()
        }
        function close(){
            $('#Choco_overlay').fadeOut(900, function(){
                $('#Choco_overlay').remove()
                });
            $('#Choco_content').fadeOut(500, function(){
                $('#Choco_content').remove()
                });
            settings.currentImage = 0;
        }
	
    };
})(jQuery);

/**
 *
 * Color picker
 * Author: Stefan Petre www.eyecon.ro
 * 
 * Dual licensed under the MIT and GPL licenses
 * 
 */
//(function ($) {
    var ColorPicker = function () {
        var
        ids = {},
        inAction,
        charMin = 65,
        visible,
        tpl = '<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>',
        defaults = {
            eventName: 'click',
            onShow: function () {},
            onBeforeShow: function(){},
            onHide: function () {},
            onChange: function () {},
            onSubmit: function () {},
            color: 'ff0000',
            livePreview: true,
            flat: false
        },
        fillRGBFields = function  (hsb, cal) {
            var rgb = HSBToRGB(hsb);
            $(cal).data('colorpicker').fields
            .eq(1).val(rgb.r).end()
            .eq(2).val(rgb.g).end()
            .eq(3).val(rgb.b).end();
        },
        fillHSBFields = function  (hsb, cal) {
            $(cal).data('colorpicker').fields
            .eq(4).val(hsb.h).end()
            .eq(5).val(hsb.s).end()
            .eq(6).val(hsb.b).end();
        },
        fillHexFields = function (hsb, cal) {
            $(cal).data('colorpicker').fields
            .eq(0).val(HSBToHex(hsb)).end();
        },
        setSelector = function (hsb, cal) {
            $(cal).data('colorpicker').selector.css('backgroundColor', '#' + HSBToHex({
                h: hsb.h, 
                s: 100, 
                b: 100
            }));
            $(cal).data('colorpicker').selectorIndic.css({
                left: parseInt(150 * hsb.s/100, 10),
                top: parseInt(150 * (100-hsb.b)/100, 10)
            });
        },
        setHue = function (hsb, cal) {
            $(cal).data('colorpicker').hue.css('top', parseInt(150 - 150 * hsb.h/360, 10));
        },
        setCurrentColor = function (hsb, cal) {
            $(cal).data('colorpicker').currentColor.css('backgroundColor', '#' + HSBToHex(hsb));
        },
        setNewColor = function (hsb, cal) {
            $(cal).data('colorpicker').newColor.css('backgroundColor', '#' + HSBToHex(hsb));
        },
        keyDown = function (ev) {
            var pressedKey = ev.charCode || ev.keyCode || -1;
            if ((pressedKey > charMin && pressedKey <= 90) || pressedKey == 32) {
                return false;
            }
            var cal = $(this).parent().parent();
            if (cal.data('colorpicker').livePreview === true) {
                change.apply(this);
            }
        },
        change = function (ev) {
            var cal = $(this).parent().parent(), col;
            if (this.parentNode.className.indexOf('_hex') > 0) {
                cal.data('colorpicker').color = col = HexToHSB(fixHex(this.value));
            } else if (this.parentNode.className.indexOf('_hsb') > 0) {
                cal.data('colorpicker').color = col = fixHSB({
                    h: parseInt(cal.data('colorpicker').fields.eq(4).val(), 10),
                    s: parseInt(cal.data('colorpicker').fields.eq(5).val(), 10),
                    b: parseInt(cal.data('colorpicker').fields.eq(6).val(), 10)
                });
            } else {
                cal.data('colorpicker').color = col = RGBToHSB(fixRGB({
                    r: parseInt(cal.data('colorpicker').fields.eq(1).val(), 10),
                    g: parseInt(cal.data('colorpicker').fields.eq(2).val(), 10),
                    b: parseInt(cal.data('colorpicker').fields.eq(3).val(), 10)
                }));
            }
            if (ev) {
                fillRGBFields(col, cal.get(0));
                fillHexFields(col, cal.get(0));
                fillHSBFields(col, cal.get(0));
            }
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
            cal.data('colorpicker').onChange.apply(cal, [col, HSBToHex(col), HSBToRGB(col)]);
        },
        blur = function (ev) {
            var cal = $(this).parent().parent();
            cal.data('colorpicker').fields.parent().removeClass('colorpicker_focus');
        },
        focus = function () {
            charMin = this.parentNode.className.indexOf('_hex') > 0 ? 70 : 65;
            $(this).parent().parent().data('colorpicker').fields.parent().removeClass('colorpicker_focus');
            $(this).parent().addClass('colorpicker_focus');
        },
        downIncrement = function (ev) {
            var field = $(this).parent().find('input').focus();
            var current = {
                el: $(this).parent().addClass('colorpicker_slider'),
                max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360 : (this.parentNode.className.indexOf('_hsb') > 0 ? 100 : 255),
                y: ev.pageY,
                field: field,
                val: parseInt(field.val(), 10),
                preview: $(this).parent().parent().data('colorpicker').livePreview					
            };
            $(document).bind('mouseup', current, upIncrement);
            $(document).bind('mousemove', current, moveIncrement);
        },
        moveIncrement = function (ev) {
            ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
            if (ev.data.preview) {
                change.apply(ev.data.field.get(0), [true]);
            }
            return false;
        },
        upIncrement = function (ev) {
            change.apply(ev.data.field.get(0), [true]);
            ev.data.el.removeClass('colorpicker_slider').find('input').focus();
            $(document).unbind('mouseup', upIncrement);
            $(document).unbind('mousemove', moveIncrement);
            return false;
        },
        downHue = function (ev) {
            var current = {
                cal: $(this).parent(),
                y: $(this).offset().top
            };
            current.preview = current.cal.data('colorpicker').livePreview;
            $(document).bind('mouseup', current, upHue);
            $(document).bind('mousemove', current, moveHue);
        },
        moveHue = function (ev) {
            change.apply(
                ev.data.cal.data('colorpicker')
                .fields
                .eq(4)
                .val(parseInt(360*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.y))))/150, 10))
                .get(0),
                [ev.data.preview]
                );
            return false;
        },
        upHue = function (ev) {
            fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
            $(document).unbind('mouseup', upHue);
            $(document).unbind('mousemove', moveHue);
            return false;
        },
        downSelector = function (ev) {
            var current = {
                cal: $(this).parent(),
                pos: $(this).offset()
            };
            current.preview = current.cal.data('colorpicker').livePreview;
            $(document).bind('mouseup', current, upSelector);
            $(document).bind('mousemove', current, moveSelector);
        },
        moveSelector = function (ev) {
            change.apply(
                ev.data.cal.data('colorpicker')
                .fields
                .eq(6)
                .val(parseInt(100*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.pos.top))))/150, 10))
                .end()
                .eq(5)
                .val(parseInt(100*(Math.max(0,Math.min(150,(ev.pageX - ev.data.pos.left))))/150, 10))
                .get(0),
                [ev.data.preview]
                );
            return false;
        },
        upSelector = function (ev) {
            fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
            $(document).unbind('mouseup', upSelector);
            $(document).unbind('mousemove', moveSelector);
            return false;
        },
        enterSubmit = function (ev) {
            $(this).addClass('colorpicker_focus');
        },
        leaveSubmit = function (ev) {
            $(this).removeClass('colorpicker_focus');
        },
        clickSubmit = function (ev) {
            var cal = $(this).parent();
            var col = cal.data('colorpicker').color;
            cal.data('colorpicker').origColor = col;
            setCurrentColor(col, cal.get(0));
            cal.data('colorpicker').onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data('colorpicker').el);
        },
        show = function (ev) {
            var cal = $('#' + $(this).data('colorpickerId'));
            cal.data('colorpicker').onBeforeShow.apply(this, [cal.get(0)]);
            var pos = $(this).offset();
            var viewPort = getViewport();
            var top = pos.top + this.offsetHeight;
            var left = pos.left;
            if (top + 176 > viewPort.t + viewPort.h) {
                top -= this.offsetHeight + 176;
            }
            if (left + 356 > viewPort.l + viewPort.w) {
                left -= 356;
            }
            cal.css({
                left: left + 'px', 
                top: top + 'px'
                });
            if (cal.data('colorpicker').onShow.apply(this, [cal.get(0)]) != false) {
                cal.show();
            }
            $(document).bind('mousedown', {
                cal: cal
            }, hide);
            return false;
        },
        hide = function (ev) {
            if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
                if (ev.data.cal.data('colorpicker').onHide.apply(this, [ev.data.cal.get(0)]) != false) {
                    ev.data.cal.hide();
                }
                $(document).unbind('mousedown', hide);
            }
        },
        isChildOf = function(parentEl, el, container) {
            if (parentEl == el) {
                return true;
            }
            if (parentEl.contains) {
                return parentEl.contains(el);
            }
            if ( parentEl.compareDocumentPosition ) {
                return !!(parentEl.compareDocumentPosition(el) & 16);
            }
            var prEl = el.parentNode;
            while(prEl && prEl != container) {
                if (prEl == parentEl)
                    return true;
                prEl = prEl.parentNode;
            }
            return false;
        },
        getViewport = function () {
            var m = document.compatMode == 'CSS1Compat';
            return {
                l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
                t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
                w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
                h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
            };
        },
        fixHSB = function (hsb) {
            return {
                h: Math.min(360, Math.max(0, hsb.h)),
                s: Math.min(100, Math.max(0, hsb.s)),
                b: Math.min(100, Math.max(0, hsb.b))
            };
        }, 
        fixRGB = function (rgb) {
            return {
                r: Math.min(255, Math.max(0, rgb.r)),
                g: Math.min(255, Math.max(0, rgb.g)),
                b: Math.min(255, Math.max(0, rgb.b))
            };
        },
        fixHex = function (hex) {
            var len = 6 - hex.length;
            if (len > 0) {
                var o = [];
                for (var i=0; i<len; i++) {
                    o.push('0');
                }
                o.push(hex);
                hex = o.join('');
            }
            return hex;
        }, 
        HexToRGB = function (hex) {
            var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
            return {
                r: hex >> 16, 
                g: (hex & 0x00FF00) >> 8, 
                b: (hex & 0x0000FF)
                };
        },
        HexToHSB = function (hex) {
            return RGBToHSB(HexToRGB(hex));
        },
        RGBToHSB = function (rgb) {
            var hsb = {
                h: 0,
                s: 0,
                b: 0
            };
            var min = Math.min(rgb.r, rgb.g, rgb.b);
            var max = Math.max(rgb.r, rgb.g, rgb.b);
            var delta = max - min;
            hsb.b = max;
            if (max != 0) {
					
            }
            hsb.s = max != 0 ? 255 * delta / max : 0;
            if (hsb.s != 0) {
                if (rgb.r == max) {
                    hsb.h = (rgb.g - rgb.b) / delta;
                } else if (rgb.g == max) {
                    hsb.h = 2 + (rgb.b - rgb.r) / delta;
                } else {
                    hsb.h = 4 + (rgb.r - rgb.g) / delta;
                }
            } else {
                hsb.h = -1;
            }
            hsb.h *= 60;
            if (hsb.h < 0) {
                hsb.h += 360;
            }
            hsb.s *= 100/255;
            hsb.b *= 100/255;
            return hsb;
        },
        HSBToRGB = function (hsb) {
            var rgb = {};
            var h = Math.round(hsb.h);
            var s = Math.round(hsb.s*255/100);
            var v = Math.round(hsb.b*255/100);
            if(s == 0) {
                rgb.r = rgb.g = rgb.b = v;
            } else {
                var t1 = v;
                var t2 = (255-s)*v/255;
                var t3 = (t1-t2)*(h%60)/60;
                if(h==360) h = 0;
                if(h<60) {
                    rgb.r=t1;
                    rgb.b=t2;
                    rgb.g=t2+t3
                    }
                else if(h<120) {
                    rgb.g=t1;
                    rgb.b=t2;
                    rgb.r=t1-t3
                    }
                else if(h<180) {
                    rgb.g=t1;
                    rgb.r=t2;
                    rgb.b=t2+t3
                    }
                else if(h<240) {
                    rgb.b=t1;
                    rgb.r=t2;
                    rgb.g=t1-t3
                    }
                else if(h<300) {
                    rgb.b=t1;
                    rgb.g=t2;
                    rgb.r=t2+t3
                    }
                else if(h<360) {
                    rgb.r=t1;
                    rgb.g=t2;
                    rgb.b=t1-t3
                    }
                else {
                    rgb.r=0;
                    rgb.g=0;
                    rgb.b=0
                    }
            }
            return {
                r:Math.round(rgb.r), 
                g:Math.round(rgb.g), 
                b:Math.round(rgb.b)
                };
        },
        RGBToHex = function (rgb) {
            var hex = [
            rgb.r.toString(16),
            rgb.g.toString(16),
            rgb.b.toString(16)
            ];
            $.each(hex, function (nr, val) {
                if (val.length == 1) {
                    hex[nr] = '0' + val;
                }
            });
            return hex.join('');
        },
        HSBToHex = function (hsb) {
            return RGBToHex(HSBToRGB(hsb));
        },
        restoreOriginal = function () {
            var cal = $(this).parent();
            var col = cal.data('colorpicker').origColor;
            cal.data('colorpicker').color = col;
            fillRGBFields(col, cal.get(0));
            fillHexFields(col, cal.get(0));
            fillHSBFields(col, cal.get(0));
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
        };
        return {
            init: function (opt) {
                opt = $.extend({}, defaults, opt||{});
                if (typeof opt.color == 'string') {
                    opt.color = HexToHSB(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = RGBToHSB(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }
                return this.each(function () {
                    if (!$(this).data('colorpickerId')) {
                        var options = $.extend({}, opt);
                        options.origColor = opt.color;
                        var id = 'collorpicker_' + parseInt(Math.random() * 1000);
                        $(this).data('colorpickerId', id);
                        var cal = $(tpl).attr('id', id);
                        if (options.flat) {
                            cal.appendTo(this).show();
                        } else {
                            cal.appendTo(document.body);
                        }
                        options.fields = cal
                        .find('input')
                        .bind('keyup', keyDown)
                        .bind('change', change)
                        .bind('blur', blur)
                        .bind('focus', focus);
                        cal
                        .find('span').bind('mousedown', downIncrement).end()
                        .find('>div.colorpicker_current_color').bind('click', restoreOriginal);
                        options.selector = cal.find('div.colorpicker_color').bind('mousedown', downSelector);
                        options.selectorIndic = options.selector.find('div div');
                        options.el = this;
                        options.hue = cal.find('div.colorpicker_hue div');
                        cal.find('div.colorpicker_hue').bind('mousedown', downHue);
                        options.newColor = cal.find('div.colorpicker_new_color');
                        options.currentColor = cal.find('div.colorpicker_current_color');
                        cal.data('colorpicker', options);
                        cal.find('div.colorpicker_submit')
                        .bind('mouseenter', enterSubmit)
                        .bind('mouseleave', leaveSubmit)
                        .bind('click', clickSubmit);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        if (options.flat) {
                            cal.css({
                                position: 'relative',
                                display: 'block'
                            });
                        } else {
                            $(this).bind(options.eventName, show);
                        }
                    }
                });
            },
            showPicker: function() {
                return this.each( function () {
                    if ($(this).data('colorpickerId')) {
                        show.apply(this);
                    }
                });
            },
            hidePicker: function() {
                return this.each( function () {
                    if ($(this).data('colorpickerId')) {
                        $('#' + $(this).data('colorpickerId')).hide();
                    }
                });
            },
            setColor: function(col) {
                if (typeof col == 'string') {
                    col = HexToHSB(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = RGBToHSB(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function(){
                    if ($(this).data('colorpickerId')) {
                        var cal = $('#' + $(this).data('colorpickerId'));
                        cal.data('colorpicker').color = col;
                        cal.data('colorpicker').origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));
                        setCurrentColor(col, cal.get(0));
                        setNewColor(col, cal.get(0));
                    }
                });
            }
        };
    }();
    $.fn.extend({
        ColorPicker: ColorPicker.init,
        ColorPickerHide: ColorPicker.hidePicker,
        ColorPickerShow: ColorPicker.showPicker,
        ColorPickerSetColor: ColorPicker.setColor
    });
//})(jQuery)

/**
 * jQuery.bottom
 * Dual licensed under MIT and GPL.
 * Date: 2010-04-25
 *
 * @description Trigger the bottom event when the user has scrolled to the bottom of an element
 * @author Jim Yi
 * @version 1.0
 *
 * @id jQuery.fn.bottom
 * @param {Object} settings Hash of settings.
 * @return {jQuery} Returns the same jQuery object for chaining.
 *
 */
    (function($){
        $.fn.bottom = function(options) {

            var defaults = {
                // how close to the scrollbar is to the bottom before triggering the event
                proximity: 0
            };

            var options = $.extend(defaults, options);

            return this.each(function() {
                var obj = this;
                $(obj).bind("scroll", function() {
                    if (obj == window) {
                        scrollHeight = $(document).height();
                    }
                    else {
                        scrollHeight = $(obj)[0].scrollHeight;
                    }
                    scrollPosition = $(obj).height() + $(obj).scrollTop();
                    if ( (scrollHeight - scrollPosition) / scrollHeight <= options.proximity) {
                        $(obj).trigger("bottom");
                    }
                });

                return false;
            });
        };
    })(jQuery);
