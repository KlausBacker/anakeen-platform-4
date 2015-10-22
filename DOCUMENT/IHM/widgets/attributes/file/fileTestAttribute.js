/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define(["underscore"], function require_downloadInline(_) {
    "use strict";
    return function initTest(type, widget, options, value, expected) {

        var currentSandbox, getSandbox = function getSandbox() {
            return currentSandbox;
        }, findWidgetName = function findWidgetName($element) {
            return _.find(_.keys($element.data()), function findfindWidgetName(currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        };

        if (value.value && !_.has(value, "displayValue")) {
            value.displayValue = value.value;
        }
        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type + " fileTest", function testFileTest() {

            beforeEach(function beforeEach() {
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

            afterEach(function afterEach() {
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
                describe(type + " : setDownloadInline", function testsetDownloadInline() {
                    it("setDownloadInline", function testsetDownloadInline() {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));
                        expect($sandBox.find(".dcpAttribute__value a").attr("href")).toContain('&inline=' + (options.renderOptions.contentDisposition ? "yes" : "no"));
                        expect($sandBox.find(".dcpAttribute__value a").attr("href")).not.toContain('&inline=' + (options.renderOptions.contentDisposition ? "no" : "yes"));
                    });

                });

            }

        });
    };
});