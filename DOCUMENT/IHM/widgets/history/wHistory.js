define([
    'underscore',
    'widgets/widget',
    'kendo/kendo.window',
    'datatables',
    "datatables-bootstrap/dataTables.bootstrap"
], function (_) {
    'use strict';

    $.widget("dcp.dcpDocumentHistory", {
        options: {
            documentId: 0,
            modal: false,
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
            title: "Document history",

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
                pastRevision: "Past Revisions",
                showDetail: "Show detail",
                hideDetail: "Hide detail",
                showNotice: "Show notice",
                hideNotice: "Hide notice",
                filterMessages: "Filter messages"
            }
        },
        htmlCaneva: function () {
            return '<table class="current-history"><thead>' +
            this._getInnerTableHead() +
            '</thead></table>';
        },

        _getInnerTableHead: function () {
            return '<tr class="history-header">' +
            '<th class="history-header-version">' + this.options.labels.version + '</th>' +
            '<th class="history-header-revision">' + this.options.labels.revision + '</th>' +
            '<th class="history-header-code">' + this.options.labels.code + '</th>' +
            '<th class="history-header-level">' + this.options.labels.level + '</th>' +
            '<th class="history-header-message">' + this.options.labels.message + '</th>' +
            '<th class="history-header-owner">' + this.options.labels.owner + '</th>' +
            '<th class="history-header-date">' + this.options.labels.date + '</th>' +
            '<th class="history-header-diff">' + this.options.labels.diff + '</th>' +
            '</tr>';
        },

        currentWidget: null,
        _create: function () {
            var scope=this;
            this.currentWidget = $('<div class="document-history"/>').html(this.htmlCaneva());


            this.element.append(this.currentWidget);
            this._initDatatable();
            this.element.data("dcpDocumentHistory", this);

            this.currentWidget.kendoWindow(this.options);

            this.currentWidget.on("click", ".history-button-showdetail", function (event) {
                var noticeShowed=scope.currentWidget.find(".history-button-shownotice").data("showNotice");
                if ($(this).data("showDetail")) {
                    $(this).data("showDetail", false);
                    $(this).val(scope.options.labels.showDetail);
                    scope.currentWidget.find(".history-comment--fixed").hide();
                } else {
                    $(this).data("showDetail", true);
                    scope.currentWidget.find(".history-comment--fixed").show();
                    if (!noticeShowed) {
                        scope.currentWidget.find(".history-level--notice").hide();
                    }
                    $(this).val(scope.options.labels.hideDetail);

                }
            });
            this.currentWidget.on("click", ".history-button-shownotice", function (event) {
                var detailShowed=scope.currentWidget.find(".history-button-showdetail").data("showDetail");
                if ($(this).data("showNotice")) {
                    $(this).data("showNotice", false);
                    $(this).val(scope.options.labels.showNotice);
                    scope.currentWidget.find(".history-level--notice").hide();
                } else {
                    $(this).data("showNotice", true);
                    scope.currentWidget.find(".history-level--notice").show();
                    if (! detailShowed) {
                        scope.currentWidget.find(".history-comment--fixed").hide();
                    }
                    $(this).val(scope.options.labels.hideNotice);
                }
            });
        },
        open: function open() {
            this.currentWidget.data("kendoWindow").open();
            this.currentWidget.data("kendoWindow").center();
        },

        _initDatatable: function () {

            var historyWidget = this;
            $('.current-history').dataTable({
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
                    {"data": "version"},
                    {"data": "revision"},
                    {"data": "code"},
                    {"data": "level"},
                    {"data": "message"},
                    {"data": "owner"},
                    {"data": "date"},
                    {"data": "diff"}
                ],
                "columnDefs": [{
                    "targets": [0, 2, 3],
                    "visible": false
                }, {
                    "targets": [0],
                    "sClass": "history-version"
                }, {
                    "targets": [1],
                    "sClass": "history-revision"
                }, {
                    "targets": [2],
                    "sClass": "history-code"
                }, {
                    "targets": [3],
                    "sClass": "history-level"
                }, {
                    "targets": 4,
                    "sClass": "history-message",
                    "render": function (data, type, full) {
                        if (_.isObject(data)) {
                            if (data.state.reference) {

                                return '<div><span class="history-state-color" style="background-color:' + data.state.color + '" >&nbsp;</span>' +
                                (data.fixed ? data.state.stateLabel : data.state.activity) +
                                '</div>';
                            }
                            return $("<div/>").text(data.title).html();
                        } else {
                            return $("<div/>").text(data).html();
                        }
                    }
                }, {
                    "targets": [5],
                    "sClass": "history-owner"
                }, {
                    "targets": [6],
                    "sClass": "history-date"
                }, {
                    "targets": [7],
                    "sClass": "history-diff"
                }],
                "headerCallback": function( thead, data, start, end, display ) {
                    console.log("headerCallback", $(thead).find('.dataTables_filter'));
                },
                "drawCallback": function (settings) {
                    var api = this.api();
                    var data = api.rows({page: 'current'}).data();
                    // Output the data for the visible rows to the browser's console

                    // show version if not null in one row
                    var showVersion = false;
                    var showState = false;
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].version) {
                            showVersion = true;
                        }

                        if (data[i].message && data[i].message.state && data[i].message.state.reference) {
                            showState = true;
                        }
                    }
                    if (showVersion) {
                        api.column(0).visible(true);
                    }
                    if (showState) {
                        // Change Label
                        historyWidget.currentWidget.find(".history-header-message").html(historyWidget.options.labels.activity);
                    }
                    var fixedRevisionRow = historyWidget.currentWidget.find(".history-level--revision").get(1);
                    if (fixedRevisionRow) {
                        var trHead = historyWidget.currentWidget.find(".history-header").clone();
                        $('<tr class="history-separator"><td class="history-separator-cell" colspan="' + $(trHead).find('th').length + '"><span>' +
                        historyWidget.options.labels.pastRevision +
                        '</span><input type="button" class="history-button-showdetail btn btn-primary" value="' + historyWidget.options.labels.showDetail + '"/></td></tr>').insertBefore(fixedRevisionRow);
                        $(trHead).insertBefore(fixedRevisionRow);
                        if (showState) {
                            $($(".history-header-message").get(1)).html(historyWidget.options.labels.state);
                        }
                    }
                    historyWidget.currentWidget.find(".history-comment--fixed").hide();
                    historyWidget.currentWidget.find(".history-level--notice").hide();
                    historyWidget.currentWidget.find(".odd").removeClass("odd");
                    historyWidget.currentWidget.find(".even").removeClass("even");

                    if (historyWidget.currentWidget.find( '.history-button-shownotice').length === 0) {
                        historyWidget.currentWidget.find(".dataTables_filter").each(function () {
                            $('<input type="button" class="history-button-shownotice btn btn-primary" value="' + historyWidget.options.labels.showNotice + '"/>').insertAfter($(this));
                        });
                        historyWidget.currentWidget.find(".dataTables_filter input").attr("placeholder",historyWidget.options.labels.filterMessages );
                        historyWidget.currentWidget.find(".row:nth-child(1) .col-sm-6:nth-child(1)").append(historyWidget.currentWidget.find(".dataTables_filter"));
                    }

                },
                "ajax": {
                    "url": "api/v1/documents/" + this.options.documentId + '/history/',
                    "dataSrc": function (json) {
                        var data = [];
                        _.each(json.data.history, function (revisionInfo) {
                            data.push({
                                "version": revisionInfo.version,
                                "revision": revisionInfo.revision,
                                "code": '',
                                "level": 'revision',
                                "message": revisionInfo,
                                "owner": revisionInfo.owner.title,
                                "date": revisionInfo.revisionDate,
                                "diff": 1,
                                "color": revisionInfo.state.color,
                                "DT_RowClass": "history-level--revision"
                            });
                            _.each(revisionInfo.messages, function (message) {
                                data.push({
                                    "version": '',
                                    "revision": '',
                                    "code": message.code,
                                    "level": message.level,
                                    "message": message.comment,
                                    "owner": message.uname,
                                    "date": message.date,
                                    "diff": 0,
                                    "DT_RowClass": "history-comment history-level--" + message.level + (revisionInfo.fixed ? " history-comment--fixed" : "")
                                });
                            });

                        });
                        return data;
                    }
                }
            }).addClass('table table-condensed table-bordered');

        }

    });
});