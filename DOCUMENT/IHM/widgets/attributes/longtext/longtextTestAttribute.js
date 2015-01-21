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
            value.displayValue=value.value;
        }
        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type + " longtextTest", function () {

            beforeEach(function () {
                var $renderZone = $("#render");

                if (window.location.hash === "#displayDom") {
                    currentSandbox = $("<div></div>");
                    if ($renderZone.length === 0) {
                        $renderZone = $("body");
                    }
                    $renderZone.append(currentSandbox);
                } else {
                    currentSandbox = setFixtures(sandbox());
                }

                jasmine.clock().install();
            });

            afterEach(function () {
                var $sandBox = getSandbox();
                try {
                    if (window.location.hash !== "#displayDom") {
                        widget.call($sandBox, "destroy");
                    }
                } catch (e) {
                    //console.log(e);
                }
                jasmine.clock().uninstall();
            });

            if (options.mode === "write") {
                describe(type + " : setdisplayedLineNumber", function () {
                    it("setdisplayedLineNumber", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));
 jasmine.clock().tick(110);
                        if (expected.minHeight) {
                            expect($sandBox.find(".dcpAttribute__value").height()).toBeGreaterThan(expected.minHeight);
                        }
                        if (expected.maxHeight) {
                            expect($sandBox.find(".dcpAttribute__value").height()).toBeLessThan(expected.maxHeight);
                        }

                    });

                });

            }
            if (options.mode === "read") {
                var a=0;

            }
        });
    };
});