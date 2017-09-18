(function ($) {
    $.widget("ui.combobox", {
        options:{
            autocomplete:{},
            mode:"input"
        },
        _create:function () {
            var input = this.options.mode == "input" ? "<input>" : "<button></button>",
                that = this,
                wasOpen = false,
                select = this.element.hide(),
                value = select.val() ? select.val() : "",
                wrapper = this.wrapper = $("<span>")
                    .addClass("ui-combobox")
                    .insertAfter(select);

            input = $(input)
                .appendTo(wrapper)
                .attr({
                    "title":select.attr("title"),
                    "id":select.prop("id")
                })
                .val(value)
                .addClass("ui-state-default ui-combobox-input")
                .autocomplete(this.options.autocomplete)
                .addClass("ui-widget ui-widget-content ui-corner-left")
                .on("focusout", function () {
                    var oldValue = $(this).attr("data-old-value");
                    if (oldValue) {
                        $(this).val(oldValue);
                        $(this).attr("data-old-value", "");
                    }
                });
            if (this.options.mode == "button") {
                input.html(select.html());
                input.on({
                    "click":function (event, ui) {
                        if (wasOpen) {
                            $(this).autocomplete("close");
                            return false;
                        }
                        $(this).autocomplete("search");
                        return false;
                    },
                    "mousedown":function () {
                        wasOpen = input.autocomplete("widget").is(":visible");
                    }}).css("cursor", "pointer").removeClass("ui-corner-left").addClass("ui-corner-all");
            }

            input.data("autocomplete")._renderItem = function (ul, item) {
                var html = "<a>";
                if (item.imgsrc) {
                    if (!item.imgclass) {
                        html += '<img title="' + item.label + '" src="' + item.imgsrc + '"/>';
                    } else {
                        html += '<span  class="' + item.imgclass + '"><img title="' + item.label + '" src="' + item.imgsrc + '" class="ui-icon-empty"/></span>';
                    }
                } else {
                    html += item.label;
                }
                html += "</a>";
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append(html)
                    .appendTo(ul);
            };
            if (this.options.mode == "input") {
                $("<a>")
                    .attr("tabIndex", -1)
                    .attr("title", "[TEXT:fusers:Show All Items]")
                    .appendTo(wrapper)
                    .button({
                        icons:{
                            primary:"ui-icon-triangle-1-s"
                        },
                        text:false
                    })
                    .removeClass("ui-corner-all")
                    .addClass("ui-corner-right ui-combobox-toggle")
                    .on({
                        "mousedown":function () {
                            wasOpen = input.autocomplete("widget").is(":visible");
                        },
                        "click":function () {
                            input.attr("data-old-value", input.val());
                            input.focus();
// close if already visible
                            if (wasOpen) {
                                return;
                            }
                            input.val("");
// pass empty string as value to search for, displaying all results
                            input.autocomplete("search");
                        }
                    });
            }
            select.remove();
        },
        _destroy:function () {
            this.wrapper.remove();
            this.element.show();
        }
    });
})(jQuery);

$(function () {
    $.ui.dialog.prototype._makeDraggable = function () {
        this.uiDialog.draggable({
            containment:false
        });
    };
});

function focuskey(expand) {
    var kid = document.getElementById("kid");
    if (kid) {
        kid.focus();
        kid.select();
    }
    if (expand) {
        expandTree('gtree');
        expandTree('gtreeeall');
    }
}

function htmlEncode(value) {
    if (!value) {
        return '';
    }
    return $("<div/>").text(value).html();
}

