define([
    'jquery',
    'underscore',
    'mustache',
    'dcpDocument/widgets/widget',
    'dcpDocument/widgets/window/wDialog'
], function require_revisiondiff($, _, Mustache) {
    'use strict';

    $.widget("dcp.dcpRevisionDiff",  $.dcp.dcpDialog, {
        options :        {
            documentId :     0,
            firstRevision :  0,
            secondRevision : 0,
            window :         {
                modal :     true,
                animation : {
                    open :     {
                        effects :  "fade:in",
                        duration : 1000
                    }, close : {
                        effects :  "fade:out",
                        duration : 1000
                    }
                },
                actions :   [
                    "Maximize",
                    "Close"
                ],
                visible :   false,
                height :    "300px",
                width :     "500px",
                title :     "Document difference"
            },
            labels :         {
                "first" :          "First document",
                "second" :         "Second document",
                "attributeId" :    "Attribute id",
                "attributeLabel" : "Attribute label",
                "documentHeader" : '"{{title}}"  (Revision : {{revision}}). <br/>Created on <em>{{revdate}}</em>',
                "filterMessages" : "Filter data",
                "showOnlyDiff" :   "Show only differences",
                "showAll" :        "Show all"
            }
        },
        firstDocument :  null,
        secondDocument : null,

        _create :        function dcpRevisionDiff__create() {
            var currentWidget = this, $widget = $(this);

            this.element.html(this.htmlCaneva());
            require(['datatables'], function dcpDocumentWHistory_initTable()
            {
                currentWidget._initDatatable();
            });

            this.element.data("dcpRevisionDiff", this);

            this._super();

            this.element.on("click" + this.eventNamespace, ".revision-diff-button-showonlydiff", function dcpRevisionDiff_showDiff() {
                if ($widget.data("showOnlyDiff")) {
                    $widget.data("showOnlyDiff", false);
                    $(this).text(currentWidget.options.labels.showOnlyDiff).removeClass("btn-primary");
                    currentWidget.element.find(".revision-diff-equal").show();
                } else {
                    $widget.data("showOnlyDiff", true);
                    currentWidget.element.find(".revision-diff-equal").hide();
                    $(this).text(currentWidget.options.labels.showAll).addClass("btn-primary");
                }
            });

        },



        htmlCaneva :            function dcpRevisionDiff_htmlCaneva() {
            return '<table class="revision-diff-main"><thead>' +
            '<tr class="revision-diff-header">' +
            '<th class="revision-diff-header--attribute-id"/>' +
            '<th class="revision-diff-header--attribute-label"/>' +
            '<th class="revision-diff-header--first"/>' +
            '<th class="revision-diff-header--second"/>' +
            '</tr>' +
            '</thead></table>';
        },
        _initDatatable :        function dcpRevisionDiff__initDatatable() {

            var revisionDiffWidget = this;
            this.element.find('.revision-diff-main').dataTable({
                "autoWidth" :      false,
                "ordering" :       false,
                "paging" :         false,
                // "scrollY": "200px",
                "scrollCollapse" : false,
                "info" :           false,
                "language" :       {
                    "search" : " "
                },
                "columns" :        [
                    {
                        data :      "attributeId",
                        name :      "attributeId",
                        title :     revisionDiffWidget.options.labels.attributeId,
                        className : "revision-diff-attributeid",
                        visible :   false
                    },
                    {
                        data :      "attributeLabel",
                        name :      "attributeLabel",
                        title :     revisionDiffWidget.options.labels.attributeLabel,
                        className : "revision-diff-attributelabel"

                    },
                    {
                        data :      "first",
                        name :      "first",
                        title :     revisionDiffWidget.options.labels.first,
                        className : "revision-diff-first",
                        render :    function dcpRevisionDiff_renderFirst(data) {
                            if (_.isArray(data)) {
                                return _.pluck(data, 'displayValue').join(', ');
                            } else {
                                return data.displayValue;
                            }
                        }
                    },
                    {
                        data :      "second",
                        name :      "second",
                        title :     revisionDiffWidget.options.labels.second,
                        className : "revision-diff-second",
                        render :    function dcpRevisionDiff_renderSecond(data) {
                            if (_.isArray(data)) {
                                return _.pluck(data, 'displayValue').join(', ');
                            } else {
                                return data.displayValue;
                            }
                        }
                    }
                ],

                "initComplete" : function dcpRevisionDiff_initComplete() {
                    var api = this.api();
                    // var data = api.rows({page: 'current'}).data();
                    // Output the data for the visible rows to the browser's console
                    $(api.columns('first:name').header()).html(revisionDiffWidget._getDocHeader(revisionDiffWidget.firstDocument));
                    $(api.columns('second:name').header()).html(revisionDiffWidget._getDocHeader(revisionDiffWidget.secondDocument));
                    revisionDiffWidget.element.find(".dataTables_filter input").attr("placeholder", revisionDiffWidget.options.labels.filterMessages);

                    var firstHeadCell = revisionDiffWidget.element.find(".row:nth-child(1) .col-sm-6:nth-child(1)");
                    if (firstHeadCell.find('.revision-diff-button-showonlydiff').length === 0) {
                        firstHeadCell.append($('<button class="revision-diff-button-showonlydiff btn btn-default btn-sm" >' + revisionDiffWidget.options.labels.showOnlyDiff + '</button>'));
                    }
                },

                "ajax" : function dcpRevisionDiff_getData(data, callback) {
                    var myData = [];

                    $.getJSON("api/v1/documents/" + revisionDiffWidget.options.documentId +
                    "/revisions/" + revisionDiffWidget.options.firstRevision +
                    ".json?fields=family.structure,document.properties.revdate,document.properties.revision,document.attributes").
                        done(function dcpRevisionDiff_getDataDone(data1) {
                            revisionDiffWidget.firstDocument = data1.data.revision;
                            $.getJSON("api/v1/documents/" + revisionDiffWidget.options.documentId +
                            "/revisions/" + revisionDiffWidget.options.secondRevision +
                            ".json?fields=document.properties.revdate,document.properties.revision,document.attributes").
                                done(function dcpRevisionDiff_getRevisionDone(data2) {
                                    revisionDiffWidget.secondDocument = data2.data.revision;
                                    _.each(data1.data.revision.attributes, function dcpRevisionDiff_analyzeAttribute(firstValue, index) {
                                        var secondValue = data2.data.revision.attributes[index];
                                        myData.push({
                                            attributeId :    index,
                                            attributeLabel : revisionDiffWidget._findAttributeLabel(data1.data.family.structure, index),
                                            first :          firstValue,
                                            second :         secondValue,
                                            "DT_RowClass" :  (revisionDiffWidget.isEqualAttributeValue(firstValue, secondValue)) ? "revision-diff-equal" : "revision-diff-not-equal"
                                        });

                                    });
                                    callback(
                                        {data : myData}
                                    );
                                }).fail(function dcpRevisionDiff_getRevisionFail(xhr) {
                                    var result = JSON.parse(xhr.responseText);
                                    window.alert(result.exceptionMessage);
                                });
                        }).fail(function dcpRevisionDiff_getDataFail(xhr) {
                            var result = JSON.parse(xhr.responseText);
                            window.alert(result.exceptionMessage);
                        });
                }

            }).addClass('table table-condensed table-bordered table-hover');

        },

        isEqualAttributeValue : function dcpRevisionDiff_isEqualAttributeValue(v1, v2) {
            if (_.isEqual(v1, v2)) {
                return true;
            }

            if (_.isArray(v1) && _.isArray(v2)) {
                var values1 = _.pluck(v1, "value");
                var values2 = _.pluck(v2, "value");
                return _.isEqual(values1, values2);
            }
            return false;
        },

        _findAttributeLabel : function wRevisionDiffFindAttributeLabel(structure, aid) {
            var scope = this;
            var label = null;
            _.some(structure, function dcpRevisionDiff_analyzeAttribute(attributInfo, attributId) {
                if (attributId === aid) {
                    label = attributInfo.label;
                    return true;
                }
                if (_.isObject(attributInfo.content)) {
                    var contentLabel = scope._findAttributeLabel(attributInfo.content, aid);

                    if (contentLabel !== null) {
                        label = contentLabel;
                        return true;
                    }
                }
                return false;

            });
            return label;
        },

        _getDocHeader : function wRevisionDiffGetDocHeader(documentStructure) {
            var documentHeader = this.options.labels.documentHeader;
            return Mustache.render(documentHeader || "", documentStructure.properties);
        },

        _destroy : function _destroy() {
            var $history = this.element.find('.revision-diff-main');

            if ($history.DataTable) {
                $history.DataTable().destroy();
            }
            this._super();
        }
    });
});