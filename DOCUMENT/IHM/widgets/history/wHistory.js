define([
    'jquery',
    'underscore',
    'kendo/kendo.core',
    'dcpDocument/widgets/widget',
    'dcpDocument/widgets/history/wRevisionDiff',
    'kendo/kendo.window',
    'dcpDocument/widgets/window/wDialog',
    'datatables',
    "datatables-bootstrap/dataTables.bootstrap"
], function ($, _, kendo)
{
    'use strict';

    $.widget("dcp.dcpDocumentHistory", $.dcp.dcpDialog, {
        options: {
            documentId: 0,
            window: {
                modal: true,
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
                linkRevision: "See revision number #",
                loading: "Loading ...",
                revisionDiffLabels: {}
            }
        },
        htmlCaneva: function dcpDocumentHistoryhtmlCaneva()
        {
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

        element: null,
        _create: function dcpDocumentHistory_create()
        {
            var scope = this;


            this.element.html(this.htmlCaneva());
            this._initDatatable();
            this.element.data("dcpDocumentHistory", this);


            this._super();

            this.element.on("click" + this.eventNamespace, ".history-button-showdetail", function ()
            {
                var noticeButton = scope.element.find(".history-button-shownotice");
                var noticeShowed = noticeButton.data("showNotice");
                if ($(this).data("showDetail")) {
                    $(this).data("showDetail", false);
                    $(this).text(scope.options.labels.showDetail).removeClass("btn-primary");
                    scope.element.find(".history-comment").hide();

                    noticeButton.attr("disabled", "disabled").removeClass("btn-primary").text(scope.options.labels.showNotice);
                } else {
                    $(this).data("showDetail", true);
                    scope.element.find(".history-comment").show();
                    if (!noticeShowed) {
                        scope.element.find(".history-level--notice").hide();
                    }
                    $(this).text(scope.options.labels.hideDetail).addClass("btn-primary");
                    noticeButton.removeAttr("disabled");

                }
            });
            this.element.on("click" + this.eventNamespace, ".history-button-shownotice", function ()
            {
                var detailShowed = scope.element.find(".history-button-showdetail").data("showDetail");
                if ($(this).data("showNotice")) {
                    $(this).data("showNotice", false);
                    $(this).text(scope.options.labels.showNotice).removeClass("btn-primary");
                    scope.element.find(".history-level--notice").hide();
                } else {
                    $(this).data("showNotice", true);
                    scope.element.find(".history-level--notice").show();
                    if (!detailShowed) {
                        scope.element.find(".history-comment").hide();
                    }
                    $(this).text(scope.options.labels.hideNotice).addClass("btn-primary");
                }
            });
            this.element.on("click" + this.eventNamespace, ".history-diff-input", function ()
            {
                var selectedDiff = scope.element.find(".history-diff-input:checked");

                if (selectedDiff.length === 2) {
                    scope.element.find(".history-diff-input:not(:checked)").attr("disabled", "disabled");
                    var $diffTarget = $('<div class="revision-diff"/>');
                    var diffWidget = $diffTarget.dcpRevisionDiff({
                        documentId: scope.options.documentId,
                        firstRevision: $(selectedDiff.get(1)).data("revision"),
                        secondRevision: $(selectedDiff.get(0)).data("revision"),
                        window: {
                            width: "70%",
                            height: "70%",
                            title: scope.options.labels.revisionDiffLabels.title
                        },
                        labels: scope.options.labels.revisionDiffLabels
                    }).data("dcpRevisionDiff");

                    diffWidget.open();

                } else
                    if (selectedDiff.length < 2) {
                        scope.element.find(".history-diff-input").removeAttr("disabled", "disabled");
                    }
            });

            this.element.on("click" + this.eventNamespace, "a[data-document-id]", function (event)
            {
                var docid = $(this).data("document-id");
                if (docid) {
                    event.preventDefault();
                    scope.element.trigger("viewRevision", {
                        initid: docid,
                        revision: parseInt($(this).data("revision"))
                    });
                }
            });
        },


        _fillDataTable: function dcpDocumentHistory_fillDataTable(data)
        {
            var myData = [];
            _.each(data.data.history, function (revisionInfo)
            {
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
                _.each(revisionInfo.messages, function (message)
                {
                    myData.push({
                        "version": '',
                        "revision": '',
                        "code": message.code,
                        "level": message.level,
                        "message": message.comment,
                        "owner": message.uname,
                        "date": message.date,
                        "diff": 0,
                        "DT_RowClass": "history-comment history-level--" + message.level + (revisionInfo.properties.status === "fixed" ? " history-comment--fixed" : "")
                    });
                });

            });
            return myData;
        },

        _initDatatable: function dcpDocumentHistory_initDatatable()
        {

            var historyWidget = this;
            this.element.find('.history-main').dataTable({
                "autoWidth": false,
                "ordering": false,
                "paging": false,
                // "scrollY": "200px",
                "scrollCollapse": false,
                "info": false,
                "language": {
                    "search": " ",
                    "loadingRecords": this.options.labels.loading
                },
                "columns": [
                    {
                        data: "date",
                        name: "date",
                        title: historyWidget.options.labels.date,
                        className: "history-date",
                        "render": function (data)
                        {
                            var theDate = new Date(data.substr(0, 10));
                            // The time is not manage by date because each navigator defer with timezone
                            return kendo.toString(theDate, "D") + ' ' + data.substr(11, 8);
                        }
                    },
                    {
                        data: "message",
                        name: "message",
                        title: historyWidget.options.labels.message,
                        className: "history-message",
                        render: function (data)
                        {
                            if (_.isObject(data)) {
                                if (data.state.reference) {

                                    return '<div><span class="history-state-color" style="background-color:' + data.state.color + '" >&nbsp;</span>' +
                                    (data.status === "fixed" ? data.state.stateLabel : data.state.activity) +
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
                        render: function (data)
                        {
                            if (data !== '') {
                                return '<a class="history-revision-link btn btn-default" href="?app=DOCUMENT&id=' +
                                historyWidget.options.documentId +
                                '&revision=' + data + '"' +
                                'data-document-id="' + historyWidget.options.documentId + '" ' +
                                'data-revision="' + data + '"' +
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
                        render: function (data, renderType, allData)
                        {

                            if (data === 1) {
                                return '<input class="history-diff-input" data-revision="' + allData.revision + '" type="checkbox"/>';
                            } else {
                                return '';
                            }
                        }
                    }
                ],

                "drawCallback": function ()
                {

                    var noticeShowed = historyWidget.element.find(".history-button-shownotice").data("showNotice");
                    var detailShowed = historyWidget.element.find(".history-button-showdetail").data("showDetail");

                    if (detailShowed) {
                        historyWidget.element.find(".history-comment").show();
                        if (!noticeShowed) {
                            historyWidget.element.find(".history-level--notice").hide();
                        }
                    } else {
                        historyWidget.element.find(".history-comment").hide();

                    }
                },
                "initComplete": function ()
                {
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
                        historyWidget.element.find(".history-diff-input").attr("disabled", "disabled");
                    }


                    if (showVersion) {
                        api.column("version:name").visible(true);
                    }
                    if (showState) {
                        // Change Label
                        historyWidget.element.find(".history-header--message").html(historyWidget.options.labels.activity);
                    }
                    var fixedRevisionRow = historyWidget.element.find(".history-level--revision").get(1);
                    if (fixedRevisionRow) {
                        var trHead = historyWidget.element.find(".history-header").clone();
                        $('<tr class="history-separator"><td class="history-separator-cell" colspan="' + $(trHead).find('th').length + '"><span>' +
                        historyWidget.options.labels.pastRevision +
                        '</span>' +
                        '</td></tr>').insertBefore(fixedRevisionRow);
                        $(trHead).insertBefore(fixedRevisionRow);
                        if (showState) {
                            $($(".history-header--message").get(1)).html(historyWidget.options.labels.state);
                        }
                    }
                    historyWidget.element.find(".history-comment").hide();
                    historyWidget.element.find(".odd").removeClass("odd");
                    historyWidget.element.find(".even").removeClass("even");

                    if (historyWidget.element.find('.history-button-shownotice').length === 0) {
                        var firstHeadCell = historyWidget.element.find(".row:nth-child(1) .col-sm-6:nth-child(1)");
                        historyWidget.element.find(".dataTables_filter").each(function ()
                        {
                            firstHeadCell.append($('<button class="history-button-showdetail btn btn-default btn-sm" >' + historyWidget.options.labels.showDetail + '</button>'));

                            firstHeadCell.append($('<button disabled="disabled" class="history-button-shownotice btn btn-default btn-sm" >' + historyWidget.options.labels.showNotice + '</button>'));

                        });
                        historyWidget.element.find(".dataTables_filter input").attr("placeholder", historyWidget.options.labels.filterMessages);
                        historyWidget.element.find(".row:nth-child(1) .col-sm-6").addClass("col-xs-6");
                    }

                },


                "ajax": function (data, callback)
                {

                    $.getJSON("api/v1/documents/" + historyWidget.options.documentId + '/history/').
                        done(function (response)
                        {
                            var tableData = historyWidget._fillDataTable(response);
                            callback(
                                {data: tableData}
                            );
                        }).fail(function (response)
                        {
                            var result = JSON.parse(response.responseText);
                            _.each(result.messages, function (error)
                            {
                                if (error.code === "CRUD0219" && error.uri) {
                                    // redirect with the good trash uri
                                    $.getJSON(error.uri.replace('.json', '') + '/history/').
                                        done(function (response)
                                        {
                                            var tableData = historyWidget._fillDataTable(response);
                                            callback(
                                                {data: tableData}
                                            );
                                        }).fail(function (response)
                                        {
                                            var result = JSON.parse(response.responseText);
                                            _.each(result.messages, function (error)
                                            {
                                                if (error.type === "error") {
                                                    $('body').trigger("notification", {
                                                        type: error.type,
                                                        message: error.contentText
                                                    });
                                                }
                                            });
                                            console.error("fail", response);
                                        });
                                } else
                                    if (error.type === "error") {
                                        $('body').trigger("notification", {
                                            type: error.type,
                                            message: error.contentText
                                        });
                                    }
                            });
                            console.error("fail", response);
                        });
                }
            }).addClass('table table-condensed table-bordered table-hover');

        },

        _destroy: function dcpDocumentHistory_destroy()
        {
            var $history = this.element.find('.history-main');

            if ($history.DataTable) {
                $history.DataTable().destroy();
            }
            this._super();
        }

    });
});