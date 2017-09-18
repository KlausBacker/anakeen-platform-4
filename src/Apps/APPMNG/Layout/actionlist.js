$(function () {
    var cache = {};
    /**
     * Autocomplete for application
     */
    $("#id").combobox({
        autocomplete:{
            minLength:0,
            source:function (request, response) {
                var term = request.term;
                if (term in cache) {
                    response(cache[ term ]);
                    return;
                }
                $.getJSON("?app=APPMNG&action=GET_APPS", {
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
                datatable.fnFilter("");
                return false;
            }
        }
    });

    /**
     * Datatable
     */
    window.datatable = createDatatable("dataTable", '?app=APPMNG&action=GET_DATATABLE_INFO', 0, 0, [
        {
            "aTargets":['name'],
            "bSortable":false,
            "mDataProp":"name",
            fnRender:function (data) {
                return '<a class="imgappmng" data-id="' + data.aData.id + '" title="[TEXT:edit]"></a>' + data.aData.name;
            }
        },
        {
            "aTargets":['short_name'],
            "bSortable":false,
            "mDataProp":"short_name"
        },
        {
            "aTargets":['available'],
            "sWidth":"15px",
            "bSortable":false,
            "mDataProp":"available"
        },
        {
            "bSortable":false,
            "aTargets":['acl'],
            "sWidth":"100px",
            "mDataProp":"acl"
        },
        {
            "bSortable":false,
            "sWidth":"15px",
            "aTargets":['root'],
            "mDataProp":"root"
        },
        {
            "bSortable":false,
            "sWidth":"85px",
            "aTargets":['openaccess'],
            "mDataProp":"openaccess"
        }
    ], function (aoData) {
        var oSettings = this.fnSettings();
        aoData.push({ "name":"totalRow", "value":oSettings._iRecordsTotal },
            {"name":"totalSearch", "value":oSettings._iRecordsDisplay}, {"name":"action_appl_id", "value":$("#searchId").val()});
    }, function () {
        $(".imgappmng").each(function (index, elem) {
            $(this).button({
                icons:{
                    primary:"ui-icon-pencil"
                },
                text:false
            }).on("click", function () {
                    var url = "?app=APPMNG&action=ACTION_EDIT&id=" + $(elem).attr("data-id");
                    var head = "[TEXT:titlemodifyaction]";
                    var postUrl = "?app=APPMNG&action=ACTION_MOD&action_appl_id=" + $("#searchId").val();
                    displaySubWindow(350, 450, url, head, datatable, postUrl);
                });
        });
    }, function (nRow, aData, iDisplayIndex) {
        if (aData["root"] == "Y") {
            $('td:eq(5)', nRow).addClass('greenlight');
        }
        if (aData["available"] == "N") {
            $('td:eq(2)', nRow).addClass('redalert');
        }
        return nRow;
    });

});
