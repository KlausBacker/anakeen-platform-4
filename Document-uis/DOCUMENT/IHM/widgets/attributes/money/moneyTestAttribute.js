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

        describe(type + " moneyTest", function () {

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
            });

            if (options.mode === "write") {
                var a2 = 0;
            }
            if (options.mode === "read") {
                var a1 = 0;
            }
        });
    };
});