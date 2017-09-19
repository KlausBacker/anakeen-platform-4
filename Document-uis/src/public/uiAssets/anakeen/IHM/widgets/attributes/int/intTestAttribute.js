/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define([
    "underscore",
    "jquery",
    'dcpDocument/test/UnitTestUtilities'
], function (_, $, unitTestUtils)
{
    "use strict";
    return function (type, widget, options, value, expected)
    {

        var currentSandbox, getSandbox = function getCurrentSandbox()
        {
            return currentSandbox;
        };

        if (value.value && !_.has(value, "displayValue")) {
            value.displayValue = value.value;
        }
        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type + " intTest", function ()
        {

            beforeEach(function ()
            {
                currentSandbox = unitTestUtils.generateSandBox(options, $("#render"));
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