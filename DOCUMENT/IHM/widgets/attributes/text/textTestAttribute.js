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

        describe(type + " textTest", function () {

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
                describe(type + " : placeHolder", function () {
                    it("placeHolder", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, options);

                        if (options.renderOptions.placeHolder) {
                            expect($sandBox.find(".dcpAttribute__value")).toHaveAttr("placeholder", options.renderOptions.placeHolder);
                        } else {
                            expect($sandBox.find(".dcpAttribute__value")).not.toHaveAttr("placeholder");
                        }
                    });

                });

                describe(type + " : maxlength", function () {
                    it("maxlength", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(expected.value);

                        if (options.renderOptions.maxLength > 0) {
                            expect($sandBox.find(".dcpAttribute__value")).toHaveAttr("maxlength", options.renderOptions.maxLength.toString());
                        } else {
                            expect($sandBox.find(".dcpAttribute__value")).not.toHaveAttr("maxlength");
                        }

                    });
                });
            }
            if (options.mode === "read") {

                describe(type + " : format", function () {
                    it("format", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(attrValue.value);


                        expect($sandBox.find(".dcpAttribute__value .dcpAttribute__content__value")).toHaveHtml( expected.formatValue);


                    });
                });
            }
        });
    };
});