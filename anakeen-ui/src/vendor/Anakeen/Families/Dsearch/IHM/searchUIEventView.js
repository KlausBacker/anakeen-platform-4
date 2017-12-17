/**
 * Created by Alex on 23/06/15.
 */

/*
Research result in consult mode
 */

/*global define, require, console*/

{
    window.dcp.document.documentController("addEventListener",
        "ready",
        {
            "name": "addDsearchResultViewEvent",
            "documentCheck": function (document) {
                return document.type === "search";
            }
        },
        function prepareResultViewEvents() {

            $(this).documentController("addEventListener",
                "actionClick",
                {
                    "name": "previewConsult.viewEvent",
                    "documentCheck": function isDSearch(document) {
                        return ((document.type === "search") && document.renderMode === "view");
                    }
                },
                function eventButtonView(event, document, data) {
                    var $documentController = $(this);
                    if (data.eventId === "previewConsult") {
                        var continueDefault = $documentController.documentController("triggerEvent", "custom:content",
                            {
                                "familyName": $documentController.documentController("getProperties").family.name,
                                "id": $documentController.documentController("getProperties").id,
                                "title": $documentController.documentController("getProperties").title
                            });
                        if (!continueDefault) {
                            event.preventDefault();
                        }
                        else {
                            var $window = $('<div />');
                            $('body').append($window);
                            $window.kendoWindow({
                                title: $documentController.documentController("getProperties").title,
                                content: {
                                    url: "?app=SEARCH_UI_HTML5&action=RESULT&id=" + $documentController.documentController("getProperties").id,
                                    iframe: true
                                },
                                position: {
                                    top: 0,
                                    left: 0
                                },
                                pinned: false,
                                width: "90%",
                                height: "90%",
                                actions: [
                                    "Minimize",
                                    "Maximize",
                                    "Close"
                                ]
                            });
                            $window.kendoWindow("center");
                        }
                    }
                }
            );
        });


    window.dcp.document.documentController("addEventListener",
        "close",
        {
            "name": "removeDsearchResultViewEvent",
            "documentCheck": function (document) {
                return (document.type === "search");
            }
        },
        function () {
            var $this = $(this);
            var $child = $($('body').children($('<div/>'))[$('body').children($('<div/>')).length - 1]);
            if ($child[0].className === "k-widget k-window") {
                $child.remove();
            }
            $this.documentController("removeEventListener", ".viewEvent");
        }
    );
}