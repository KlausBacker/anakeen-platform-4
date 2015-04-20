/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define([
    "jquery",
    "underscore",
    'dcpDocument/test/UnitTestUtilities'], function ($, _, unitTestUtils)
{
    "use strict";
    return function (type, widget, options, value)
    {

        var currentSandbox, getSandbox = function ()
        {
            return currentSandbox;
        };

        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type + " defaultTest", function ()
        {

            beforeEach(function ()
            {
                currentSandbox = unitTestUtils.generateSandBox(options, $("#render"));
            });

            afterEach(function ()
            {
                var $sandBox = getSandbox();
                $sandBox.off(".testu");
                try {
                    if (window.location.hash !== "#displayDom") {
                        widget.call($sandBox, "destroy");
                        currentSandbox.remove();
                    }
                } catch (e) {
                    //console.log(e);
                }
            });

            describe(type + " : creation", function ()
            {

                it("content", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function() {
                        expect($sandBox).not.toBeEmpty();
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("event", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("class", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function() {
                        expect($sandBox).toHaveClass("dcpAttribute__content");
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("data", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function() {
                        expect($sandBox).toHaveAttr("data-type", widget.call($sandBox, "getType"));
                        expect($sandBox).toHaveAttr("data-attrid", widget.call($sandBox, "option", "id"));
                        done();
                    });
                    widget.call($sandBox, options);
                });

            });

            describe(type + " : destroy", function ()
            {
                if (!options.noDestroyTesting) {
                    it("clean", function (done)
                    {
                        var $sandBox = getSandbox();
                        $sandBox.on("dcpattributewidgetready.testu", function() {
                            widget.call($sandBox, "destroy");
                        });
                        $sandBox.on("dcpattributedestroy.testu", function ()
                        {
                            expect($sandBox).toBeEmpty();
                            expect($sandBox).toHaveAttr("class", "");
                            expect($sandBox).not.toHaveAttr("data-type");
                            expect($sandBox).not.toHaveAttr("data-attrid");
                            done();
                        });
                        widget.call($sandBox, options);
                    });

                }
            });

            describe(type + " : setValue", function ()
            {
                beforeEach(function ()
                {
                    setFixtures(sandbox());
                });

                it("equality", function (done)
                {
                    var $sandBox = getSandbox(), attrValue;
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        widget.call($sandBox, "setValue", value);
                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(attrValue.value);
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("event", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        widget.call($sandBox, "setValue", value);
                        expect('dcpattributechange').toHaveBeenTriggeredOn($sandBox);
                        done();
                    });
                    spyOnEvent($sandBox, 'dcpattributechange');
                    widget.call($sandBox, options);

                });
            });

            describe(type + " : getValue", function ()
            {

                it("init", function (done)
                {
                    var $sandBox = getSandbox(), attrValue;
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(attrValue.value);
                        done();
                    });
                    widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                });
            });

            describe(type + " : link", function ()
            {
                it("hasLink", function (done)
                {
                    var $sandBox = getSandbox(), value;
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        value = widget.call($sandBox, "hasLink");
                        expect(value).toBeTruthy();
                        widget.call($sandBox, "option", "renderOptions", {});
                        value = widget.call($sandBox, "hasLink");
                        expect(value).toBeFalsy();
                        done();
                    });
                    widget.call($sandBox, _.defaults({"renderOptions": {htmlLink: {url: "http://www.anakeen.com"}}}, options));
                });

                it("getLink", function (done)
                {
                    var $sandBox = getSandbox(), value;
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        value = widget.call($sandBox, "getLink");
                        expect(value.url).toEqual("http://www.anakeen.com");
                        widget.call($sandBox, "option", "renderOptions", {});
                        value = widget.call($sandBox, "getLink");
                        expect(value).toBeNull();
                        done();
                    });
                    widget.call($sandBox, _.defaults({"renderOptions": {htmlLink: {url: "http://www.anakeen.com"}}}, options));
                });
            });

            describe(type + " : deleteButton", function ()
            {

                it("Create", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        if (options.mode && options.mode === "write") {
                            expect($sandBox.find(".dcpAttribute__content__button--delete")).toExist();
                        } else {
                            expect($sandBox.find(".dcpAttribute__content__button--delete")).not.toExist();
                        }
                        done();
                    });
                    widget.call($sandBox, _.defaults({"deleteButton": true}, options));
                });

                it("NoRemoveButton", function (done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function ()
                    {
                        expect($sandBox.find(".dcpAttribute__content__button--delete")).not.toExist();
                        done();
                    });
                    widget.call($sandBox, _.defaults({"deleteButton": false}, options));
                });

                if (options.mode && options.mode === "write") {
                    it("Event", function (done)
                    {
                        var $sandBox = getSandbox();
                        $sandBox.on("dcpattributewidgetready.testu", function ()
                        {
                            spyOnEvent($sandBox, 'dcpattributedelete');
                            $sandBox.find(".dcpAttribute__content__button--delete").trigger("click");
                            expect('dcpattributedelete').toHaveBeenTriggeredOn($sandBox);
                            done();
                        });
                        widget.call($sandBox, _.defaults({"deleteButton": true}, options));
                    });
                }
            });

        });
    };
});