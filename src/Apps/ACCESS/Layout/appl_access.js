$(function () {
    var cache = {};
    /**
     * Autocomplete for application
     */
    $("#idapp").combobox({
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                var term = request.term;
                if (term in cache) {
                    response(cache[ term ]);
                    return;
                }
                $.getJSON("?app=ACCESS&action=GET_APPS", {
                    "filterName":request.term
                }, function (data) {
                    cache[ term ] = data;
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
                appDatatable.fnFilter("");
                return false;
            }
        }
    });
    /**
     * Combox for accountype
     */
    $("#accounttype").combobox({
        mode:"button",
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                $.getJSON("?app=ACCESS&action=GET_ACCOUNTTYPES_IMAGE", function (data) {
                    response(data);
                });
            },
            select:function (event, ui) {
                if (!ui.item.imgclass) {
                    $(this).html('<img src="' + ui.item.imgsrc + '" />');
                } else {
                    $(this).html('<span class="'+ui.item.imgclass+'"><img src="' + ui.item.imgsrc + '" class="ui-icon-empty"/></span>')
                }

                $("#accounttypeValue").val(ui.item.value);
                appDatatable.fnDraw();
                return false;
            }
        }
    });

    /**
     * Datatable for  app
     */
    window.appDatatable = createDatatable("dataTable", '?app=ACCESS&action=GET_DATATABLE_INFO', 1, 20, [
        {
            "aTargets":['accounttype'],
            "mDataProp":"imgaccess",
            "sWidth":"55px",
            "sClass":"accounttypeimg ui-corner-tl",
            fnRender:function (data) {
                return '<a class="access" title="'+"[TEXT:Modify access]"+'" onclick="' + data.aData.edit + '" href="#"><img src="' + data.aData.imgaccess + '"></a> ';
            }
        },
        {
            "aTargets":['first_column'],
            "mDataProp":"name",
            "sWidth":"30%",
            fnRender:function (data) {
                return '<a class="access" title="'+"[TEXT:Modify access]"+'" onclick="' + data.aData.edit + '" href="#">' + data.aData.name + '</a> ';
            }
        },
        {
            "aTargets":['second_column'],
            "sWidth":"30%",
            "mDataProp":"description"
        },
        {
            "aTargets":['third_column'],
            "mDataProp":"aclname",
             "sClass":"ui-corner-tr",
            "bSortable":false,
            "bSearchable":false
        }
    ], function (aoData) {
        var oSettings = this.fnSettings();
        $("#header").find("th").each(function (i) {
            var value = $(this).find("input").val();
            if ($(this).children(0).find(".ui-combobox").length > 0) {
                value = $("#accounttypeValue").val();
            }
            aoData = addFieldToData(aoData, 'sSearch_' + i, value);
        });
        aoData.push({ "name":"totalRow", "value":oSettings._iRecordsTotal },
            {"name":"totalSearch", "value":oSettings._iRecordsDisplay}, {"name":"app_id", "value":$("#searchId").val()});
    });

    findSearchString($("#header").find("input"), ["name", "description"], appDatatable);

});