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

        describe(type + " htmlTest", function () {

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

                describe(type + " : destroy", function () {
                    beforeEach(function (done) {
                        var $sandBox = getSandbox();
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));
                        $sandBox.on("dcpattributedestroy", function () {
                            done();
                        });

                        widget.call($sandBox, "destroy");
                    });
                    it("destroy", function () {
                        var $sandBox = getSandbox();
                        expect($sandBox).toBeEmpty();
                        expect($sandBox).toHaveAttr("class", "");
                        expect($sandBox).not.toHaveAttr("data-type");
                        expect($sandBox).not.toHaveAttr("data-attrid");

                    });
                });

                describe(type + " : height", function () {
                    beforeEach(function (done) {
                        var $sandBox = getSandbox();
                        var ckEditor;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));
                        ckEditor=$sandBox.dcpHtmltext().data("dcpDcpHtmltext").ckEditorInstance;
                        ckEditor.on("loaded", function () {
                            done();
                        });

                    });
                     describe(type + " : icon+ height", function () {
                         it("height and iicon", function () {
                             var $sandBox = getSandbox();
                             var $ckElement=$sandBox.find("iframe.cke_reset");
                             expect($sandBox).not.toBeEmpty();
                             expect($ckElement.height()).toEqual(expected.height);

                             _.each(expected.icons, function (icon) {
                                 expect($ckElement.find("."+icon)).not.toBeEmpty();
                             });
                             _.each(expected.notIcons, function (icon) {
                                 expect($ckElement.find("."+icon)).not.toExist();
                             });
                         });
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
                        expect($sandBox.find(".dcpAttribute__value .dcpAttribute__content__value")).toHaveHtml(expected.formatValue);
                    });
                });
            }
        });
    };
});