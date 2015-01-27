/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define(["underscore"], function (_) {
    "use strict";
    return function (type, widget, options, value, expected) {

        var currentSandbox, getSandbox = function () {
            return currentSandbox;
        }, findWidgetName = function ($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        };

        if (value.value && !_.has(value, "displayValue")) {
            value.displayValue = value.value;
        }
        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }


        if (_.isUndefined(options.renderOptions.toolbarStartupExpanded)) {
            options.renderOptions.toolbarStartupExpanded = true;
        }

        describe(type + " htmlTest", function () {

            beforeEach(function () {
                var $renderZone = $("#render");

                currentSandbox = $("<div></div>");
                if ($renderZone.length === 0) {
                    $renderZone = $("body");
                }
                $renderZone.append(currentSandbox);
            });


            if (options.mode === "write") {

                describe(type + " : destroy", function () {
                    beforeEach(function (done) {
                        var $sandBox = getSandbox();
                        var ckEditor;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                        ckEditor = $sandBox.dcpHtmltext().data("dcpDcpHtmltext").ckEditorInstance;

                        $sandBox.on("dcpattributedestroy", function () {
                            done();
                        });
                        ckEditor.on("loaded", function () {
                            // Need to defer because ckEditor also defer postLoaded
                            _.delay(function () {
                                widget.call($sandBox, "destroy");
                            }, 1);
                        });
                    });
                    afterEach(function (done) {
                        // nothing to do
                        var $sandBox = getSandbox();

                        $sandBox.off("dcpattributedestroy");
                        _.defer(function () {
                            done();
                        });
                    });
                    it("destroy", function () {
                        var $sandBox = getSandbox();
                        expect($sandBox).toBeEmpty();
                        expect($sandBox).toHaveAttr("class", "");
                        expect($sandBox).not.toHaveAttr("data-type");
                        expect($sandBox).not.toHaveAttr("data-attrid");
                    });
                });


            }

        });
    };
});