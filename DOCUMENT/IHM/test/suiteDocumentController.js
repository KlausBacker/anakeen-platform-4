/*global define ,describe, define, beforeEach, expect, it, spyOn, afterEach*/
define([
    'underscore',
    'jquery',
    'backbone',
    'dcpDocument/models/mDocument',
    'dcpDocument/models/mFamilyStructure',
    'dcpDocument/models/mDocumentLock',
    'dcpDocument/test/UnitTestUtilities',
    'dcpDocument/test/valuesDocuments',
    'dcpDocument/widgets/documentController/documentController'], function initDCTest(_, $, Backbone, mDocument, mFamilyStructure, mLock, unitTestUtils, values)
{

    "use strict";

    return function (config, documentOptions)
    {
        var currentSandbox, synchro, currentValues, getSandbox = function ()
        {
            return currentSandbox;
        }, prepareDocumentController = function prepareDocumentController(config)
        {
            config = config || {};
            _.defaults(config, {
                "initid": "53681",
                "viewId": "!defaultConsultation",
                "noRouter": true
            });
            return config;
        };

        config = config || {};

        describe("documentController : " + config.name, function ()
        {

            var getMockFunction = function getMockFunction()
            {
                var mock = {
                    "fct": function mockFunction()
                    {
                    }
                };
                spyOn(mock, "fct");
                return mock;
            };

            beforeEach(function initSyncBeforeTest()
            {
                currentSandbox = unitTestUtils.generateSandBox(config, $("#render"));
                synchro = Backbone.sync;
                Backbone.sync = function mockSynch(method, model, options)
                {
                    var documentIdentifier="no compatible model class";
                    if (model instanceof mDocument) {
                         documentIdentifier = model.id + model.get("viewId");

                        if (documentIdentifier === "1081!defaultConsultation") {
                            documentIdentifier = "1081!coreConsultation";
                        }

                        if (documentIdentifier === "53681!defaultConsultation") {
                            documentIdentifier = "53681!coreConsultation";
                        }


                        if (method === "read" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            options.success(values[documentIdentifier]);
                            return true;
                        }

                        if (method === "update" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            options.success(values[documentIdentifier]);
                            return true;
                        }

                        if (method === "delete" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            options.success(values[documentIdentifier]);
                            return true;
                        }
                    }

                     if (model instanceof mFamilyStructure) {
                         documentIdentifier = model.id + "!structure";
                         if (method === "read" && !_.isUndefined(values[documentIdentifier])) {
                             // Defer because document Model is not initialized yet
                             _.defer(function () {
                                 options.success(values[documentIdentifier]);
                             });

                            return true;
                        }
                     }
                    if (model instanceof mLock) {
                        documentIdentifier = model.id + "!lock";

                        if (method === "update" && !_.isUndefined(values[documentIdentifier])) {
                            options.success(values[documentIdentifier]);
                            return true;
                        }
                    }
                    throw new Error("Unable to sync", documentIdentifier);
                };
            });

            afterEach(function cleanAfterTest()
            {
                var $sandox = getSandbox();
                $sandox.off(".test");
                $sandox.documentController("destroy");
                Backbone.sync = synchro;
            });

            describe("init", function ()
            {

                it("ready", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                });

                it("reinit", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function ()
                    {
                        var mock = getMockFunction();
                        $sandox.documentController("addEventListener", "ready", mock.fct);
                        $sandox.documentController("reinitDocument");
                        //2 because auto launch ready
                        expect(mock.fct.calls.count()).toEqual(2);
                        done();
                    });
                });

            });

            describe("destroy", function ()
            {

                it("destroy", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEventListener", "destroy", function ()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        $sandox.documentController("destroy");
                    });
                });

            });

            describe("fetch", function ()
            {

                it("arguments", function test_arguments(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once" : true}, function ()
                    {
                        expect(function ()
                        {
                            $sandox.documentController("fetchDocument", null);
                        }).toThrowError('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
                        done();
                    });
                });

                it("without arguments", function test_without_arguments(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function ()
                    {
                        var mockReady = getMockFunction();
                        $sandox.documentController("addEventListener", "ready", mockReady.fct);
                        $sandox.documentController("fetchDocument", {"initid" : $sandox.documentController("getProperty", "initid")});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2);
                        done();
                    });
                });

                it("events", function test_events(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeClose = getMockFunction(), mockClose = getMockFunction(),
                            mockSuccess = getMockFunction();
                        $sandox.documentController("addEventListener", "ready", mockReady.fct);
                        $sandox.documentController("addEventListener", "beforeClose", mockBeforeClose.fct);
                        $sandox.documentController("addEventListener", "close", mockClose.fct);
                        $sandox.documentController("fetchDocument", {"initid": $sandox.documentController("getProperty", "initid")}, {success: mockSuccess.fct});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2);
                        expect(mockBeforeClose.fct.calls.count()).toEqual(1);
                        expect(mockClose.fct.calls.count()).toEqual(1);
                        expect(mockSuccess.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

                it("change viewId", function (done)
                {
                    var $sandox = getSandbox(), documentOptions = prepareDocumentController(documentOptions);
                    $sandox.documentController(documentOptions);
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function ()
                    {
                        var mock = getMockFunction(), viewId = documentOptions.viewId === "!coreConsultation" ? "!defaultEdition" : "!coreConsultation";
                        $sandox.documentController("addEventListener", "ready", mock.fct);

                        $sandox.documentController("fetchDocument", {"initid": $sandox.documentController("getProperty", "initid"), "viewId" : viewId});
                        //2 because auto launch ready
                        expect(mock.fct.calls.count()).toEqual(2);
                        expect($sandox.documentController("getProperty", "viewId"), viewId);
                        done();
                    });
                });

            });

            describe("save", function ()
            {

                it("events", function test_events(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeSave = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterSave = getMockFunction(), mockSuccess = getMockFunction(), mockValidate = getMockFunction();
                        $sandox.documentController("addEventListener", "ready", mockReady.fct);
                        $sandox.documentController("addEventListener", "validate", mockValidate.fct);
                        $sandox.documentController("addEventListener", "beforeSave", mockBeforeSave.fct);
                        $sandox.documentController("addEventListener", "close", mockClose.fct);
                        $sandox.documentController("addEventListener", "afterSave", mockAfterSave.fct);
                        $sandox.documentController("saveDocument", {success: mockSuccess.fct});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2, "ready count");
                        expect(mockBeforeSave.fct.calls.count()).toEqual(1, "save count");
                        expect(mockClose.fct.calls.count()).toEqual(1, "close count");
                        expect(mockAfterSave.fct.calls.count()).toEqual(1, "after save count");
                        expect(mockSuccess.fct.calls.count()).toEqual(1, "success count");
                        //One for the data from the form, one form the data from the sync
                        expect(mockValidate.fct.calls.count()).toEqual(2, "validate count");
                        done();
                    });
                });

            });

            describe("delete", function ()
            {

                it("events", function test_events(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeDelete = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterDelete = getMockFunction(), mockSuccess = getMockFunction();
                        $sandox.documentController("addEventListener", "ready", mockReady.fct);
                        $sandox.documentController("addEventListener", "beforeDelete", mockBeforeDelete.fct);
                        $sandox.documentController("addEventListener", "close", mockClose.fct);
                        $sandox.documentController("addEventListener", "afterDelete", mockAfterDelete.fct);
                        $sandox.documentController("deleteDocument", {success: mockSuccess.fct});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2);
                        expect(mockBeforeDelete.fct.calls.count()).toEqual(1);
                        expect(mockClose.fct.calls.count()).toEqual(1);
                        expect(mockAfterDelete.fct.calls.count()).toEqual(1);
                        expect(mockSuccess.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

            });

            describe("properties", function ()
            {

                it("getProperty", function test_getProperty(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        _.each(currentValues.data.view.documentData.document.properties, function (value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandox.documentController("getProperty", attrid);
                            expect(currentValue).toEqual(value, "for " + attrid);
                        });
                        done();
                    });
                });

                it("getProperties", function test_getProperty(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        var properties = $sandox.documentController("getProperties");
                        _.each(properties, function (value, attrid)
                        {
                            if (attrid === "viewId") {
                                expect(documentOptions.viewId).toEqual(value, "for " + attrid);
                                return;
                            }
                            if (attrid === "renderMode") {
                                expect(currentValues.data.view.renderOptions.mode).toEqual(value, "for " + attrid);
                                return;
                            }
                            expect(currentValues.data.view.documentData.document.properties[attrid]).toEqual(value, "for " + attrid);
                        });
                        done();
                    });
                });

            });

            describe("attributes", function ()
            {

                it("getAttribute", function test_getAttribute(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_attribute()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function (value, attrid)
                        {
                            var currentAttribute;
                            currentAttribute = $sandox.documentController("getAttribute", attrid);
                            expect(currentAttribute.getValue()).toEqual(value, "for " + attrid);
                        });
                        expect(function testUnkownAttribute()
                        {
                            $sandox.documentController("getAttribute", "unkown attribute id");
                        }).toThrow();
                        done();
                    });
                });

                it("internalObject", function test_internalObject(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_attribute()
                    {
                        var attribute = $sandox.documentController("getAttribute", "zoo_f_title");
                        expect(attribute.id).toBeDefined("id");
                        expect(attribute.id).toEqual("zoo_f_title", "Test id value");
                        expect(attribute.getValue).toBeDefined("getValue");
                        expect(attribute.getProperties).toBeDefined("getProperties");
                        expect(attribute.getOptions).toBeDefined("getOptions");
                        expect(attribute.getOption).toBeDefined("getOption");
                        expect(attribute.getValue).toBeDefined("getValue");
                        expect(attribute.setValue).toBeDefined("setValue");
                        expect(attribute.getValue()).toBeUndefined("getValue of a frame");
                        done();
                    });
                });

                it("getAttributes", function test_getAttributes(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        var attributes = $sandox.documentController("getAttributes");
                        _.each(attributes, function (attribute)
                        {
                            var type = attribute.getProperties().type;
                            if (type !== "frame" && type !== "array" && type !== "tab") {
                                expect(currentValues.data.view.documentData.document.attributes[attribute.id]).toBeDefined("for " + attribute.id);
                            }
                        });
                        done();
                    });
                });

            });

            describe("value", function ()
            {

                it("getValue", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function (value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandox.documentController("getValue", attrid);
                            expect(currentValue).toEqual(value, "for "+attrid);
                        });
                        done();
                    });
                });

                it("getValues", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function (value, attrid)
                        {
                            var values;
                            values = $sandox.documentController("getValues", attrid);
                            _.each(values, function(value, attrid) {
                                expect(currentValues.data.view.documentData.document.attributes[attrid]).toEqual(value, "for " + attrid);
                            });
                        });
                        done();
                    });
                });

                it("setValue", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        $sandox.documentController("setValue", "zoo_date", {value: "12-05-1985"});
                        expect($sandox.documentController("getValue", "zoo_date").value).toEqual("12-05-1985");
                        done();
                    });
                });

                it("changeEvent", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    var mock = getMockFunction();
                    $sandox.documentController("addEventListener", "change", mock.fct);
                    $sandox.documentController("addEventListener", "ready", function ()
                    {
                        $sandox.documentController("setValue", "zoo_date", {value: "12-05-1985"});
                        expect(mock.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

            });

            describe("arrayManipulation", function ()
            {

                describe("append", function ()
                {
                    it("non array attribute", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var appendNonArrayRow = function ()
                            {
                                $sandox.documentController("appendArrayRow", "zoo_date", {"element1": {value: "toto"}});
                            };
                            expect(appendNonArrayRow).toThrowError("Attribute zoo_date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good value", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var appendNonArrayRow = function ()
                            {
                                $sandox.documentController("appendArrayRow", "zoo_array_dates", "toto");
                            };
                            expect(appendNonArrayRow).toThrowError("Values must be an object where each properties is an attribute of the array for zoo_array_dates");
                            done();
                        });
                    });

                    it("add row", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length;
                            $sandox.documentController("appendArrayRow", "zoo_array_dates", {"zoo_date_array": {"value": "12-05-1985"}});
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            expect(_.last(dates).value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });
                });

                describe("insertBeforeArrayRow", function ()
                {
                    it("non array attribute", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var appendNonArrayRow = function ()
                            {
                                $sandox.documentController("insertBeforeArrayRow", "zoo_date", {"element1": {value: "toto"}});
                            };
                            expect(appendNonArrayRow).toThrowError("Attribute zoo_date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good value", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var appendNonArrayRow = function ()
                            {
                                $sandox.documentController("insertBeforeArrayRow", "zoo_array_dates", "toto");
                            };
                            expect(appendNonArrayRow).toThrowError("Values must be an object where each properties is an attribute of the array for zoo_array_dates");
                            done();
                        });
                    });

                    it("non good index", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var appendToBigIndex, appendToLowIndex, dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length;
                            appendToBigIndex = function ()
                            {
                                $sandox.documentController("insertBeforeArrayRow", "zoo_array_dates", {"zoo_date_array": {"value": "12-05-1985"}}, length + 1);
                            };
                            appendToLowIndex = function ()
                            {
                                $sandox.documentController("insertBeforeArrayRow", "zoo_array_dates", {"zoo_date_array": {"value": "12-05-1985"}}, -1);
                            };
                            expect(appendToBigIndex).toThrowError("Index must be between 0 and " + length);
                            expect(appendToLowIndex).toThrowError("Index must be between 0 and " + length);
                            done();
                        });
                    });

                    it("append first row", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length;
                            $sandox.documentController("insertBeforeArrayRow", "zoo_array_dates", {"zoo_date_array": {"value": "12-05-1985"}}, 0);
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            expect(_.first(dates).value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });

                    it("append second row", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length;
                            $sandox.documentController("insertBeforeArrayRow", "zoo_array_dates", {"zoo_date_array": {"value": "12-05-1985"}}, 2);
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            expect(dates[2].value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });
                });

                describe("arrayRemoveRow", function ()
                {

                    it("non array attribute", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var removeNonArrayRow = function ()
                            {
                                $sandox.documentController("removeArrayRow", "zoo_date", 0);
                            };
                            expect(removeNonArrayRow).toThrowError("Attribute zoo_date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good index", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var removeToBigIndex, removeToLowIndex, dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length - 1;
                            removeToBigIndex = function ()
                            {
                                $sandox.documentController("removeArrayRow", "zoo_array_dates", length + 1);
                            };
                            removeToLowIndex = function ()
                            {
                                $sandox.documentController("removeArrayRow", "zoo_array_dates", -1);
                            };
                            expect(removeToBigIndex).toThrowError("Index must be between 0 and " + length + " for zoo_array_dates");
                            expect(removeToLowIndex).toThrowError("Index must be between 0 and " + length + " for zoo_array_dates");
                            done();
                        });

                    });

                    it("remove one row", function (done)
                    {
                        var $sandox = getSandbox();
                        $sandox.documentController(prepareDocumentController(config));
                        $sandox.documentController("addEventListener", "ready", function ()
                        {
                            var dates, length;
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            length = dates.length;
                            $sandox.documentController("removeArrayRow", "zoo_array_dates", 0);
                            dates = $sandox.documentController("getValue", "zoo_date_array");
                            expect(dates.length).toEqual(length - 1, "Number of row");
                            done();
                        });
                    });

                });

            });

        });
    };


});