function refreshRightSide(action, grp, elem) {
    var $element = $(elem);
    $(".selected").removeClass("selected");
    $(document.body).data("selectedSpanId", $element.children("span:first").attr("id"));
    setSelected();
    $.post("?app=FUSERS", {
        "action":"FUSERS_DATATABLES_LAYOUT",
        "type":action,
        "group":grp
    }, function (data) {
        $("#finfo").html(data);
        var searchCols = [];
        var columnDefs = [];
        switch (action) {
            case "user":
                columnDefs = [
                    {
                        "aTargets":['icon'],
                        "mDataProp":"icon",
                        "sWidth":"55px",
                        "sClass":"typeimg ui-corner-tl",
                        "bSortable":false,
                        bUseRendered:false,
                        fnRender:function (data) {
                            return '<img src="' + data.aData.icon + '">';
                        }
                    },
                    {
                        "aTargets":['us_login'],
                        "mDataProp":"us_login",
                        bUseRendered:false,
                        fnRender:function (data) {
                            return '<a href="api/v1/documents/' + data.aData.id + '.html">' + htmlEncode(data.aData.us_login) + '</a>';
                        }
                    },
                    {
                        "aTargets":['us_lname'],
                        "mDataProp":"us_lname",
                        bUseRendered:false,
                        fnRender:function (data) {
                            return htmlEncode(data.aData.us_lname);
                        }
                    },
                    {
                        "aTargets":['us_fname'],
                        "mDataProp":"us_fname",
                        bUseRendered:false,
                        fnRender:function (data) {
                            return htmlEncode(data.aData.us_fname);
                        }
                    },
                    {
                        "aTargets":['us_mail'],
                        "mDataProp":"us_mail",
                        bUseRendered:false,
                        fnRender:function (data) {
                            return  htmlEncode(data.aData.us_mail) ;
                        }
                    }
                ];
                searchCols = ["us_login", "us_lname", "us_fname", "us_mail"];
                break;
            case "role":
                columnDefs = [
                    {
                        "aTargets":['icon'],
                        "mDataProp":"icon",
                        "sWidth":"55px",
                        "sClass":"typeimg ui-corner-tl",
                        "bSortable":false,
                        bUseRendered:false,
                        fnRender:function (data) {
                            return '<a class="type" title="' + "[TEXT:Modify type]" + '" href="#"><img src="' + data.aData.icon + '"></a> ';
                        }
                    },
                    {
                        "aTargets":['title'],
                        "mDataProp":"title",
                        fnRender:function (data) {
                            return'<a href="api/v1/documents/' + data.aData.id + '.html">' + htmlEncode(data.aData.title) + '</a>';
                        }
                    }
                ];
                searchCols = ["title"];
                break;
            case "group":
                columnDefs = [
                    {
                        "aTargets":['icon'],
                        "mDataProp":"icon",
                        "sWidth":"55px",
                        "sClass":"typeimg ui-corner-tl",
                        "bSortable":false,
                        bUseRendered:false,
                        fnRender:function (data) {
                            return '<a class="type" title="' + "[TEXT:Modify type]" + '" href="#"><img src="' + data.aData.icon + '"></a> ';
                        }
                    },
                    {
                        "aTargets":['us_login'],
                        "mDataProp":"us_login",
                        fnRender:function (data) {
                            return '<a href="api/v1/documents/' + data.aData.id + '.html">' + htmlEncode(data.aData.us_login) + '</a>';
                        }
                    },
                    {
                        "aTargets":['grp_name'],
                        "mDataProp":"grp_name",
                        fnRender:function (data) {
                            return htmlEncode(data.aData.grp_name);
                        }
                    }
                ];
                searchCols = ["title"];
                break;
        }
        window.datatable = setDatatable(columnDefs, action);

        findSearchString($("#header").find("input"), searchCols, datatable);
        $("#menu").hide().menu().children().each(function () {
            var parent = $(this);
            parent.on("click", function () {
                displayWindow(400, 600, parent.find("a").attr("href"), action);
                $("#menu").hide();
                return false;
            });
        });
        $("#mainaction").button().on("click", function () {
            displayWindow(400, 600, $(this).attr("href"), action);
            return false;
        });
        $("#otheraction").button({
            text:false,
            icons:{
                primary:"ui-icon-triangle-1-s"
            }
        }).on("click", function () {
                var menu = $("#menu");
                if (menu.css("display") != "none") {
                    menu.hide();
                    return false;
                }
                menu.show().position({
                    my:"left top",
                    at:"left bottom",
                    of:this
                });
                var menuPosition = menu.offset();
                var marginLeftRight  = (menu.css("position") === "absolute") ? 0: ($("body").outerWidth(true)  - $("body").outerWidth());
                menu.css({
                    position: "absolute",
                    top: menuPosition.top,
                    left: menuPosition.left + marginLeftRight
                });
                $(window).one("click", function () {
                    menu.hide();
                });
                return false;
            });
        $("#buttonset").buttonset();
    });


    return false;
}

function addFieldToData(aoData, fieldName, fieldValue) {
    $.each(aoData, function (c) {
        if (aoData[c].name == fieldName && fieldValue !== undefined) {
            aoData[c].value = fieldValue;
            return false;
        }
        return true;
    });
    return aoData;
}

