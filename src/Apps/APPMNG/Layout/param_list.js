$(function () {
    var $datatable = $(".datatable");


    $("#displayStatic").button().on("click", function () {
        var $this = $(this).next();
        if ($this.attr("data-pressed") == "true") {
            $this.find(".ui-button-text").text("[TEXT:Show static parameters]");
            $this.attr("data-pressed", "false");
        } else {
            $this.find(".ui-button-text").text("[TEXT:Hide static parameters]");
            $this.attr("data-pressed", "true");
        }

        datatable.fnDraw();
    });

    $("#fedit").on("submit", function () {
        var form = $(this).serialize();
        form += "&action=" + $(this).attr("data-action");
        $.post("?app=APPMNG", form, function (data) {
            if (!data.success) {
                displayWarningMsg(data.error);
            } else {
                $("#dedit").hide().appendTo("#editdefault");
                if (editedParam) {
                    editedParam.setAttribute('avalue', data.data.value);
                    editedParam.style.display = 'inline';
                    if (editedParam.tagName == 'DIV') {
                        if (data.data.value === null) {
                            data.data.value = '';
                        }
                        if ($("#fedit").find("[name=atype]").val()[0] !== "U") {
                            modified[data.data.id + data.data.appid] = {
                                "value":htmlEncode(data.data.value) + " <i>(" + data.data.textModify + ")</i>",
                                "class":'changed'
                            };
                            editedParam.innerHTML = modified[data.data.id + data.data.appid]["value"];
                            editedParam.className += ' changed';
                        }
                    }
                }
                editedParam = '';
                if (colorPick2) {
                    colorPick2.hidePopup();
                }
                submiting = false;
                datatable.fnDraw();
            }
        });
        return false;
    });

    $("#val").on("blur", function blurInput() {
        var $this = $(this);
        if ($('#optionPickerDiv').css("visibility") === "visible") {
            return null;
        }
        if (colorPick2) {
            colorPick2.hidePopup();
        }
        if ($this.val() != $this.attr("originvalue")) {
            //$("#fedit").trigger("submit");
        } else if (editedParam) {
            $(editedParam).show();
            $("#dedit").hide().appendTo($("#editdefault"));
            editedParam = '';
        }
        return null;
    });

    window.datatable = $datatable.dataTable({
        bServerSide:true,
        bJQueryUI:true,
        bProcessing:true,
        bPaginate:false,
        iDisplayLength:0,
        bSort:false,
        sAjaxSource:"?app=APPMNG&action=GET_PARAM_LIST_DATATABLE_INFO",
        bDeferRender:true,
        sDom:'rt',
        fnServerParams:function (aoData) {
            var $this = $(this);
            $this.find("th").each(function (i) {
                var value = $(this).find("input").val();
                aoData = addFieldToData(aoData, 'sSearch_' + i, value);
            });
            aoData.push(
                {
                    "name":"userid", "value":$this.attr("data-userid")
                },
                {
                    "name":"pview", "value":$this.attr("data-pview")
                },
                {
                    "name":"type", "value":$this.attr("data-type")
                },
                {
                    "name":"appid", "value":$this.attr("data-appid")
                },
                {
                    "name":"withstatic", "value":$("#displayStatic").next().attr("data-pressed")
                });
        },
        "oLanguage":{
            "sZeroRecords":"[TEXT:appmng:No matching record found]",
            "sInfo":"[TEXT:Showing _TOTAL_ result]",
            "sInfoEmpty":"[TEXT:appmng:No result]",
            "sProcessing":"[TEXT:appmng:Processing]",
            "sInfoFiltered":""
        },
        fnDrawCallback:function () {
            $(".groupby").css("width", "70px");
            $(".deletebutton").button({
                icons:{
                    primary:"ui-icon-trash"
                },
                text:false
            }).on("click", function () {
                    var $parent = $(this);
                    $("<div><span style='height:230px;'>[TEXT:delconfirmparam] " + $parent.attr("data-id") + " ?</span></div>").dialog({
                        resizable:false,
                        height:140,
                        title:"[TEXT:delete]",
                        modal:true,
                        buttons:{
                            "[TEXT:delete]":function () {
                                $.post("?app=APPMNG", {
                                    "action":$("#actiondel").val(),
                                    "id":$parent.attr("data-id"),
                                    "appid":$parent.attr("data-appid"),
                                    "atype":$parent.attr("data-type")
                                }, function () {
                                    delete modified[$parent.attr("data-id") + $parent.attr("data-appid")];
                                    datatable.fnDraw();
                                });
                                $(this).dialog("close");
                            },
                            "[TEXT:Cancel]":function () {
                                $(this).dialog("close");
                            }
                        },
                        close:function () {
                            $(this).dialog("destroy");
                        }
                    });
                    return false;
                });
            $("div.static").parent().addClass("static");
            $(this).find(".appHeader").each(function () {
                var delete1 = $(this).children().eq(0).attr("colspan", "2").next();
                var delete2 = delete1.next().attr("colspan", "2").next();
                delete1.remove();
                delete2.remove();
            });
        },
        aoColumnDefs:[
            {
                "aTargets":['description'],
                "mDataProp":"descr",
                "sClass":"desc",
                "sWidth":"15%",
                bUseRendered:false,
                fnRender:function (data) {
                    return '<div class="' + data.aData.classtype + ' column">' + data.aData.descr + '</div>';
                }
            },
            {
                "aTargets":['groupby'],
                "bSearchable":true,
                "mDataProp":"appname",
                "sWidth":"70px",
                bUseRendered:false,
                fnRender:function (data) {
                    var elem = data.aData.appname;
                    if (data.aData.appicon) {
                        elem = '<img border="0" class="imgApp" src="' + data.aData.appicon + '"/>' + elem;
                    } else {
                        elem = '<a data-id="' + data.aData.name + '" data-appid="' + data.aData.appid + '" data-type="' + data.aData.type + '" class="' + data.aData.classtype + ' deletebutton" border="0" title="[TEXT:delete]"></a>';
                    }
                    return elem;
                }
            },
            {
                "aTargets":['name'],
                "sWidth":"15%",
                "bSearchable":true,
                "mDataProp":"name",
                bUseRendered:false,
                fnRender:function (data) {
                    return '<div class="' + data.aData.classtype + ' column">' + data.aData.name + '</div>';
                }
            },
            {
                "aTargets":['valeur'],
                "mDataProp":"val",
                "sClass":"values",
                bUseRendered:false,
                fnRender:function (data) {
                    if (!data.aData.appid) {
                        return '<div class="column static"></div>';
                    }
                    var onclick = "movediv(this,'" + data.aData.name + "','" + data.aData.type + "','" + data.aData.appid + "','" + data.aData.kind + "',this.getAttribute('avalue'))";
                    var id = data.aData["DT_RowId"];
                    var val = (data.aData.val ? htmlEncode(data.aData.val) : "&nbsp;");
                    var rowclass = data.aData.classtype;
                    if (modified[id]) {
                        val = modified[id]["value"];
                        rowclass += " " + modified[id]["class"];
                    }
                    return '<div id="v' + id + '" avalue="' + data.aData.sval + '" class="' + rowclass + ' column" onclick="' + onclick + '">' + val + '</div>';
                }
            }
        ]
    });

    /**
     * Combox for user
     */
    $("#userid").combobox({
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                $.getJSON("?app=APPMNG&action=GET_USERS", {
                    "filterName":request.term
                }, function (data) {
                    response(data);
                });
            },
            focus:function (event, ui) {
                $(this).val(ui.item.label);
                $("#searchId").val(ui.item.value);
                return false;
            },
            select:function (event, ui) {
                $(this).val(ui.item.label);
                $("#searchId").val(ui.item.value);
                $(".datatable").attr("data-userid", ui.item.value);
                datatable.fnFilter("");
                return false;
            }
        }
    });

    $("#appid").combobox({
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                var $appsearchid = $("#appsearchId");
                $.getJSON("?app=APPMNG&action=GET_APPS_PARAMS", {
                    "filterName":request.term,
                    "pview":$appsearchid.attr("data-pview"),
                    "type":$appsearchid.attr("data-type"),
                    "withstatic":$("#displayStatic").next().attr("data-pressed")
                }, function (data) {
                    response(data);
                });
            },
            focus:function (event, ui) {
                $(this).val(ui.item.label);
                $("#appsearchId").val(ui.item.value);
                return false;
            },
            select:function (event, ui) {
                $(this).val(ui.item.label);
                $("#appsearchId").val(ui.item.value);
                $(".datatable").attr("data-appid", ui.item.value);
                datatable.fnFilter("");
                return false;
            }
        }
    });

    $("#tabs").tabs({
        select:function (event, ui) {
            $.fn.dataTableExt.iApiIndex = ui.index;
            $("#appsearchId").attr("data-type", ui.index ? "system" : "normal").val("");
            $("#appid").val("");
            $(".datatable").attr("data-appid", "");
            datatable.fnDraw();
        }
    });
    var lastLi = $("#tabs").find("ul");
    $(".searchappbutton").appendTo(lastLi);

    findSearchString($datatable.find("thead").find("input"), ["appname", "name"], datatable);
});
function htmlEncode(value) {
    if (!value) {
        return '';
    }
    return $("<div/>").text(value).html();
}

