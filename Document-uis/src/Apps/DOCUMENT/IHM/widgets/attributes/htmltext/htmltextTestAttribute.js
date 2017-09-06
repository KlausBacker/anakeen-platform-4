/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define([
    "jquery",
    "underscore"

], function require_htmlText($, _)
{
    "use strict";
    return function htmlText_initTest(type, widget, options, value, expected)
    {

        var currentSandbox, getSandbox = function htmlText_getSandbox()
        {
            return currentSandbox;
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

        describe(type + " htmlTest", function ()
        {
            beforeEach(function ()
            {
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

            afterEach(function ()
            {
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
                describe(type + " : height", function ()
                {
                    beforeEach(function (done)
                    {
                        var $sandBox = getSandbox();
                        var ckEditor;
                        widget.call($sandBox, _.defaults({"attributeValue": value}, options));
                        $sandBox.one("dcpattributewidgetready.testu", done);
                    });

                    describe(type + " : icon+ height", function ()
                    {
                        it("height and icon", function ()
                        {
                            var $sandBox = getSandbox();
                            var wHtmlText = $sandBox.dcpHtmltext().data("dcpDcpHtmltext");
                            var $ckElement = $sandBox.find("iframe.cke_reset");
                            expect($sandBox).not.toBeEmpty();
                            expect($ckElement.height()).toEqual(expected.height);
                            _.each(expected.icons, function checkExpectedIcon(icon)
                            {
                                expect($ckElement.find("." + icon)).not.toBeEmpty();
                            });
                            _.each(expected.notIcons, function checkExpectedIcon(icon)
                            {
                                expect($ckElement.find("." + icon)).not.toExist();
                            });

                            expect(wHtmlText.ckEditorInstance.config.toolbarStartupExpanded).toEqual(options.renderOptions.toolbarStartupExpanded);
                            expect(wHtmlText.ckEditorInstance.config.toolbar).toEqual(options.renderOptions.toolbar);
                        });
                    });
                });
            }
            if (options.mode === "read") {
                describe(type + " : format", function ()
                {
                    it("format", function ()
                    {
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