function setDatatable(columnDef, type) {
    return $(".dataTable").dataTable({
        bServerSide:true,
        bJQueryUI:true,
        bProcessing:true,
        bPaginate:true,
        iDisplayLength:$("#fusersDisplayLength").val(),
        sAjaxSource:"?app=FUSERS&action=FUSERS_GET_DATATABLE_INFO",
        bDeferRender:true,
        "sScrollY": "100px",
        sDom:'rt<"F"ip>l',
        "aaSorting":[
            [1, 'asc']
        ],
        fnRowCallback:function (nRow, aData, iDisplayIndex) {
            $(nRow).addClass("tableRow").on("click", function () {
                displayWindow(400, 600, 'api/v1/documents/' + aData["id"]+'.html', type);
                return false;
            });
            return nRow;
        },
        fnServerParams:function (aoData) {
            var oSettings = this.fnSettings();
            var filters={};
            var $header=$("#header");
            $header.find("th").each(function (i) {
                var $input=$(this).find("input");
                var attrKey;
                var value = $input.val();
                if ($(this).children(0).find(".ui-combobox").length > 0) {
                    value = $("#typeValue").val();
                    attrKey="family";
                } else {
                    attrKey=$input.attr("id");
                }
                aoData = addFieldToData(aoData, 'sSearch_' + i, value);
                if (value) {
                    filters[attrKey]=value;
                }
            });
            $header.data("filters", filters);
            aoData.push({ "name":"totalRow", "value":oSettings._iRecordsTotal },
                {"name":"totalSearch", "value":oSettings._iRecordsDisplay},
                {"name":"type", "value":$("#fusersType").val()},
                {"name":"group", "value":$("#fusersGroup").val()});
        },
        fnDrawCallback:function () {
            $(".dataTables_scrollBody").height($(window).height() - $("#buttonset").outerHeight(true) - $(".dataTables_info").parent().outerHeight(true) - $(".dataTables_length").outerHeight(true)- $(".dataTables_scrollHead").outerHeight(true) - correctedDataTableHeight);
            $("#icon").combobox({
                mode:"button",
                autocomplete:{
                    minLength:0,
                    source:function (request, response) {
                        $.getJSON("?app=FUSERS&action=GET_TYPE_IMAGE&type=" + $("#fusersType").val(), function (data) {
                            response(data);
                        });
                    },
                    select:function (event, ui) {
                        if (!ui.item.imgclass) {
                            $(this).html('<img src="' + ui.item.imgsrc + '" title="' + ui.item.label + '"/>');
                        } else {
                            $(this).html('<span  class="' + ui.item.imgclass + '"><img title="' + ui.item.label + '" src="' + ui.item.imgsrc + '" class="ui-icon-empty"/></span>');
                        }
                        $("#typeValue").val(ui.item.value);
                        datatable.fnDraw();
                        return false;
                    }
                }
            });
        },
        "oLanguage":{
            "sZeroRecords":"[TEXT:fusers:No matching record found]",
            "sInfo":"[TEXT:fusers:Showing _START_ to _END_ of _TOTAL_ ]",
            "sInfoEmpty":"[TEXT:fusers:No result]",
            "sInfoFiltered":"",
            "sInfoThousands":" ",
            "sProcessing":"[TEXT:fusers:Processing]",
            "sLengthMenu":"[TEXT:show _MENU_ per page]"
        },
        aoColumnDefs:columnDef
    });
}

function findSearchString($elements, fields, dataTable) {
    $elements.each(function (index, element) {
        $(element).on({
            "keypress":function (e) {
                if (e.keyCode == 13) {
                    var index = $.inArray(this.name, fields);
                    if (index == -1) {
                        /* Filter on all columns  of this element */
                        dataTable.fnFilter(this.value);
                    } else {
                        /* Filter on column (index) of this element */
                        dataTable.fnFilter(this.value, index);
                    }
                    return false;
                }
                return true;
            },
            "click":function (e) {
                //Prevent bubbling event
                return false;
            }
        });
    });
}

function displayWindow(height, width, ref, type) {
    var dialog = $("#dialogmodal"),
    $width = $(window).width() * 0.8,
    $height = $(window).height() * 0.8;
    //var $jParent = window.parent.jQuery.noConflict();
    if (dialog.length <= 0) {
        dialog = $('<iframe id="dialogmodal" style="padding: 0;" src="' + ref + '" frameborder="0"></iframe>').appendTo('body');
    } else {
        dialog.attr("src", ref);
    }
    dialog.dialog({
        autoOpen:true,
        modal:true,
        draggable:true,
        resizable:true,
        height:$height,
        width:$width,
        open:function (event, ui) {
            if (isIE) {
                $('body').css('overflow', 'hidden');
            }
        },
        overlay:{
            opacity:0.5,
            background:"black"
        },
        position:"center",
        beforeClose:function () {
            $(this).attr("src", "Images/1x1.gif");
            return false;
        }
    });

    dialog.width($width).height($height);

    dialog.on("load", function () {
            var $this = $(this), doc, oldFrame;
            doc = this.contentDocument || this.contentWindow.document;
            if (doc) {
                dialog.dialog("option", "title", $("<div/>").text((doc.title || "")).html());
            }
            if (doc && doc.location && doc.location.href &&
                doc.location.href.toLowerCase().indexOf("images/1x1.gif") > -1) {
                oldFrame = $("#oldFrame");
                if (oldFrame.length === 0) {
                    oldFrame = $('<div id="oldFrame" style="display : none;"></div>');
                    $("body").append(oldFrame);
                }
                oldFrame.empty();
                /**detach and reattach iframe to handle ff infinite load bug**/
                $this.remove();
                datatable.fnDraw();
                if (isIE) {
                    $('body').css('overflow', 'auto');
                }
                if (type == "group") {
                    refreshLeftSide();
                }
                oldFrame.append($this);
            }
        });
}

