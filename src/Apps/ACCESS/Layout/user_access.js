$(function () {
    var cache = {};
    var $iduser = $("#iduser");
    var accountType = $iduser.attr("data-type");

    /**
     * Autocomplet for URG
     */
    $iduser.combobox({
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                var term = request.term;
                if (term in cache) {
                    response(cache[ term ]);
                    return;
                }
                $.getJSON("?app=ACCESS&action=GET_ACCOUNT", {
                    "accountType":accountType,
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
                urgDatatable.fnFilter("");
                return false;
            }
        }
    });

    /**
     * DATABLE FOR URG
     */
    window.urgDatatable = createDatatable("dataTable", '?app=ACCESS&action=USER_GET_DATATABLE_INFO&accountType=' + accountType, 0, 0, [
        {
            "aTargets":['first_column'],
            "mDataProp":"name",
            "sClass":"ui-corner-tl",
            "sWidth":"30%",
            fnRender:function (data) {
                return '<a class="access" title="'+"[TEXT:Modify access]"+'" onclick="' + data.aData.edit + '" href="#"><img class="imgaccess" src="' + data.aData.imgaccess + '">' + data.aData.name + '</a> ';
            }
        },
        {
            "aTargets":['second_column'],
            "sWidth":"30%",
            "mDataProp":"description",
            "bSortable":false,
            "bSearchable":false
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
            aoData = addFieldToData(aoData, 'sSearch_' + i, value);
        });
        aoData.push({ "name":"totalRow", "value":oSettings._iRecordsTotal },
            {"name":"totalSearch", "value":oSettings._iRecordsDisplay}, {"name":"user_id", "value":$("#searchId").val()});
    });

    findSearchString($("#header").find("input"), ["name"], urgDatatable);
});

