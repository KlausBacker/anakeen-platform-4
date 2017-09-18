$(function () {
    /**
     * Datatable
     */
    window.datatable = createDatatable("dataTable", '?app=APPMNG&action=GET_APP_DATATABLE_INFO', 1, 0, [
        {
            "aTargets":['name'],
            "bSortable":false,
            "mDataProp":"name",
            "sWidth":"150px",
            fnRender:function (data) {
                return '<img border="0" class="imgappmng" src="' + data.aData.appicon + '">' + data.aData.name;
            }
        },
        {
            "aTargets":['id'],
            "bSortable":false,
            "mDataProp":"id",
            "sWidth":"120px",
            "sClass": "buttonColumn",
            fnRender:function (data) {
                return '<a class="update" href="#" data-id="' + data.aData.id + '" title="[TEXT:update]">' +
                    '</a>' +
                    '<a class="edit" href="#" data-id="' + data.aData.id + '" title="[TEXT:edit]"></a>' +
                    '<a class="delete" data-id="' + data.aData.id + '" data-name="' + data.aData.name + '" title="[TEXT:delete]"></a>';
            }
        },
        {
            "aTargets":['available'],
            "sWidth":"15px",
            "bSortable":false,
            "mDataProp":"available"
        },
        {
            "aTargets":['displayable'],
            "sWidth":"15px",
            "bSortable":false,
            "mDataProp":"displayable"
        },
        {
            "bSortable":false,
            "aTargets":['version'],
            "sWidth":"80px",
            "mDataProp":"version"
        },
        {
            "bSortable":false,
            "aTargets":['description'],
            "mDataProp":"description"
        }
    ], function (aoData) {
        var oSettings = this.fnSettings();
        aoData.push({ "name":"totalRow", "value":oSettings._iRecordsTotal },
            {"name":"totalSearch", "value":oSettings._iRecordsDisplay});
    }, function () {
        $(".update")
            .button({
                text:false,
                icons:{
                    primary:"ui-icon-refresh"
                }
            })
            .removeClass("ui-corner-all")
            .addClass("ui-corner-left")
            .on("click", function () {
                var id = $(this).attr("data-id");
                showOverlay();
                $.post("?app=APPMNG&action=APP_UPDATE", {
                    "appsel":id
                }, function () {
                    hideOverlay();
                    datatable.fnDraw();
                });
                return false;
            });
        $(".edit")
            .button({
                icons:{
                    primary:"ui-icon-pencil"
                },
                text:false
            })
            .removeClass("ui-corner-all")
            .on("click", function () {
                var id = $(this).attr("data-id");
                var url = "?app=APPMNG&action=APP_EDIT&id=" + id;
                var postUrl = "?app=APPMNG&action=APP_MOD&id=" + id;
                displaySubWindow(350, 450, url, "[TEXT:titlemodify]", datatable, postUrl);
                return false;
            });
        $(".delete").button({
            icons:{
                primary:"ui-icon-trash"
            },
            text:false
        })
            .removeClass("ui-corner-all")
            .addClass("ui-corner-right")
            .on("click", function () {
                var name = $(this).attr("data-name");
                var id = $(this).attr("data-id");
                var dialog = $("#dialogmodal");
                if (dialog.length <= 0) {
                    dialog = $('<div id="dialogmodal"></div>');
                }
                dialog.html("<span>[TEXT:delconfirm] " + name + "</span>");
                dialog.dialog({
                    modal:true,
                    title:"[TEXT:delete application]",
                    draggable:true,
                    resizable:false,
                    position:"center",
                    buttons:{
                        "[TEXT:delete]":function () {
                            showOverlay();
                            var $parent = $(this);
                            var url = "?app=APPMNG&action=APP_DELETE&appsel="+id;
                            $.post(url, function (data) {
                                hideOverlay();
                                if (!data.success) {
                                    $parent.html(data.error);
                                    $parent.dialog("option", "buttons", [
                                        {
                                            text:"[TEXT:appmng:Close]",
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
                        "[TEXT:appmng:Close]":function () {
                            $(this).dialog("close");
                        }
                    },
                    close:function (event, ui) {
                        $("#dialogmodal").remove();
                    }
                });
                return false;
            });
    }, function (nRow, aData) {
        if (aData["available"] == "N") {
            $('td:eq(4)', nRow).addClass('redalert');
        }
        return nRow;
    });

    $("#updateAll").button({
        text:false,
        icons:{
            primary:"ui-icon-refresh"
        }
    }).on("click", function () {
            showOverlay();
            $.post("?app=APPMNG&action=APP_UPDATEALL", function () {
                hideOverlay();
                datatable.fnDraw();
            })
        });

    var showOverlay = function () {
        $(".ui-widget-overlay").show();
        $("#dataTable_processing").addClass("loadingImg").css('visibility', 'visible');
    };

    var hideOverlay = function () {
        $("#dataTable_processing").removeClass("loadingImg").css('visibility', 'hidden');
        $(".ui-widget-overlay").hide();
    };
});
