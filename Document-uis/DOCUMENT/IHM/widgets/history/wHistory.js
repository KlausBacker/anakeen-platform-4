define([
    'jquery',
    'underscore',
    'mustache',
    'kendo/kendo.core',
    'dcpDocument/widgets/widget',
    'dcpDocument/widgets/history/wRevisionDiff',
    'kendo/kendo.window',
    'dcpDocument/widgets/window/wDialog'
], function require_whistory($, _, Mustache, kendo)
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
                noOneNotice: "No one notices",
                filterMessages: "Filter messages",
                linkRevision: "See revision number #",
                historyTitle: "History for {{title}}",
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
            var widget = this, $widget = $(this);

            this.element.html(this.htmlCaneva());
            require(['datatables'], function dcpDocumentWHistory_initTable()
            {
                widget._initDatatable();
            });

            this.element.data("dcpDocumentHistory", this);

            this._super();

            this.element.on("click" + this.eventNamespace, ".history-button-showdetail", function whistoryShowDetail()
            {
                var noticeButton = widget.element.find(".history-button-shownotice");
                var noticeShowed = noticeButton.data("showNotice");
                if ($widget.data("showDetail")) {
                    $widget.data("showDetail", false);
                    $widget.data("showNotice", false);
                    $(this).text(widget.options.labels.showDetail).removeClass("btn-primary");
                    widget.element.find(".history-comment").hide();

                    noticeButton.attr("disabled", "disabled").removeClass("btn-primary").text(widget.options.labels.showNotice);
                } else {
                    $widget.data("showDetail", true);
                    widget.element.find(".history-comment").show();
                    if (!noticeShowed) {
                        widget.element.find(".history-level--notice").hide();
                    }
                    $(this).text(widget.options.labels.hideDetail).addClass("btn-primary");
                    noticeButton.removeAttr("disabled");
                }
            });
            this.element.on("click" + this.eventNamespace, ".history-button-shownotice", function whistoryShowNotice()
            {
                var $notices = widget.element.find(".history-level--notice");
                if ($widget.data("showNotice")) {
                    $widget.data("showNotice", false);
                    $(this).text(widget.options.labels.showNotice).removeClass("btn-primary");
                    widget.element.find(".history-level--notice").hide();
                } else {
                    $widget.data("showNotice", true);
                    if ($notices.length > 0) {
                        $notices.show();
                        $(this).text(widget.options.labels.hideNotice).addClass("btn-primary");
                    } else {

                        $(this).text(widget.options.labels.noOneNotice);
                    }

                }
            });
            this.element.on("click" + this.eventNamespace, ".history-diff-input", function whistoryShowDiff()
            {
                var selectedDiff = widget.element.find(".history-diff-input:checked");

                if (selectedDiff.length === 2) {
                    widget.element.find(".history-diff-input:not(:checked)").attr("disabled", "disabled");
                    var $diffTarget = $('<div class="revision-diff"/>');
                    var diffWidget = $diffTarget.dcpRevisionDiff({
                        documentId: widget.options.documentId,
                        firstRevision: $(selectedDiff.get(1)).data("revision"),
                        secondRevision: $(selectedDiff.get(0)).data("revision"),
                        window: {
                            width: "70%",
                            height: "70%",
                            title: widget.options.labels.revisionDiffLabels.title
                        },
                        labels: widget.options.labels.revisionDiffLabels
                    }).data("dcpRevisionDiff");

                    diffWidget.open();

                } else
                    if (selectedDiff.length < 2) {
                        widget.element.find(".history-diff-input").removeAttr("disabled", "disabled");
                    }
            });

            this.element.on("click" + this.eventNamespace, "a[data-document-id]", function whistoryShowDocument(event)
            {
                var docid = $widget.data("document-id");
                if (docid) {
                    event.preventDefault();
                    widget.element.trigger("viewRevision", {
                        initid: docid,
                        revision: parseInt($widget.data("revision"))
                    });
                }
            });
        },

        _fillDataTable: function dcpDocumentHistory_fillDataTable(data)
        {
            var myData = [];

            this.dialogWindow.setOptions({
                title:Mustache.render(this.options.labels.historyTitle,data.data.history[0].properties)
            });

            _.each(data.data.history, function whistoryFillRevision(revisionInfo)
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
                _.each(revisionInfo.messages, function whistoryFillMessage(message)
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
                        "render": function whistoryRenderDate(data)
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
                        render: function whistoryRenderMessage(data)
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
                        className: "history-owner",
                        render: function whistoryEncodeMessage(data) {
                             return $("<div/>").text(data).html();
                        }
                    },
                    {
                        data: "version",
                        name: "version",
                        title: historyWidget.options.labels.version,
                        className: "history-version",
                        visible: false,
                        render: function whistoryEncodeMessage(data) {
                            return $("<div/>").text(data).html();
                        }
                    },
                    {
                        data: "revision",
                        name: "revision",
                        title: historyWidget.options.labels.revision,
                        className: "history-revision",
                        render: function whistoryRenderRevision(data)
                        {
                            if (data !== '') {
                                return '<a class="history-revision-link btn btn-default" href="api/v1/documents/' +
                                    historyWidget.options.documentId +
                                    '/revisions/' + data + '.html"' +
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
                        visible: false,
                        render: function whistoryEncodeMessage(data) {
                            return $("<div/>").text(data).html();
                        }
                    },
                    {
                        data: "level",
                        name: "level",
                        title: historyWidget.options.labels.level,
                        className: "history-level",
                        visible: false,
                        render: function whistoryEncodeMessage(data) {
                            return $("<div/>").text(data).html();
                        }
                    },
                    {
                        data: "diff",
                        name: "diff",
                        title: historyWidget.options.labels.diff,
                        className: "history-diff",
                        render: function whistoryRenderDiff(data, renderType, allData)
                        {

                            if (data === 1) {
                                return '<input class="history-diff-input" data-revision="' + allData.revision + '" type="checkbox"/>';
                            } else {
                                return '';
                            }
                        }
                    }
                ],

                "drawCallback": function whistorydrawCallback()
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

                "initComplete": function whistoryinitComplete()
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
                        historyWidget.element.find(".dataTables_filter").each(function whistory_filter()
                        {
                            firstHeadCell.append($('<button class="history-button-showdetail btn btn-default btn-sm" >' + historyWidget.options.labels.showDetail + '</button>'));

                            firstHeadCell.append($('<button disabled="disabled" class="history-button-shownotice btn btn-default btn-sm" >' + historyWidget.options.labels.showNotice + '</button>'));

                        });
                        historyWidget.element.find(".dataTables_filter input").attr("placeholder", historyWidget.options.labels.filterMessages);
                        historyWidget.element.find(".row:nth-child(1) .col-sm-6").addClass("col-xs-6");
                    }

                },

                "ajax": function whistory_getData(data, callback)
                {

                    $.getJSON("api/v1/documents/" + historyWidget.options.documentId + '/history/').
                    done(function whistory_getDataDone(response)
                    {
                        var tableData = historyWidget._fillDataTable(response);
                        callback(
                            {data: tableData}
                        );
                    }).fail(function whistory_getDataFail(response)
                    {
                        var result = JSON.parse(response.responseText);
                        _.each(result.messages, function whistory_getDataParseMessage(error)
                        {
                            if (error.code === "CRUD0219" && error.uri) {
                                // redirect with the good trash uri
                                $.getJSON(error.uri.replace('.json', '') + '/history/').
                                done(function whistory_getDataRedirect(response)
                                {
                                    var tableData = historyWidget._fillDataTable(response);
                                    callback(
                                        {data: tableData}
                                    );
                                }).fail(function whistory_getDataFail(response)
                                {
                                    var result = JSON.parse(response.responseText);
                                    _.each(result.messages, function whistory_getDataFailParseMessage(error)
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