var modified = {};
var currentColor = '';
var submiting = false;
var colorPick2 = false;
var selectOpen = false;
var op = new OptionPicker();

function CHC(color) {
    currentColor = color;
    ColorPicker_highlightColor(color);
}

function pickColor(color) {
    document.fedit.val.value = color;
    document.fedit.val.style.backgroundColor = color;
    document.fedit.val.focus();
}

function pickOption(value) {
    document.fedit.val.value = value;
    document.fedit.val.focus();
}


var editedParam = '';

function movediv(th, Aname, Atype, Appid, Kind, Value) {
    var firstColorInit = false;
    $("#val").css("background-color", "");
    if (submiting) {
        return; // wait return of submit
    }
    if (Kind == 'static' || Kind == 'readonly' || !Kind) {
        alert('[TEXT:unmodifiable parameter]');
        return;
    }
    if ((editedParam != '') && (editedParam.id == th.id)) {
        return;
    }
// undisplay cell containt
    th.style.display = 'none';
    document.getElementById('dedit').style.display = 'block';
    th.parentNode.appendChild(document.getElementById('dedit'));
    var formEdit = document.getElementById('fedit');

    formEdit.appid.value = Appid;
    formEdit.atype.value = Atype;
    formEdit.aname.value = Aname;
    formEdit.val.value = Value;
    if (Kind == 'password') {
        formEdit.val.type = 'password';
    }
    else formEdit.val.type = 'text';
    formEdit.val.focus();
    formEdit.val.select();
    formEdit.val.setAttribute('originvalue', Value);

// redisplay precedent cell
    if (editedParam != '') {
        editedParam.style.display = 'inline';
    }

    editedParam = th;
// show color picker if needed
    if (Kind == 'color') {
        if (!colorPick2) {
            colorPick2 = new ColorPicker();
            firstColorInit = true;
        }
        colorPick2.show('dedit');
        if (firstColorInit) {
            $("#pickercolortable").find("td").removeAttr("onclick");
            $("#pickercolortable").on("mousedown", "td", function (event) {
                event.preventDefault();
                CPC(currentColor);
                if (colorPick2) {
                    //colorPick2.hidePopup();
                }
                //$("#val").trigger("blur");
            });
        }

    } else {
        if (colorPick2) {
            colorPick2.hidePopup();
        }
        if (Kind.substr(0, 4) == 'enum') {
            op.show('dedit');
            $("#" + op.divName).css("z-index", 100);
            op.setOptions(Kind.substr(5, Kind.length - 6).split('|'));
            selectOpen = true;
        }
    }

}
