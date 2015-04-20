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

        describe("documentController : "+config.name, function ()
        {

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
                        var mock = {
                            "fct": function ()
                            {
                            }
                        };
                        spyOn(mock, "fct");
                        $sandox.documentController("addEvent", "ready", mock.fct);
                        $sandox.documentController("reinitDocument");
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

            describe("value", function ()
            {

                it("getValue", function (done)
                {
                    var $sandox = getSandbox();
                    $sandox.documentController(prepareDocumentController(config));
                    $sandox.documentController("addEvent", "ready", function ()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function(value, attrid) {
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
                        _.each(currentValues.data.view.documentData.document.attributes, function (value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandox.documentController("getValue", attrid);
                            expect(currentValue).toEqual(value);
                        });
                        done();
                    });
                });

            });

        });
    };

    return documentControllerTest;

});
