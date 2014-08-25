/*global define, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/
define(["underscore"], function (_) {
    "use strict";
    return function (type, widget, options, value) {

        var currentSandbox, getSandbox = function () {
            return currentSandbox;
        };

        if (!_.isFunction(widget)) {
            throw Error("First argument must be a widget function");
        }

        describe(type+" defaultTest", function () {

            beforeEach(function () {
                currentSandbox = $("<div></div>");
                $("body").prepend(currentSandbox);
                //currentSandbox = setFixtures(sandbox());
            });

            describe(type + " : creation", function () {

                it("content", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    expect($sandBox).not.toBeEmpty();
                });

                it("class", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    expect($sandBox).toHaveClass("dcpAttribute__contentWrapper");
                });

                it("event", function () {
                    var $sandBox = getSandbox();
                    spyOnEvent($sandBox, 'dcpattributecreate');
                    widget.call($sandBox, options);
                    expect('dcpattributecreate').toHaveBeenTriggeredOn($sandBox);
                });

                it("data", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    expect($sandBox).toHaveAttr("data-type", widget.call($sandBox, "getType"));
                    expect($sandBox).toHaveAttr("data-id", widget.call($sandBox, "option", "id"));
                });

            });

            describe(type + " : destroy", function () {

                it("content", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    widget.call($sandBox, "destroy");
                    expect($sandBox).toBeEmpty();
                });

                it("class", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    widget.call($sandBox, "destroy");
                    expect($sandBox).toHaveAttr("class", "");
                });

                it("event", function () {
                    var $sandBox = getSandbox();
                    spyOnEvent($sandBox, 'dcpattributedestroy');
                    widget.call($sandBox, options);
                    widget.call($sandBox, "destroy");
                    expect('dcpattributedestroy').toHaveBeenTriggeredOn($sandBox);
                });

                it("data", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    widget.call($sandBox, "destroy");
                    expect($sandBox).not.toHaveAttr("data-type");
                    expect($sandBox).not.toHaveAttr("data-id");
                });

            });

            describe(type + " : setValue", function () {
                beforeEach(function () {
                    setFixtures(sandbox());
                });

                it("equality", function () {
                    var $sandBox = getSandbox(), attrValue;
                    widget.call($sandBox, options);
                    widget.call($sandBox, "setValue", value);
                    attrValue = widget.call($sandBox, "getValue");
                    expect(value.value).toEqual(attrValue.value);
                });

                it("event", function () {
                    var $sandBox = getSandbox();
                    spyOnEvent($sandBox, 'dcpattributechange');
                    widget.call($sandBox, options);
                    widget.call($sandBox, "setValue", value);
                    expect('dcpattributechange').toHaveBeenTriggeredOn($sandBox);
                });
            });

            describe(type + " : getValue", function () {

                it("init", function () {
                    var $sandBox = getSandbox(), attrValue;
                    widget.call($sandBox, _.defaults({"value" : value}, options));
                    attrValue = widget.call($sandBox, "getValue");
                    expect(value.value).toEqual(attrValue.value);
                });
            });

            describe(type + " : link", function () {
                it("hasLink", function () {
                    var $sandBox = getSandbox(), value;
                    widget.call($sandBox, _.defaults({"renderOptions" : {htmlLink : {url : "http://www.anakeen.com"}}}, options));
                    value = widget.call($sandBox, "hasLink");
                    expect(value).toBeTruthy();
                    widget.call($sandBox, "option", "renderOptions", {});
                    value = widget.call($sandBox, "hasLink");
                    expect(value).toBeFalsy();
                });

                it("getLink", function () {
                    var $sandBox = getSandbox(), value;
                    widget.call($sandBox, _.defaults({"renderOptions" : {htmlLink : {url : "http://www.anakeen.com"}}}, options));
                    value = widget.call($sandBox, "getLink");
                    expect(value.url).toEqual("http://www.anakeen.com");
                    widget.call($sandBox, "option", "renderOptions", {});
                    value = widget.call($sandBox, "getLink");
                    expect(value).toBeNull();
                });
            });

            describe(type + " : flashElement", function () {

                beforeEach(function () {
                    jasmine.clock().install();
                });

                afterEach(function () {
                    jasmine.clock().uninstall();
                });

                it("FlashClass", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    widget.call($sandBox, "flashElement");
                    expect($sandBox).toHaveClass("dcpAttribute__content--flash");
                    jasmine.clock().tick(11);
                    expect($sandBox).not.toHaveClass("dcpAttribute__content--flash");
                });

                it("EndFlashClass", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, options);
                    widget.call($sandBox, "flashElement");
                    jasmine.clock().tick(11);
                    expect($sandBox).toHaveClass("dcpAttribute__content--endflash");
                    jasmine.clock().tick(601);
                    expect($sandBox).not.toHaveClass("dcpAttribute__content--endflash");
                });
            });

            describe(type + " : deleteButton", function () {

                it("Create", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, _.defaults({"deleteButton" : true}, options));
                    if (options.mode && options.mode === "write") {
                        expect($sandBox.find(".dcpAttribute__content__button--delete")).toExist();
                    } else {
                        expect($sandBox.find(".dcpAttribute__content__button--delete")).not.toExist();
                    }
                });

                it("NoRemoveButton", function () {
                    var $sandBox = getSandbox();
                    widget.call($sandBox, _.defaults({"deleteButton" : false}, options));
                    expect($sandBox.find(".dcpAttribute__content__button--delete")).not.toExist();
                });

                if (options.mode && options.mode === "write") {
                    it("Event", function () {
                        var $sandBox = getSandbox();
                        widget.call($sandBox, _.defaults({"deleteButton" : true}, options));
                        spyOnEvent($sandBox, 'dcpattributedelete');
                        $sandBox.find(".dcpAttribute__content__button--delete").trigger("click");
                        expect('dcpattributedelete').toHaveBeenTriggeredOn($sandBox);
                    });
                }
            });

        });
    };
});