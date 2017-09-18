/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define([
    "jquery",
    "underscore",
    'dcpDocument/test/UnitTestUtilities'], function requireSuiteDefaultTestAttribute($, _, unitTestUtils)
{
    "use strict";
    return function initTestSuite(type, widget, options, value)
    {

        var currentSandbox, getSandbox = function getSandbox()
        {
            return currentSandbox;
        };

        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type + " defaultTest", function testDefaultTest()
        {

            beforeEach(function beforeEach()
            {
                currentSandbox = unitTestUtils.generateSandBox(options, $("#render"));
            });

            afterEach(function afterEach()
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

            describe(type + " : creation", function testCreation()
            {

                it("content", function testcontent(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready() {
                        expect($sandBox).not.toBeEmpty();
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("event", function testevent(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("class", function testClass(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready() {
                        expect($sandBox).toHaveClass("dcpAttribute__content");
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("data", function testData(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready() {
                        expect($sandBox).toHaveAttr("data-type", widget.call($sandBox, "getType"));
                        expect($sandBox).toHaveAttr("data-attrid", widget.call($sandBox, "option", "id"));
                        done();
                    });
                    widget.call($sandBox, options);
                });

            });

            describe(type + " : destroy", function testdestroy()
            {
                if (!options.noDestroyTesting) {
                    it("clean", function testClean(done)
                    {
                        var $sandBox = getSandbox();
                        $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready() {
                            widget.call($sandBox, "destroy");
                        });
                        $sandBox.on("dcpattributedestroy.testu", function testdcpattributedestroy()
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

            describe(type + " : setValue", function testsetValue()
            {
                beforeEach(function testsetValuebeforeEach()
                {
                    setFixtures(sandbox());
                });

                it("equality", function testsetValue(done)
                {
                    var $sandBox = getSandbox(), attrValue;
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
                    {
                        widget.call($sandBox, "setValue", value);
                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(attrValue.value);
                        done();
                    });
                    widget.call($sandBox, options);
                });

                it("event", function testsetValueevent(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
                    {
                        widget.call($sandBox, "setValue", value);
                        expect('dcpattributechange').toHaveBeenTriggeredOn($sandBox);
                        done();
                    });
                    spyOnEvent($sandBox, 'dcpattributechange');
                    widget.call($sandBox, options);

                });
            });

            describe(type + " : getValue", function testgetValue()
            {

                it("init", function testgetValueinit(done)
                {
                    var $sandBox = getSandbox(), attrValue;
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
                    {
                        attrValue = widget.call($sandBox, "getValue");
                        expect(value.value).toEqual(attrValue.value);
                        done();
                    });
                    widget.call($sandBox, _.defaults({"attributeValue": value}, options));

                });
            });

            describe(type + " : link", function testlink()
            {
                it("hasLink", function testHaslink(done)
                {
                    var $sandBox = getSandbox(), value;
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
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

                it("getLink", function testgetLink(done)
                {
                    var $sandBox = getSandbox(), value;
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
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

            describe(type + " : deleteButton", function testdeleteButton()
            {

                it("Create", function testdeleteButtonCreate(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
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

                it("NoRemoveButton", function testdeleteButtonNoRemoveButton(done)
                {
                    var $sandBox = getSandbox();
                    $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
                    {
                        expect($sandBox.find(".dcpAttribute__content__button--delete")).not.toExist();
                        done();
                    });
                    widget.call($sandBox, _.defaults({"deleteButton": false}, options));
                });

                if (options.mode && options.mode === "write") {
                    it("Event", function testdeleteEvent(done)
                    {
                        var $sandBox = getSandbox();
                        $sandBox.on("dcpattributewidgetready.testu", function testdcpattributewidgetready()
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