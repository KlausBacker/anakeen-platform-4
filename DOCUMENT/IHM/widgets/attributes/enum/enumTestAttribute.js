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

        describe(type + " enumTest", function () {

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

                describe(type + " : checked", function () {
                    it("check button", function () {
                        var $sandBox = getSandbox(), attrValue;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                        if (options.renderOptions.editDisplay === "vertical" ||
                            options.renderOptions.editDisplay === "horizontal" ||
                            options.renderOptions.editDisplay === "bool") {
                            attrValue = widget.call($sandBox, "getValue");

                            _.each(options.sourceValues, function (item) {
                                expect($sandBox.find("input.dcpAttribute__value[value=" + item.key + "]")).toExist();
                            });
                            if (options.options.multiple !== "yes") {
                                expect(value.value).toEqual(attrValue.value);
                                expect($sandBox.find("input.dcpAttribute__value[value=" + value.value + "]")).toHaveProp("checked", true);
                                expect($sandBox.find("input.dcpAttribute__value[value!=" + value.value + "]")).toHaveProp("checked", false);
                            } else {
                                var wAllValues = _.pluck(options.sourceValues, "key");
                                var wSelectedValues = _.pluck(attrValue, "value");
                                _.each(value, function (oneValue) {
                                    expect($sandBox.find("input.dcpAttribute__value[value=" + oneValue.value + "]")).toHaveProp("checked", true);
                                });
                                _.each(_.difference(wAllValues, wSelectedValues), function (oneValue) {
                                    expect($sandBox.find("input.dcpAttribute__value[value=" + oneValue + "]")).toHaveProp("checked", false);
                                });

                            }
                        }
                    });
                });

                describe(type + " : select", function () {
                    it("select button", function () {
                        var $sandBox = getSandbox(), attrValue;
                        var wAllValues = _.pluck(options.sourceValues, "key");
                        widget.call($sandBox, options);

                        attrValue = widget.call($sandBox, "getValue");
                        if (options.renderOptions.editDisplay === "vertical" ||
                            options.renderOptions.editDisplay === "horizontal") {


                            expect(attrValue.value).toBeFalsy();

                            if (options.options.multiple !== "yes") {

                                if (_.contains(wAllValues, value.value)) {
                                    $sandBox.find("input.dcpAttribute__value[value=" + value.value + "]").trigger("click");

                                    attrValue = widget.call($sandBox, "getValue");
                                    expect(attrValue.value).toEqual(value.value);
                                }
                            }
                        }


                        if (options.renderOptions.editDisplay === "bool") {

                            // Set to first value
                            expect(attrValue.value).toEqual(options.sourceValues[0].key);

                            // invert select trigger
                            $sandBox.find("input.dcpAttribute__value[value!=" + value.value + "]").trigger("click");
                            attrValue = widget.call($sandBox, "getValue");
                            expect(attrValue.value).toEqual(value.value);


                        }
                    });
                });
            }

        });
    };
});