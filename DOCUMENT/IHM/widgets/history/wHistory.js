define([
    'underscore',
    'kendo/kendo.core',
    'widgets/widget',
    'widgets/history/wRevisionDiff',
    'kendo/kendo.window',
    'datatables',
    "datatables-bootstrap/dataTables.bootstrap"
], function (_, kendo) {
    'use strict';

    $.widget("dcp.dcpDocumentHistory", {
        options: {
            documentId: 0,
            window: {
                modal: true,
                animation: {
                    open: {
                        effects: "fade:in",
                        duration: 1000
                    }, close: {
                        effects: "fade:out",
                        duration: 1000
                    }
                },
                actions: [
                    "Maximize",
                    "Close"
                ],
                visible: false,
                height: "300px",
                width: "500px",
                title: "Document history"
            },
            labels: {
                version: "Version",
                revision: "Rev",
                state: "State",
                activity: "Activity",
                owner: "Owner",
                code: "Code",
                date: "Date",
                diff: "Diff",
                level: "Level",
                message: "Message",
                pastRevision: "",
                showDetail: "Show details",
                hideDetail: "Hide details",
                showNotice: "Show notices",
                hideNotice: "Hide notices",
                filterMessages: "Filter messages",
                linkRevision: "See revision number #"
            }
        },
        htmlCaneva: function () {
            return '<table class="history-main"><thead>' +
            '<tr class="history-header">' +
            '<th class="history-header--date"/>' +
            '<th class="history-header--message"/>' +
            '<th class="history-header--owner"/>' +
            '<th class="history-header--version"/>' +
            '<th class="history-header--revision"/>' +
            '<th class="history-header--code"/>' +
            '<th class="history-header--level"/>' +
            '<th class="history-header--diff"/>' +
            '</tr>' +
            '</thead></table>';
        },

        currentWidget: null,
        _create: function () {
            var scope = this;
            this.currentWidget = $('<div class="document-history"/>').html(this.htmlCaneva());


            this.element.append(this.currentWidget);
            this._initDatatable();
            this.element.data("dcpDocumentHistory", this);

            this.currentWidget.kendoWindow(this.options.window);

            this.currentWidget.on("click" + this.eventNamespace, ".history-button-showdetail", function () {
                var noticeButton = scope.currentWidget.find(".history-button-shownotice");
                var noticeShowed = noticeButton.data("showNotice");
                if ($(this).data("showDetail")) {
                    $(this).data("showDetail", false);
                    $(this).text(scope.options.labels.showDetail).removeClass("btn-primary");
                    scope.currentWidget.find(".history-comment").hide();

                    noticeButton.attr("disabled", "disabled").removeClass("btn-primary").text(scope.options.labels.showNotice);
                } else {
                    $(this).data("showDetail", true);
                    scope.currentWidget.find(".history-comment").show();
                    if (!noticeShowed) {
                        scope.currentWidget.find(".history-level--notice").hide();
                    }
                    $(this).text(scope.options.labels.hideDetail).addClass("btn-primary");
                    noticeButton.removeAttr("disabled");

                }
            });
            this.currentWidget.on("click" + this.eventNamespace, ".history-button-shownotice", function () {
                var detailShowed = scope.currentWidget.find(".history-button-showdetail").data("showDetail");
                if ($(this).data("showNotice")) {
                    $(this).data("showNotice", false);
                    $(this).text(scope.options.labels.showNotice).removeClass("btn-primary");
                    scope.currentWidget.find(".history-level--notice").hide();
                } else {
                    $(this).data("showNotice", true);
                    scope.currentWidget.find(".history-level--notice").show();
                    if (!detailShowed) {
                        scope.currentWidget.find(".history-comment").hide();
                    }
                    $(this).text(scope.options.labels.hideNotice).addClass("btn-primary");
                }
            });
            this.currentWidget.on("click" + this.eventNamespace, ".history-diff-input", function () {
                var selectedDiff = scope.currentWidget.find(".history-diff-input:checked");

                if (selectedDiff.length === 2) {
                    scope.currentWidget.find(".history-diff-input:not(:checked)").attr("disabled", "disabled");
                    var diffWidget = $('body').dcpRevisionDiff({
                        documentId: scope.options.documentId,
                        firstRevision: $(selectedDiff.get(1)).data("revision"),
                        secondRevision: $(selectedDiff.get(0)).data("revision"),
                        window: {
                            width: "70%",
                            height: "70%"
                        }
                    }).data("dcpRevisionDiff");

                    diffWidget.open();

                } else if (selectedDiff.length < 2) {
                    scope.currentWidget.find(".history-diff-input").removeAttr("disabled", "disabled");
                }
            });

            this.currentWidget.on("click" + this.eventNamespace, "a[data-document-id]", function (event) {
                var docid=$(this).data("document-id");
                if (window.dcp.document && docid) {
                    event.preventDefault();
                    window.dcp.document.clear();
                    window.dcp.document.set("revision", parseInt($(this).data("revision")));
                    window.dcp.document.set("initid", docid);
                    window.dcp.document.fetch();
                }
            });
        },
        open: function open() {
            this.currentWidget.data("kendoWindow").open();
            this.currentWidget.data("kendoWindow").center();
        },

        _fillDataTable: function (data) {
            var myData = [];
            _.each(data.data.history, function (revisionInfo) {
                myData.push({
                    "version": revisionInfo.properties.version,
                    "revision": revisionInfo.properties.revision,
                    "code": '',
                    "level": 'revision',
                    "message": revisionInfo.properties,
                    "owner": revisionInfo.properties.owner.title,
                    "date": revisionInfo.properties.revisionDate,
                    "diff": 1,
                    "color": revisionInfo.properties.state.color,
                    "DT_RowClass": "history-level--revision"
                });
                _.each(revisionInfo.messages, function (message) {
                    myData.push({
                        "version": '',
                        "revision": '',
                        "code": message.code,
                        "level": message.level,
                        "message": message.comment,
                        "owner": message.uname,
                        "date": message.date,
                        "diff": 0,
                        "DT_RowClass": "history-comment history-level--" + message.level + (revisionInfo.properties.status==="fixed" ? " history-comment--fixed" : "")
                    });
                });

            });
            return myData;
        },

        _initDatatable: function () {

            var historyWidget = this;
            this.element.find('.history-main').dataTable({
                "autoWidth": false,
                "ordering": false,
                "paging": false,
                // "scrollY": "200px",
                "scrollCollapse": false,
                "info": false,
                "language": {
                    "search": " "
                },
                "columns": [
                    {
                        data: "date",
                        name: "date",
                        title: historyWidget.options.labels.date,
                        className: "history-date",
                        "render": function (data) {

                            return kendo.toString(new Date(data.replace(' ', 'T')), "G");

                        }
                    },
                    {
                        data: "message",
                        name: "message",
                        title: historyWidget.options.labels.message,
                        className: "history-message",
                        render: function (data) {
                            if (_.isObject(data)) {
                                if (data.state.reference) {

                                    return '<div><span class="history-state-color" style="background-color:' + data.state.color + '" >&nbsp;</span>' +
                                    (data.status==="fixed" ? data.state.stateLabel : data.state.activity) +
                                    '</div>';
                                }
                                return $("<div/>").text(data.title).html();
                            } else {
                                return $("<div/>").text(data).html();
                            }
                        }
                    },
                    {
                        data: "owner",
                        name: "owner",
                        title: historyWidget.options.labels.owner,
                        className: "history-owner"
                    },
                    {
                        data: "version",
                        name: "version",
                        title: historyWidget.options.labels.version,
                        className: "history-version",
                        visible: false
                    },
                    {
                        data: "revision",
                        name: "revision",
                        title: historyWidget.options.labels.revision,
                        className: "history-revision",
                        render: function (data) {
                            if (data !== '') {
                                return '<a class="history-revision-link btn btn-default" href="?app=DOCUMENT&id=' +
                                historyWidget.options.documentId +
                                '&revision=' + data + '"' +
                                'data-document-id="'+historyWidget.options.documentId+'" '+
                                'data-revision="'+data+'"'+
                                '>' +
                                historyWidget.options.labels.linkRevision.replace('#', data) + '</a>';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: "code",
                        name: "code",
                        title: historyWidget.options.labels.code,
                        className: "history-code",
                        visible: false
                    },
                    {
                        data: "level",
                        name: "level",
                        title: historyWidget.options.labels.level,
                        className: "history-level",
                        visible: false
                    },
                    {
                        data: "diff",
                        name: "diff",
                        title: historyWidget.options.labels.diff,
                        className: "history-diff",
                        render: function (data, renderType, allData) {

                            if (data === 1) {
                                return '<input class="history-diff-input" data-revision="' + allData.revision + '" type="checkbox"/>';
                            } else {
                                return '';
                            }
                        }
                    }
                ],

                "drawCallback": function () {

                    var noticeShowed = historyWidget.currentWidget.find(".history-button-shownotice").data("showNotice");
                    var detailShowed = historyWidget.currentWidget.find(".history-button-showdetail").data("showDetail");

                    if (detailShowed) {
                        historyWidget.currentWidget.find(".history-comment").show();
                        if (!noticeShowed) {
                            historyWidget.currentWidget.find(".history-level--notice").hide();
                        }
                    } else {
                        historyWidget.currentWidget.find(".history-comment").hide();

                    }
                },
                "initComplete": function () {
                    var api = this.api();
                    var data = api.rows({page: 'current'}).data();
                    // Output the data for the visible rows to the browser's console

                    // show version if not null in one row
                    var showVersion = false;
                    var showState = false;
                    var onlyOneRevision = true;
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].version) {
                            showVersion = true;
                        }
                        if (data[i].revision > 0) {
                            onlyOneRevision = false;

                        }
                        if (data[i].message && data[i].message.state && data[i].message.state.reference) {
                            showState = true;
                        }
                    }
                    if (onlyOneRevision) {
                        //api.column("revision:name").visible(!onlyOneRevision);
                        historyWidget.currentWidget.find(".history-diff-input").attr("disabled", "disabled");
                    }


                    if (showVersion) {
                        api.column("version:name").visible(true);
                    }
                    if (showState) {
                        // Change Label
                        historyWidget.currentWidget.find(".history-header--message").html(historyWidget.options.labels.activity);
                    }
                    var fixedRevisionRow = historyWidget.currentWidget.find(".history-level--revision").get(1);
                    if (fixedRevisionRow) {
                        var trHead = historyWidget.currentWidget.find(".history-header").clone();
                        $('<tr class="history-separator"><td class="history-separator-cell" colspan="' + $(trHead).find('th').length + '"><span>' +
                        historyWidget.options.labels.pastRevision +
                        '</span>' +
                        '</td></tr>').insertBefore(fixedRevisionRow);
                        $(trHead).insertBefore(fixedRevisionRow);
                        if (showState) {
                            $($(".history-header--message").get(1)).html(historyWidget.options.labels.state);
                        }
                    }
                    historyWidget.currentWidget.find(".history-comment").hide();
                    historyWidget.currentWidget.find(".odd").removeClass("odd");
                    historyWidget.currentWidget.find(".even").removeClass("even");

                    if (historyWidget.currentWidget.find('.history-button-shownotice').length === 0) {
                        var firstHeadCell = historyWidget.currentWidget.find(".row:nth-child(1) .col-sm-6:nth-child(1)");
                        historyWidget.currentWidget.find(".dataTables_filter").each(function () {
                            firstHeadCell.append($('<button class="history-button-showdetail btn btn-default btn-sm" >' + historyWidget.options.labels.showDetail + '</button>'));

                            firstHeadCell.append($('<button disabled="disabled" class="history-button-shownotice btn btn-default btn-sm" >' + historyWidget.options.labels.showNotice + '</button>'));

                        });
                        historyWidget.currentWidget.find(".dataTables_filter input").attr("placeholder", historyWidget.options.labels.filterMessages);
                        // historyWidget.currentWidget.find(".row:nth-child(1) .col-sm-6:nth-child(1)").append(historyWidget.currentWidget.find(".dataTables_filter"));
                    }

                },


                "ajax": function (data, callback) {
                    var myData = [];
                    $.getJSON("api/v1/documents/" + historyWidget.options.documentId + '/history/').
                        done(function (response) {
                            var tableData = historyWidget._fillDataTable(response);
                            callback(
                                {data: tableData}
                            );
                        }).fail(function (response) {
                            var result = JSON.parse(response.responseText);
                            _.each(result.messages, function (error) {
                                if (error.code === "CRUD0219" && error.uri) {
                                    console.log("need retry with", error.uri);
                                    // redirect with the good trash uri
                                    $.getJSON(error.uri.replace('.json','') + '/history/').
                                        done(function (response) {
                                            var tableData = historyWidget._fillDataTable(response);
                                            callback(
                                                {data: tableData}
                                            );
                                        }).fail(function (response) {
                                            var result = JSON.parse(response.responseText);
                                            _.each(result.messages, function (error) {
                                                if (error.type === "error") {
                                                    $('body').trigger("notification", {
                                                        type: error.type,
                                                        message: error.contentText
                                                    });
                                                }
                                            });
                                            console.log("fail", response);
                                        });
                                } else if (error.type === "error") {
                                    $('body').trigger("notification", {type: error.type, message: error.contentText});
                                }
                            });
                            console.log("fail", response);
                        });
                }
            }).addClass('table table-condensed table-bordered table-hover');

        },

        _destroy : function _destroy() {
            var $history = this.element.find('.history-main');
            if (this.currentWidget && this.currentWidget.data("kendoWindow")) {
                this.currentWidget.data("kendoWindow").destroy();
            }
            if ($history.DataTable) {
                $history.DataTable().destroy();
            }
            this._super();
        }

    });
});