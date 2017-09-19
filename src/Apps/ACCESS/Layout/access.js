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
                        html += '<span class="' + item.imgclass + '"><img src="' + item.imgsrc + '" class="ui-icon-empty"/></span>';
                    }

                } else {
                    html += item.label
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
                    .attr("title", "[TEXT:access:Show All Items]")
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

function addFieldToData(aoData, fieldName, fieldValue) {
    $.each(aoData, function (c) {
        if (aoData[c].name == fieldName && fieldValue != undefined) {
            aoData[c].value = fieldValue;
            return false;
        }
        return true;
    });
    return aoData;
}
function createDatatable(id, url, sortColumn, displayLength, colomDef, fnServerParams) {

    return $("#" + id).dataTable({
        bServerSide:true,
        bJQueryUI:true,
        bProcessing:true,
        bPaginate:displayLength ? true : false,
        iDisplayLength:displayLength,
        sAjaxSource:url,
        bDeferRender:true,
        sDom:'rt<"F"ip>',
        fnServerParams:fnServerParams,
        "aaSorting":[
            [sortColumn, 'asc']
        ],
        "oLanguage":{
            "sZeroRecords":"[TEXT:access:No matching record found]",
            "sInfo":"[TEXT:access:Showing _START_ to _END_ of _TOTAL_ ]",
            "sInfoEmpty":"[TEXT:access:No result]",
            "sProcessing":"[TEXT:access:Processing]",
            "sInfoFiltered":""
        },
        aoColumnDefs:colomDef
    });
}

function displaySubWindow(height, width, ref, title, datatable) {
    var dialog = $("#dialogmodal");
    if (dialog.length <= 0) {
        dialog = $('<div id="dialogmodal"></div>');
    }
    $.get(ref, function (data) {
        dialog.html(data);
        dialog.dialog({
            modal:true,
            title:title,
            draggable:true,
            resizable:false,
            height:height,
            width:width,
            stack:'.ui-dialog',
            position:"center",
            buttons:{
                "[TEXT:access:modify]":function () {
                    var form = $(this).find("form").serialize();
                    var $parent = $(this);
                    $.post("?app=ACCESS&action=MODIFY", form, function (data) {
                        if (!data.success) {
                            $parent.html(data.error);
                            $parent.dialog("option", "buttons", [
                                {
                                    text:"[TEXT:access:Close]",
                                    click:function () {
                                        $(this).dialog("close");
                                    }
                                }
                            ]);
                        } else {
                            datatable.fnFilter("");
                            $parent.dialog("close");
                        }
                    });
                },
                "[TEXT:accesss:Close]":function () {
                    $(this).dialog("close");
                }
            },
            close:function (event, ui) {
                $("#dialogmodal").remove();
            }
        });
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
