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

        describe(type + " imageTest", function () {

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

            if (options.mode === "read") {
                describe(type + " : setThumbnailWidth", function () {
                    it("setThumbnailWidth", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                        expect($sandBox.find(".dcpAttribute__value img").attr("src")).toContain('&width=' + (options.renderOptions.thumbnailWidth));
                        expect($sandBox.find(".dcpAttribute__value img").attr("src")).toContain('&size=' + (options.renderOptions.thumbnailWidth));


                    });

                });

            }

        });
    };
});