function setSelected() {
    var $body = $(document.body), spanNode,
    selectedSpanId = $body.data('selectedSpanId');
    $(".selected").removeClass("selected");
    if (selectedSpanId === undefined) {
        return false;
    }
    spanNode = document.getElementById(selectedSpanId);
    if (spanNode === null) {
        $body.removeData('selectedSpanId');
        return false;
    }
    $(spanNode).parent('a').addClass("selected");
    var itemId = $(spanNode).closest('li').attr('id');
    if (itemId) {
        window.trees.gtree.expandToItem(itemId);
    }
    return true;
}

function refreshLeftSide() {
    $.post("?app=FUSERS&action=FUSERS_LIST", function (data) {
        $("#flist").html(data);
        var gtreeState = window.trees.gtree.states.serializeStates();
        window.trees = {
            'gtree': new FUSERS.mktree('gtree', gtreeState, {appName: 'FUSERS', paramName: 'FUSERS_GTREESTATE'}),
            'gtreeall': new FUSERS.mktree('gtreeall')
        };
        setSelected();
        focuskey(false);
    });
}
var correctedDataTableHeight = 25;
$(document).ready(function () {
    var $gtree = $("#gtree");
    var gtreemargin = $gtree.outerHeight(true) - $gtree.outerHeight();
    var adjustHeight= function () {
        $("#gtree").height($(window).height() - $gtree.offset().top  - gtreemargin);
        $(".dataTables_scrollBody").height($(window).height() - $("#buttonset").outerHeight(true) - $(".dataTables_info").parent().outerHeight(true) - $(".dataTables_length").outerHeight(true)- $(".dataTables_scrollHead").outerHeight(true) - correctedDataTableHeight);
    };

    window.trees = {
        gtree: new FUSERS.mktree('gtree', undefined, {appName: 'FUSERS', paramName: 'FUSERS_GTREESTATE'}),
        gtreeall: new FUSERS.mktree('gtreeall')
    };
    focuskey(false);
    refreshRightSide('user', 0, $("#SPANUsers").parent());
    gtreemargin += 18;

    adjustHeight();
    window.setTimeout(adjustHeight, 500);
    $(window).on("resize", adjustHeight);

    /**
     * Complete form inputs to export accounts
     */
    $(".fusers-info").on("click", ".account-export", function () {
        var type=$("#fusersType").val();
        var selectedGroup=$("#fusersGroup").val();

        $.get("?app=FUSERS&action=FUSERS_EXPORTFORM&accountType="+type+"&group="+selectedGroup).done(function (data) {
            var $form=$("<div/>").append(data);
            var $submit=$form.find("input[type=submit]");
            var $abort=$form.find("button.abort");
            var $iframe=$form.find("iframe");
            var $abortUrl="?app=FUSERS&action=FUSERS_EXPORTSTATUS&abort=true&statusKey="+ $form.find("input[name=statusKey]").val();
            var info, filterInfo;
            var filters=$("#header").data("filters");

            $form.find("[name=accountType]").val($("#fusersType").val());
            $form.find("[name=selectedGroup]").val($("#fusersGroup").val());
            $submit.button();
            $form.find("[name=filters]").val(JSON.stringify(filters));

            info=$(".do a.selected").text();
            if (filters) {
                for (var prop in filters) {
                    filterInfo=$("input#"+prop).attr("placeholder");
                    if (!filterInfo) {
                        if (prop === "family") {
                            filters[prop]=$("#icon").find("img").attr("title");
                            filterInfo=$(".icon").data("label");
                        }
                        if (!filterInfo) {
                            filterInfo=prop;
                        }
                    }
                    info += ", "+(filterInfo)+" : "+filters[prop];
                }
            }
            info+='.';
            $form.find(".fusers-export-info").text(info);


            $iframe.on("load", function () {
                if (this.contentWindow && this.contentWindow.location.href !== "about:blank") {
                    $(this).css("height", "200px").css("width","100%");
                }
            });
            $form.dialog({
                autoOpen:true,
                modal:true,
                draggable:true,
                resizable:true,
                height:'auto',
                width:440,
                title:$form.find("form").data("title"),
                close: function () {
                    $form.remove();
                    $.getJSON($abortUrl);
                    $form=$("<div/>");

                },
                overlay:{
                    opacity:0.5,
                    background:"black"
                },
                position:"center"

            });
            $abort.button();
            $abort.on("click", function () {
                $.getJSON($abortUrl);
            });
            $submit.on("mouseup", function () {
                var url="?app=FUSERS&action=FUSERS_EXPORTSTATUS&statusKey="+ $form.find("input[name=statusKey]").val();

                var poolFunction=function () {
                    $.getJSON(url).done(function (data) {
                        var $status=$form.find(".status");
                        if (data.msg === "::END::") {
                            $submit.prop("disabled",false).removeClass("ui-state-disabled");
                            $status.find("> span").text('END');
                            $status.hide();
                        } else {
                            $status.show();
                            $submit.prop("disabled",true).addClass("ui-state-disabled");
                            $status.find("> span").text(data.msg);
                            if ($status.length > 0) {
                                window.setTimeout(poolFunction, 1000);
                            }
                        }
                    });
                };
                // Follow status each seconds
                window.setTimeout(poolFunction, 1000);

            });
        });
    }).on("click", ".account-import", function () {
        $.get("?app=FUSERS&action=FUSERS_IMPORTFORM").done(function (data) {
            var $form=$("<div/>").append(data);

            var $submit=$form.find("input[type=submit]");
            var $iframe=$form.find("iframe");
            var $abort=$form.find("button.abort");
            var $dryRun=$form.find("select[name=analyzeOnly]");
            var $abortUrl="?app=FUSERS&action=FUSERS_IMPORTSTATUS&abort=true&statusKey="+ $form.find("input[name=statusKey]").val();
            var realImportDone=false;

            $submit.button();
            $abort.button();
            $form.find("input[type=file]").button();

            $abort.on("click", function () {

                $.getJSON($abortUrl);
            });



            $form.dialog({
                autoOpen:true,
                modal:true,
                draggable:true,
                resizable:true,
                height:400,
                width:'80%',
                title:$form.find("form").data("title"),
                close: function () {
                    $form.remove();
                    $.getJSON($abortUrl).done(function () {
                        if (realImportDone) {
                            window.location.reload();
                        }
                    });
                    $form=$("<div/>");
                },
                overlay:{
                    opacity:0.5,
                    background:"black"
                },
                position:"center"

            });
            $submit.prop("disabled",true).addClass("ui-state-disabled");
            $dryRun.on("change", function() {
                if ($(this).val() === "true") {
                    $submit.val($submit.attr("data-analyze-label"));
                    $submit.addClass("fuser-analyze");
                    $abort.find("span").text($abort.attr("data-analyze-label"));
                } else {
                    $submit.val($submit.attr("data-import-label"));
                    $submit.removeClass("fuser-analyze");
                    $abort.find("span").text($abort.attr("data-import-label"));
                }
            });
            $dryRun.trigger("change");
            $submit.on("mouseup", function () {
                 var url="?app=FUSERS&action=FUSERS_IMPORTSTATUS&statusKey="+ $form.find("input[name=statusKey]").val();

                var poolFunction=function () {
                    $.getJSON(url).done(function (data) {
                        var $status=$form.find(".status");
                        if (data.msg === "::END::") {
                            if ($dryRun.val()==="false") {
                                realImportDone=true;
                            }
                            $submit.prop("disabled",false).removeClass("ui-state-disabled");
                            $status.find("> span").text('END');
                            $status.hide();
                            $iframe.css("opacity","");
                        } else {
                            $submit.prop("disabled",true).addClass("ui-state-disabled");
                            $status.show();
                            $status.find("> span").text(data.msg);
                            $iframe.css("opacity","0.5");
                            if ($status.length > 0) {
                                window.setTimeout(poolFunction, 1000);
                            }
                        }
                    });
                };

                // Follow status each seconds
                window.setTimeout(poolFunction, 1000);

             });
            $("input[name=accountImportFile]").on("change", function () {
                if ($(this).val()) {
                    $submit.prop("disabled", false).removeClass("ui-state-disabled");
                } else {
                    $submit.prop("disabled", true).addClass("ui-state-disabled");

                }

            });




        });
    });
});
