var TENGINE_MONITOR = TENGINE_MONITOR || {};

TENGINE_MONITOR.taskMonitor = (function () {
    var __CLASS__ = function () {
        this.serverApiOk = true;
        this.url = "?app=TENGINE_MONITOR&action=TENGINE_MONITOR_TASKS";
        this.logtask = [];
        this.logbook = {};
    };

    __CLASS__.prototype = {
        'serverApiOk': true,
        'url': "?app=TENGINE_MONITOR&action=TENGINE_MONITOR_TASKS",
        'logtask': [],
        'logbook': {},
        /**
         *
         * @param tid
         * @param target
         */
        'displayLogTask': function (tid, target) {
            for (var il = 0; il < this.logtask[tid].length; il++) {
                $('<div />').append($('<span />').addClass('date').html(this.logtask[tid][il].date))
                    .append($('<span />').html(this.logtask[tid][il].comment))
                    .appendTo(target);
            }
        },
        /**
         *
         * @param tid
         * @param target
         * @param data
         * @returns {Function}
         */
        'histoHandler': function (tid, target, data) {
            if (data == undefined) {
                $('<div />').html("[TEXT:TE:Monitor:server communication error]" + "<br/>" + "[TEXT:TE:Monitor:Oooops, something wrong happens]").addClass('alert').appendTo(target);
            } else if (data.success) {
                this.logtask[tid] = data.data;
                this.displayLogTask(tid, target);
            } else {
                $('<div />').html("[TEXT:TE:Monitor:server communication error]" + "<br/>" + data.message).addClass('alert').appendTo(target);
            }
        },
        "canCancelTaskWithStatus": function (status) {
            switch (status) {
                case 'B':
                case 'T':
                case 'W':
                case 'P':
                    return true;
            }
            return false;
        },
        "canDeleteTaskWithStatus": function (status) {
            switch (status) {
                case 'K':
                case 'D':
                case 'I':
                    return true;
            }
            return false;
        },
        /**
         *
         * @param oTable
         * @param nTr
         * @returns {*|jQuery|HTMLElement}
         */
        'fnFormatDetails': function (oTable, nTr) {
            var aData = oTable.fnGetData(nTr);
            var sOut = $('<div />');

            var actions = $('<div />').addClass('actions').appendTo(sOut);
            if (this.canCancelTaskWithStatus(aData.status)) {
                actions.append(
                    $('<a />')
                        .addClass('paginate_button')
                        .attr('title', "[TEXT:TE:Monitor:Tasks:stop task processing and clean working datas]")
                        .html("[TEXT:TE:Monitor:Tasks:Abort]")
                        .on('click', {monitor: this}, function (event) {
                            event.stopPropagation();
                            $.ajax({
                                url: event.data.monitor.url + '&op=abort&tid=' + aData.tid,
                                type: "GET",
                                context: event.data.monitor,
                                success: function (data) {
                                    if (data.success) {
                                        globalMessage.show('[TEXT:TE:Monitor:Tasks:task successfully aborted] (' + aData.tid + ')', 'info');
                                        this.logbook.fnDraw();
                                    } else {
                                        globalMessage.show("[TEXT:TE:Monitor:Tasks:task aborting fails]" + '<br/>' + data.message, 'warning');
                                    }
                                },
                                error: function () {
                                    globalMessage.show("[TEXT:TE:Monitor:Tasks:abort command execution fails]" + '<br/>' + data.message, 'error');
                                }
                            });
                        })
                );
            }
            if (this.canDeleteTaskWithStatus(aData.status)) {
                actions.append(
                    $('<a />')
                        .addClass('paginate_button')
                        .attr('title', "[TEXT:TE:Monitor:Tasks:delete task]")
                        .html("[TEXT:TE:Monitor:Tasks:Delete]")
                        .on('click', {monitor: this}, function (event) {
                            event.stopPropagation();
                            $.ajax({
                                url: event.data.monitor.url + '&op=delete&tid=' + aData.tid,
                                type: "GET",
                                context: event.data.monitor,
                                success: function (data) {
                                    if (data.success) {
                                        globalMessage.show('[TEXT:TE:Monitor:Tasks:task successfully deleted] (' + aData.tid + ')', 'info');
                                        this.logbook.fnDraw();
                                    } else {
                                        globalMessage.show("[TEXT:TE:Monitor:Tasks:task deletion failed]" + '<br/>' + data.message, 'warning');
                                    }
                                },
                                error: function () {
                                    globalMessage.show("[TEXT:TE:Monitor:Tasks:delete command execution failed]" + '<br/>' + data.message, 'error');
                                }
                            });
                        })
                );
            }

            var target = $('<div />').attr('name', "H" + aData.tid).addClass('log')
                .append($('<div />').addClass('title').html('[TEXT:TE:Monitor:Tasks:Log]'))
                .appendTo(sOut);

            var dData = $('<div />').addClass('data')
                .append($('<div />').addClass('title').html('[TEXT:TE:Monitor:Tasks:Datas]'))
                .appendTo(sOut);
            for (var key in aData) {
                if (aData.hasOwnProperty(key) && key != "0") {
                    $('<div />').
                        appendTo(dData)
                        .append(
                        $('<div />')
                            .append($('<span />').addClass('key').html(key))
                            .append($('<span />').addClass('value').html(aData[key]))
                    );
                }
            }
            if (this.logtask[aData.tid] == undefined) {
                // Start history loading
                $.ajax({
                    url: this.url + '&op=histo&tid=' + aData.tid,
                    type: "GET",
                    context: {
                        monitor: this,
                        target: target,
                        tid: aData.tid
                    },
                    success: function (data, textStatus, jqXHR) {
                        this.monitor.histoHandler(this.tid, this.target, data)
                    },
                    error: function (data) {
                        this.monitor.histoHandler(this.tid, this.target, data)
                    }
                });
            } else {
                this.displayLogTask(aData.tid, target);
            }
            return sOut;
        },
        /**
         *
         * @returns {*|jQuery}
         */
        'loadDatatable': function () {
            var _this = this;
            return $('#tasks-dt').dataTable(
                {
                    sDom: '<"top"pi<"clear">>rt<"bottom"pl><"clear">',
                    bAutoWidth: false,
                    oLanguage: {
                        sLengthMenu: "[TEXT:TE:Monitor:Tasks:Display _MENU_ tasks per page]",
                        sInfo: "[TEXT:TE:Monitor:Tasks:Showing _START_ to _END_ of _TOTAL_ records]",
                        sInfoEmpty: "[TEXT:TE:Monitor:Tasks:Showing 0 to 0 of 0 records]",
                        sInfoFiltered: "[TEXT:TE:Monitor:Tasks:(filtered from _MAX_ total records)]",
                        sLoadingRecords: "<i class=\"fa fa-goc fa-spinner\"></i> [TEXT:TE:Monitor:Tasks:sLoadingRecords]",
                        sProcessing: "[TEXT:TE:Monitor:Tasks:sProcessing]",
                        sSearch: "[TEXT:TE:Monitor:Tasks:sSearch]",
                        sZeroRecords: "[TEXT:TE:Monitor:Tasks:sZeroRecords]",
                        oPaginate: {
                            sFirst: "[TEXT:TE:Monitor:Tasks:sFirst]",
                            sLast: "[TEXT:TE:Monitor:Tasks:sLast]",
                            sNext: "[TEXT:TE:Monitor:Tasks:sNext]",
                            sPrevious: "[TEXT:TE:Monitor:Tasks:sPrevious]"
                        }
                    },
                    iDisplayLength: 25,
                    aLengthMenu: [[25, 50, 100], [25, 50, 100]],
                    sPaginationType: "full_numbers",
                    bServerSide: (function () {
                        return _this.serverApiOk
                    })(), // true
                    bFilter: true,
                    bProcessing: true,
                    sAjaxSource: (function () {
                        return (_this.serverApiOk ? _this.url + '&op=tasks' : null)
                    })(),
                    aaData: (function () {
                        return (_this.serverApiOk ? null : [] )
                    })(),
                    bDestroy: (function () {
                        return _this.serverApiOk
                    })(),
                    fnServerData: function (sSource, aoData, fnCallback) {
                        $.ajax({
                            dataType: 'json',
                            type: "POST",
                            url: sSource,
                            data: aoData,
                            context: {
                                'monitor': _this,
                                'fnCallback': fnCallback
                            },
                            success: function (data, textStatus, jqXHR) {
                                this.monitor.logbook.fnSettings().oLanguage.sEmptyTable = "[TEXT:TE:Monitor:no tasks]" + "<br/>" + data.message;
                                this.fnCallback(data, textStatus, jqXHR);
                                this.monitor.refreshCounters();
                            }
                        });
                    },
                    aaSorting: [
                        [0, 'desc']
                    ],
                    aoColumnDefs: [
                        {
                            aTargets: [0],
                            mDataProp: "cdate",
                            sClass: "date"
                        },
                        {
                            aTargets: [1],
                            mDataProp: "statuslabel",
                            fnRender: function (o) {
                                /*
                                 var rhtml = '<span class="state flag flag_'+o.aData.status+'" '
                                 +       '       title="'+o.aData.statuslabel+'" >'
                                 +       '   <span class="flag-color">'+o.aData.status+'</span> '
                                 +       '   <span class="flag-label">'+o.aData.statuslabel+'</span>'
                                 +       '</span>';
                                 */
                                var rhtml = '<span class="state flag flag_' + o.aData.status + '" '
                                    + '       title="' + o.aData.statuslabel + '" >'
                                    + '   <span class="flag-color">&nbsp;</span> '
                                    + '   <span class="flag-label">' + o.aData.statuslabel + '</span>'
                                    + '</span>';
                                return (rhtml);
                            }
                        },
                        {
                            aTargets: [2],
                            mDataProp: "owner",
                            bSortable: false
                        },
                        {
                            aTargets: [3],
                            mDataProp: "doctitle",
                            bSortable: false,
                            fnRender: function (o) {
                                if (o.aData.doctitle == '') return '';
                                var rhtml = '[' + o.aData.docid + '] ' + o.aData.doctitle;
                                return (rhtml);
                            }
                        },
                        {
                            aTargets: [4],
                            mDataProp: "engine"
                        },
                        {
                            aTargets: [5],
                            mDataProp: "tid",
                            bUseRendered: false,
                            fnRender: function (o) {
                                if (o.aData.tid == undefined || o.aData.tid == '') return '';
                                var tids = o.aData.tid.split('.');
                                var rhtml = '<span title="' + o.aData.tid + '">' + tids[0] + '…</span>';
                                return rhtml;
                            }
                        }
                    ]
                });
        },
        /**
         *
         * @param counter
         * @param val
         */
        'setCounter': function (counter, val) {
            if (val == undefined) val = '0';
            $('[data-counter="' + counter + '"]').html(val);
        },
        /**
         *
         */
        'refreshCounters': function () {
            $.ajax({
                url: "?app=TENGINE_CLIENT&action=TENGINE_CLIENT_INFOS",
                type: "GET",
                context: this,
                success: function (data) {
                    if (data.success) {
                        this.setCounter("sent", data.info.status_breakdown.S);
                        this.setCounter("interrupted", data.info.status_breakdown.I);
                        this.setCounter("ko", data.info.status_breakdown.K);
                        this.setCounter("done", data.info.status_breakdown.D);
                        this.setCounter("transferring", data.info.status_breakdown.T);
                        this.setCounter("processing", data.info.status_breakdown.P);
                        this.setCounter("waiting", data.info.status_breakdown.W);
                        this.setCounter("client-cur", data.info.cur_client);
                        this.setCounter("client-max", data.info.max_client);
                        this.setCounter("sys-load-1", data.info.load[0]);
                        this.setCounter("sys-load-2", data.info.load[1]);
                        this.setCounter("sys-load-3", data.info.load[2]);
                    } else {
                        this.resetCounters();
                    }
                },
                error: function (data) {
                    this.resetCounters();
                }
            });
        },
        /**
         *
         */
        'resetCounters': function () {
            this.setCounter("interrupted", '-');
            this.setCounter("ko", '-');
            this.setCounter("done", '-');
            this.setCounter("processing", '-');
            this.setCounter("transferring", '-');
            this.setCounter("waiting", '-');
            this.setCounter("client-cur", '-');
            this.setCounter("client-max", '-');
            this.setCounter("sys-load-1", '-');
            this.setCounter("sys-load-2", '-');
            this.setCounter("sys-load-3", '-');
        },
        /**
         *
         */
        'run': function () {
            var _this = this;

            this.logbook = this.loadDatatable();

            $('#tasks-dt tbody').on('click', 'td', {monitor: this}, function (event) {
                if ($(this).hasClass('details')) return;
                var nTr = $(this).parents('tr')[0];
                var onTr = $(this).parent();
                var initStateOpen = event.data.monitor.logbook.fnIsOpen(nTr);
                $(".logbook tbody tr").removeClass('minus');
                $(".logbook tbody tr[estate='open']").each(function () {
                    event.data.monitor.logbook.fnClose($(this)[0]);
                });
                if (!initStateOpen) {
                    $(".logbook tbody tr").addClass('minus');
                    onTr.attr('estate', 'open').removeClass('minus');
                    event.data.monitor.logbook.fnOpen(nTr, event.data.monitor.fnFormatDetails(event.data.monitor.logbook, nTr), 'details');
                }
            });

            $("thead tr input, thead tr select")
                .on('click', function (event) {
                    event.stopPropagation();
                })
                .on('change', {monitor: this}, function (event) {
                    event.data.monitor.logbook.fnFilter(this.value, $("thead tr .search-input").index(this));
                });

            $("thead tr input").
                on('keyup', {monitor: this}, function (event) {
                    if (event.keyCode == 13 && this.value != "") {
                        event.data.monitor.logbook.fnFilter(this.value, $("thead tr .search-input").index(this));
                    }
                });

            // _this.logbook = _this.loadDatatable();

            $('#reset-filters').on('click', {monitor: _this}, function (event) {
                $("thead tr .search-init").each(function () {
                    switch ($(this)[0].tagName) {
                        case 'SELECT':
                            $(this).val('');
                            break;
                        case 'INPUT':
                            $(this).val('');
                            break;
                        default:
                    }
                });
                var oSettings = event.data.monitor.logbook.fnSettings();
                for (var iCol = 0; iCol < oSettings.aoPreSearchCols.length; iCol++) {
                    oSettings.aoPreSearchCols[iCol].sSearch = '';
                }
                event.data.monitor.logbook.fnDraw();
            });
        }
    };

    return __CLASS__;
})();

$(document).ready(function () {
    var taskMonitor = new TENGINE_MONITOR.taskMonitor();
    taskMonitor.run();
});
