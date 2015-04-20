/*global define ,describe, define, beforeEach, setFixtures, expect, it, sandbox, spyOn, jasmine, afterEach*/
define([
    'underscore',
    'jquery',
    'backbone',
    'dcpDocument/test/UnitTestUtilities',
    'dcpDocument/test/valuesDocuments',
    'dcpDocument/widgets/documentController/documentController'], function initDCTest(_, $, Backbone, unitTestUtils, values)
{

    "use strict";

    var documentControllerTest = function (config, documentOptions)
    {

        var currentSandbox, synchro, currentValues, getSandbox = function ()
        {
            return currentSandbox;
        }, prepareDocumentController = function prepareDocumentController(config)
        {
            config = config || {};
            _.defaults(config, {
                "initid": "1081",
                "viewId": "!coreConsultation",
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

                    var documentIdentifier = model.id + model.get("viewId");

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
                    throw new Error("Unable to sync");
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
                    $sandox.documentController("addEvent", "ready", function ()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                });

                it("reinit", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEvent", "ready", {"once": true}, function ()
                    {
                        var mock = getMockFunction();
                        $sandox.documentController("addEvent", "ready", mock.fct);
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
                    $sandox.documentController("addEvent", "destroy", function ()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                    $sandox.documentController("addEvent", "ready", function ()
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
                    $sandox.documentController("addEvent", "ready", {"once" : true}, function ()
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
                    $sandox.documentController("addEvent", "ready", {"once": true}, function ()
                    {
                        var mockReady = getMockFunction();
                        $sandox.documentController("addEvent", "ready", mockReady.fct);
                        $sandox.documentController("fetchDocument");
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2);
                        done();
                    });
                });

                it("events", function test_events(done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(documentOptions));
                    $sandox.documentController("addEvent", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeClose = getMockFunction(), mockClose = getMockFunction(),
                            mockSuccess = getMockFunction();
                        $sandox.documentController("addEvent", "ready", mockReady.fct);
                        $sandox.documentController("addEvent", "beforeClose", mockBeforeClose.fct);
                        $sandox.documentController("addEvent", "close", mockClose.fct);
                        $sandox.documentController("fetchDocument", {}, {success: mockSuccess.fct});
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
                    $sandox.documentController("addEvent", "ready", {"once": true}, function ()
                    {
                        var mock = getMockFunction(), viewId = documentOptions.viewId === "!coreConsultation" ? "!defaultEdition" : "!coreConsultation";
                        $sandox.documentController("addEvent", "ready", mock.fct);

                        $sandox.documentController("fetchDocument", {"viewId" : viewId});
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
                    $sandox.documentController("addEvent", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeSave = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterSave = getMockFunction(), mockSuccess = getMockFunction();
                        $sandox.documentController("addEvent", "ready", mockReady.fct);
                        $sandox.documentController("addEvent", "beforeSave", mockBeforeSave.fct);
                        $sandox.documentController("addEvent", "close", mockClose.fct);
                        $sandox.documentController("addEvent", "afterSave", mockAfterSave.fct);
                        $sandox.documentController("saveDocument", {success: mockSuccess.fct});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(2);
                        expect(mockBeforeSave.fct.calls.count()).toEqual(1);
                        expect(mockClose.fct.calls.count()).toEqual(1);
                        expect(mockAfterSave.fct.calls.count()).toEqual(1);
                        expect(mockSuccess.fct.calls.count()).toEqual(1);
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
                    $sandox.documentController("addEvent", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeDelete = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterDelete = getMockFunction(), mockSuccess = getMockFunction();
                        $sandox.documentController("addEvent", "ready", mockReady.fct);
                        $sandox.documentController("addEvent", "beforeDelete", mockBeforeDelete.fct);
                        $sandox.documentController("addEvent", "close", mockClose.fct);
                        $sandox.documentController("addEvent", "afterDelete", mockAfterDelete.fct);
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

            describe("value", function ()
            {

                it("getValue", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEvent", "ready", function ()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function (value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandox.documentController("getValue", attrid);
                            expect(currentValue).toEqual(value);
                        });
                        done();
                    });
                });

                it("setValue", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEvent", "ready", function ()
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
                    $sandox.documentController("addEvent", "change", mock.fct);
                    $sandox.documentController("addEvent", "ready", function ()
                    {
                        $sandox.documentController("setValue", "zoo_date", {value: "12-05-1985"});
                        expect(mock.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

            });

        });
    };

    return documentControllerTest;

});
