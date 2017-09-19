$(document).ready(function () {
    var firstSearch = $($("a.searchIcon").get(0));

    $('#systemTable').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sScrollY": "200px",
            "sDom": '<"ui-state-default systemHeader"i<"searchTitle">pf>rt',
            "iDisplayLength": 50,
            "sAjaxSource": '?app=DOCADMIN&action=SYSTEM_GETDATA&id=' +
                firstSearch.attr('data-searchid'),
            "oLanguage": {
                "oPaginate": {
                    "sNext": "",
                    "sPrevious": ""
                },
                "sInfo": "_START_ - _END_ /  _TOTAL_",
                "sInfoEmpty": "",
                "sSearch": "",
                "sEmptyTable": "no matches documents"
            },
            aoColumnDefs: [
                {
                    "aTargets": [0],
                    "mDataProp": "docicon",
                    "bSearchable": false,
                    "bSortable": false,
                    "bUseRendered": false,
                    "fnRender": function (data) {
                        return '<img src="' + (data.aData.docicon || "") + '">';
                    },
                    "sWidth": "20px",
                    "sClass": " familyIconList"
                },
                {
                    "aTargets": [1],
                    "mDataProp": "docid",
                    "bVisible": false
                },
                {
                    "aTargets": [2],
                    "bSortable": false,
                    "mDataProp": "doctitle",
                    "bUseRendered": false,
                    "fnRender": function (data) {
                        return '<a class="doc-relation" data-docid="' + data.aData.docid + '">' + data.aData.doctitle + '</a>';
                    },
                    "sClass": " familyTitleList",
                    "sWidth": "auto"
                }
            ]

        }
    ).on("click", "tbody tr",function () {
            var aDoc = $(this).find('a.doc-relation');
            if (aDoc.length > 0) {
                var tabs = $('#tabs');
                var selDocId = aDoc.attr("data-docid");
                var idif = 'if' + selDocId + Math.round(Math.random() * 10000);

                var tabDocId = $('#ulTabs').find('span[data-docid=' + selDocId + ']').parent().parent();
                if (tabDocId.length == 0) {
                    //var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
                    var divTemplate = "<div class='divDoc' id='#{href}'><iframe class='frameDoc' src='api/v1/documents/#{docid}.html'></iframe></div>";
                    var titleTemplate = '<span class="ui-icon ui-icon-close" role="presentation">Remove Tab</span><img class="docIcon" src="#{src}"/> <span data-docid="#{docid}" class="docTitle" title="#{title}">#{title}</span>';

                    // var li = $(tabTemplate.replace(/#\{href\}/g, "#" + idif).replace(/#\{label\}/g, aDoc.text()));
                    // tabs.find(".ui-tabs-nav").append(li);
                    // tabs.tabs("refresh");
                    var idiv = $(divTemplate.replace(/#\{href\}/g, idif).replace(/#\{docid\}/g, aDoc.attr("data-docid")));
                    var ititle = titleTemplate.replace(/#\{src\}/g, aDoc.parent().parent().find('img').attr('src')).replace(/#\{title\}/g, aDoc.text()).replace(/#\{docid\}/g, selDocId);
                    tabs.append(idiv);
                    tabs.tabs("add", '#' + idif, ititle); // not supported in jQuery 1.10
                    var docTab = docTab = $('#' + idif);
                    docTab.find('iframe').on('load', function () {
                        if (this.contentDocument && this.contentDocument.location && this.contentDocument.location.href.toLowerCase().indexOf("about:blank") > -1) {
                            $(this).remove();
                            $('#tabs').tabs("remove", '#' + idif);
                        } else {
                            var title = '-';
                            if (this.contentDocument && this.contentDocument.title) {
                                title = this.contentDocument.title;
                            }
                            var iconSrc = $(this).contents().find("meta[name=document-icon]").attr("content");
                            var docId = $(this).contents().find("meta[name=document-initid]").attr("content");

                            tabs.find("a[href=#" + idif + "] .docTitle").text(title).attr('title', title).attr('data-docid', docId);
                            tabs.find("a[href=#" + idif + "] img").attr('src', iconSrc);
                            tabs.find("a[href=#" + idif + "] img").attr('src', iconSrc);
                        }
                    });
                    tabs.find('.docTitle').tipsy();
                    tabs.tabs("select", "#" + idif);
                    resizeTabs();
                } else {
                    tabs.tabs("select", tabDocId.attr('href'));
                }

            }
            $(this).parent().find('tr').removeClass("ui-state-highlight");
            $(this).addClass("ui-state-highlight");

        }).on("mouseout", "tbody tr",function () {
            $(this).removeClass("ui-state-hover");
        }).on("mouseover", "tbody tr", function () {
            $(this).addClass("ui-state-hover");
        });


    $(".filterButton")
        .button()
        .click(function () {
            var lastFam = $(this).parent().parent().find(".familyList a.ui-state-highlight");
            if (lastFam.length > 0) {
                lastFam.trigger('click');
            } else {
                lastFam = $(this).parent().parent().find(".familyList a");
                if (lastFam.length > 0) {
                    $(lastFam[0]).trigger('click');
                }
                return false;
            }
            return true;
        })
        .next()
        .button({
            text: false,
            icons: {
                primary: "ui-icon-triangle-1-s"
            }
        })
        .click(function () {
            var menu = $(this).parent().next().show().position({
                my: "left top",
                at: "left bottom",
                of: this
            });
            $(document).one("click", function () {
                menu.hide();
            });
            return false;
        })
        .parent()
        .buttonset()
        .next()
        .hide()
        .menu();

    $('.selfamily').on("click", function () {
        $(".searchIcon").removeClass("ui-state-highlight");

        var familyId = $(this).attr('data-familyid') || '';
        var searchId = $(this).attr('data-searchid') || '';
        $(this).parent().parent().find("a.selfamily").removeClass("ui-state-highlight");
        $(this).addClass("ui-state-highlight");
        var oTable = $("#systemTable").dataTable();
        var oSettings = oTable.fnSettings();
        oSettings.sAjaxSource = '?app=DOCADMIN&action=SYSTEM_GETDATA&famid=' + familyId + '&id=' + searchId;

        var newTitle = $(this).text();
        $(".searchTitle").html(newTitle);

        var filterButton = $(this).parent().parent().parent().find('.filterButton');
        filterButton.find('img').attr('src', $(this).find('img').attr('src'));
        filterButton.attr('title', newTitle).addClass("ui-state-highlight");
        oTable.fnDraw();
        resizeSysDoc();
    });

    $('.searchButton').button().on("click", function () {
        var searchId = $(this).attr('data-searchid');
        $(".searchIcon").removeClass("ui-state-highlight");
        $(this).addClass("ui-state-highlight");
        var oTable = $("#systemTable").dataTable();
        var oSettings = oTable.fnSettings();
        oSettings.sAjaxSource = '?app=DOCADMIN&action=SYSTEM_GETDATA&id=' + searchId;

        var newTitle = $(this).attr('original-title');
        if (!newTitle) {
            newTitle = $(this).attr('title');
        }
        $(".searchTitle").html(newTitle);

        oTable.fnDraw();
        resizeSysDoc();
    });

    function resizeSysDoc() {
        // first iframe height

        //$(".systemRight").height($(window).height - 2 * ($(".systemRight").offset().top));
        var tdsystemRight = $(".systemRight");
        var tdHeight = $(window).height() - 2 * (tdsystemRight.offset().top) - 11;
        tdsystemRight.height(tdHeight);

        resizeTabs();

        var tListbody = $(".dataTables_scrollBody");

        var windowHeight = $(window).height();

        if (tListbody.offset()) {
            var offY = tListbody.offset().top;

            tListbody.height(windowHeight - offY - 16);
        }
    }

    function resizeTabs() {
        var tdsystemRight = $(".systemRight");
        var tdHeight = tdsystemRight.height();
        $('.divDoc').height(tdHeight - $('#ulTabs').height() - 16);

    }

    $(window).resize(function () {
            resizeSysDoc();
        }
    );
    resizeSysDoc();
    $(".searchTitle").html(firstSearch.attr('title'));
    firstSearch.addClass("ui-state-highlight");
    $(".searchIcon").tipsy();
    $('#tabs').tabs().on("mousedown", "span.ui-icon-close", function () {
        var currentIframe = $($(this).parent().parent().attr("href")).find("iframe");
        currentIframe.attr("src", "about:blank");
    });


})
;