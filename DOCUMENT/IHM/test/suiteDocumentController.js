/*global define ,describe, define, beforeEach, expect, it, spyOn, afterEach*/

var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'underscore',
    'jquery',
    'backbone',
    'dcpDocument/test/UnitTestUtilities',
    'text!dcpContextRoot/' + asset + '?app=TEST_DOCUMENT&action=GENERATE_DATA',
    'dcpDocument/widgets/documentController/documentController'], function initDCTest(_, $, Backbone, unitTestUtils, values)
{

    "use strict";

    values = JSON.parse(values);

    return function require_suiteDocumentController(config, documentOptions)
    {
        var currentSandbox, synchro, currentValues, getSandbox = function getSandbox()
        {
            return currentSandbox;
        }, prepareDocumentController = function prepareDocumentController(config)
        {
            config = config || {};
            _.defaults(config, {
                "initid": "document_1",
                "viewId": "!defaultConsultation",
                "noRouter": true
            });
            return config;
        };

        config = config || {};

        describe("documentController : " + config.name, function documentController()
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

            beforeEach(function initSyncBeforeTest(done)
            {
                currentSandbox = unitTestUtils.generateSandBox(config, $("#render"));
                synchro = Backbone.sync;
                Backbone.sync = function mockSynch(method, model, options)
                {
                    var documentId, id = model.id, viewId;
                    var deferred = $.Deferred(), documentIdentifier = "no compatible model class";
                    if (model.typeModel === "ddui:document") {
                        documentId = model.id === "document_2" ? "document_1" : "document_2";
                        viewId = model.get("viewId") === "!coreConsultation" ? "!defaultConsultation" : model.get("viewId");
                        documentIdentifier = documentId + viewId;

                        if (method === "read" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            _.defer(function successRead()
                            {
                                options.success(values[documentIdentifier]);
                                deferred.done(values[documentIdentifier]);
                            });
                            return deferred;
                        }

                        if (method === "update" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            _.defer(function successRead()
                            {
                                options.success(values[documentIdentifier]);
                                deferred.done(values[documentIdentifier]);
                            });
                            return deferred;
                        }

                        if (method === "delete" && !_.isUndefined(values[documentIdentifier])) {
                            currentValues = values[documentIdentifier];
                            _.defer(function successRead()
                            {
                                options.success(values[documentIdentifier]);
                                deferred.done(values[documentIdentifier]);
                            });
                            return deferred;
                        }
                    }

                    if (model.typeModel === "ddui:familyStructure") {
                        documentIdentifier = "testStructure";
                        if (method === "read" && !_.isUndefined(values[documentIdentifier])) {
                            // Defer because document Model is not initialized yet
                            _.defer(function successTestStructure()
                            {
                                options.success(values[documentIdentifier]);
                            });
                            return deferred;
                        }
                    }
                    if (model.typeModel === "ddui:lock" || model.typeModel === "ddui:attributeTab") {
                        _.defer(function successRead()
                        {
                            options.success();
                            deferred.done();
                        });
                        return deferred;
                    }

                    throw new Error("Unable to sync :" + documentIdentifier);
                };
                done();
            });

            afterEach(function cleanAfterTest(done)
            {
                var $sandbox = getSandbox();
                $sandbox.documentController("addEventListener", "destroy", function onDestroy() {
                    done();
                });
                $sandbox.off(".test");
                $sandbox.documentController("destroy");
                Backbone.sync = synchro;
            });

            describe("init", function testDcinit()
            {

                it("ready", function testDcready(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", function testDcready()
                    {
                        expect(true).toBe(true);
                        done();
                    });
                });

                it("reinit", function testDcreinit(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function testDcInit()
                    {
                        var mock = getMockFunction();
                        $sandbox.documentController("addEventListener", "ready", mock.fct);
                        $sandbox.documentController("reinitDocument");
                        //2 because auto launch ready
                        expect(mock.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

            });

            describe("fetch", function testDcfetch()
            {

                it("arguments", function testDcargumentstest_arguments(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function testDcReady()
                    {
                        expect(function testDcFetch()
                        {
                            $sandbox.documentController("fetchDocument", null);
                        }).toThrowError('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
                        done();
                    });
                });

                it("without arguments", function testDcargumentstest_without_arguments(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function testDcReady()
                    {
                        var mockReady = getMockFunction();
                        $sandbox.documentController("addEventListener", "ready", mockReady.fct);
                        $sandbox.documentController("fetchDocument", {"initid": "document_2"});
                        //2 because auto launch ready
                        expect(mockReady.fct.calls.count()).toEqual(1);
                        done();
                    });
                });

                it("events", function testDceventstest_events(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeClose = getMockFunction(), mockClose = getMockFunction(),
                            mockSuccess = getMockFunction(), nbReady = 0;
                        $sandbox.documentController("addEventListener", "ready", function afterReady() {
                            mockReady.fct();
                            nbReady++;
                            if (nbReady === 2) {
                                expect(mockReady.fct.calls.count()).toEqual(2, "ready count");
                                expect(mockBeforeClose.fct.calls.count()).toEqual(1, "before close count");
                                expect(mockClose.fct.calls.count()).toEqual(1, "close count");
                                expect(mockSuccess.fct.calls.count()).toEqual(1, "success count");
                                done();
                            }
                        });
                        $sandbox.documentController("addEventListener", "beforeClose", mockBeforeClose.fct);
                        $sandbox.documentController("addEventListener", "close", mockClose.fct);
                        $sandbox.documentController("fetchDocument", {"initid": "document_2"}, {success: mockSuccess.fct});
                    });
                });

                it("change viewId", function testDcviewId(done)
                {
                    var $sandbox = getSandbox(), documentOptions = prepareDocumentController(documentOptions);
                    $sandbox.documentController(documentOptions);
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function testDcReady()
                    {
                        var mock = getMockFunction(), viewId = documentOptions.viewId === "!coreConsultation" ? "!defaultEdition" : "!coreConsultation";
                        $sandbox.documentController("addEventListener", "ready", mock.fct);

                        $sandbox.documentController("fetchDocument", {
                            "initid": $sandbox.documentController("getProperty", "initid"),
                            "viewId": viewId
                        });
                        //2 because auto launch ready
                        expect(mock.fct.calls.count()).toEqual(1, "ready count");
                        expect($sandbox.documentController("getProperty", "viewId"), viewId);
                        done();
                    });
                });

            });

            describe("save", function testDcsave()
            {

                it("events", function testDceventstest_events(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeSave = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterSave = getMockFunction(), mockSuccess = getMockFunction(), mockValidate = getMockFunction(), nbReady = 0;
                        $sandbox.documentController("addEventListener", "ready", function isReady() {
                            mockReady.fct();
                            nbReady++;
                            if (nbReady === 2) {
                                //2 because auto launch ready
                                expect(mockReady.fct.calls.count()).toEqual(2, "ready count");
                                expect(mockBeforeSave.fct.calls.count()).toEqual(1, "save count");
                                expect(mockClose.fct.calls.count()).toEqual(1, "close count");
                                expect(mockAfterSave.fct.calls.count()).toEqual(1, "after save count");
                                expect(mockSuccess.fct.calls.count()).toEqual(1, "success count");
                                //One for the data from the form, one form the data from the sync
                                expect(mockValidate.fct.calls.count()).toEqual(2, "validate count");
                                done();
                            }
                        });
                        $sandbox.documentController("addEventListener", "validate", mockValidate.fct);
                        $sandbox.documentController("addEventListener", "beforeSave", mockBeforeSave.fct);
                        $sandbox.documentController("addEventListener", "close", mockClose.fct);
                        $sandbox.documentController("addEventListener", "afterSave", mockAfterSave.fct);
                        $sandbox.documentController("saveDocument", {success: mockSuccess.fct});
                    });
                });

            });

            describe("delete", function testDcdelete()
            {

                it("events", function testDceventstest_events(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_launchEvents()
                    {
                        var mockReady = getMockFunction(), mockBeforeDelete = getMockFunction(), mockClose = getMockFunction(),
                            mockAfterDelete = getMockFunction(), mockSuccess = getMockFunction(), nbReady = 0;
                        $sandbox.documentController("addEventListener", "ready", function isReady() {
                            mockReady.fct();
                            nbReady++;
                            if (nbReady === 2) {
                                expect(mockReady.fct.calls.count()).toEqual(2, "ready count");
                                expect(mockBeforeDelete.fct.calls.count()).toEqual(1, "before delete count");
                                expect(mockClose.fct.calls.count()).toEqual(1, "close count");
                                expect(mockAfterDelete.fct.calls.count()).toEqual(1, "after delete count");
                                expect(mockSuccess.fct.calls.count()).toEqual(1, "success count");
                                done();
                            }
                        });
                        $sandbox.documentController("addEventListener", "beforeDelete", mockBeforeDelete.fct);
                        $sandbox.documentController("addEventListener", "close", mockClose.fct);
                        $sandbox.documentController("addEventListener", "afterDelete", mockAfterDelete.fct);
                        $sandbox.documentController("deleteDocument", {success: mockSuccess.fct});
                    });
                });

            });

            describe("properties", function testDcproperties()
            {

                it("getProperty", function testDcgetPropertytest_getProperty(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        _.each(currentValues.data.view.documentData.document.properties, function testDcGetProperty(value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandbox.documentController("getProperty", attrid);
                            expect(currentValue).toEqual(value, "for " + attrid);
                        });
                        done();
                    });
                });

                it("getProperties", function testDcgetPropertiestest_getProperty(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        var properties = $sandbox.documentController("getProperties");
                        _.each(properties, function testDcGetProperty(value, attrid)
                        {
                            if (attrid === "viewId") {
                                expect(documentOptions.viewId).toEqual(value, "for " + attrid);
                                return;
                            }
                            if (attrid === "renderMode") {
                                expect(currentValues.data.view.renderOptions.mode).toEqual(value, "for " + attrid);
                                return;
                            }
                            if (attrid === "isModified") {
                                expect(false).toEqual(value, "for " + attrid);
                                return;
                            }
                            expect(currentValues.data.view.documentData.document.properties[attrid]).toEqual(value, "for " + attrid);
                        });
                        done();
                    });
                });

            });

            describe("attributes", function testDcattributes()
            {

                it("getAttribute", function testDcgetAttributetest_getAttribute(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_attribute()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function testDcattributes(value, attrid)
                        {
                            var currentAttribute;
                            currentAttribute = $sandbox.documentController("getAttribute", attrid);
                            expect(currentAttribute.getValue()).toEqual(value, "for " + attrid);
                        });
                        expect(function testUnkownAttribute()
                        {
                            $sandbox.documentController("getAttribute", "unkown attribute id");
                        }).toThrow();
                        done();
                    });
                });

                it("internalObject", function testDcinternalObjecttest_internalObject(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_attribute()
                    {
                        var attribute = $sandbox.documentController("getAttribute", "test_document_all__f_title");
                        expect(attribute.id).toBeDefined("id");
                        expect(attribute.id).toEqual("test_document_all__f_title", "Test id value");
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

                it("getAttributes", function testDcgetAttributestest_getAttributes(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(documentOptions));
                    $sandbox.documentController("addEventListener", "ready", {"once": true}, function test_property()
                    {
                        var attributes = $sandbox.documentController("getAttributes");
                        _.each(attributes, function testDcattributes(attribute)
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

            describe("value", function testDcvalue()
            {

                it("getValue", function testDcgetValue(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(config));
                    $sandbox.documentController("addEventListener", "ready", function testDcready()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function testDcattributes(value, attrid)
                        {
                            var currentValue;
                            currentValue = $sandbox.documentController("getValue", attrid);
                            expect(currentValue).toEqual(value, "for " + attrid);
                        });
                        done();
                    });
                });

                it("getValues", function testDcgetValues(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(config));
                    $sandbox.documentController("addEventListener", "ready", function testDcready()
                    {
                        _.each(currentValues.data.view.documentData.document.attributes, function testDcValues(value, attrid)
                        {
                            var values;
                            values = $sandbox.documentController("getValues", attrid);
                            _.each(values, function testDcValues(value, attrid)
                            {
                                expect(currentValues.data.view.documentData.document.attributes[attrid]).toEqual(value, "for " + attrid);
                            });
                        });
                        done();
                    });
                });

                it("setValue", function testDcsetValue(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(config));
                    $sandbox.documentController("addEventListener", "ready", function testDcready()
                    {
                        $sandbox.documentController("setValue", "test_document_all__date", {value: "12-05-1985"});
                        expect($sandbox.documentController("getValue", "test_document_all__date").value).toEqual("12-05-1985");
                        done();
                    });
                });

                it("changeEvent", function testDcchangeEvent(done)
                {
                    var $sandbox = getSandbox();
                    $sandbox.documentController(prepareDocumentController(config));
                    $sandbox.documentController("addEventListener", "change", function eventListenerChange() {
                        expect(true).toBe(true);
                        done();
                    });
                    $sandbox.documentController("addEventListener", "ready", function testDcready()
                    {
                        $sandbox.documentController("setValue", "test_document_all__date", {value: "29-03-1985"});
                    });
                });

            });

            describe("arrayManipulation", function testDcarrayManipulation()
            {

                describe("append", function testDcappend()
                {
                    it("non array attribute", function testDcattribute(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var appendNonArrayRow = function testDcappendNonArrayRow()
                            {
                                $sandbox.documentController("appendArrayRow", "test_document_all__date", {"element1": {value: "toto"}});
                            };
                            expect(appendNonArrayRow).toThrowError("Attribute test_document_all__date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good value", function testDcvalue(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var appendNonArrayRow = function testDcappendNonArrayRow()
                            {
                                $sandbox.documentController("appendArrayRow", "test_document_all__array_dates", "toto");
                            };
                            expect(appendNonArrayRow).toThrowError("Values must be an object where each properties is an attribute of the array for test_document_all__array_dates");
                            done();
                        });
                    });

                    it("add row", function testDcrow(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length;
                            $sandbox.documentController("appendArrayRow", "test_document_all__array_dates", {"test_document_all__date_array": {"value": "12-05-1985"}});
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            expect(_.last(dates).value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });
                });

                describe("insertBeforeArrayRow", function testDcinsertBeforeArrayRow()
                {
                    it("non array attribute", function testDcattribute(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var appendNonArrayRow = function testDcappendNonArrayRow()
                            {
                                $sandbox.documentController("insertBeforeArrayRow", "test_document_all__date", {"element1": {value: "toto"}});
                            };
                            expect(appendNonArrayRow).toThrowError("Attribute test_document_all__date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good value", function testDcvalue(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var appendNonArrayRow = function testDcappendNonArrayRow()
                            {
                                $sandbox.documentController("insertBeforeArrayRow", "test_document_all__array_dates", "toto");
                            };
                            expect(appendNonArrayRow).toThrowError("Values must be an object where each properties is an attribute of the array for test_document_all__array_dates");
                            done();
                        });
                    });

                    it("non good index", function testDcindex(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var appendToBigIndex, appendToLowIndex, dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length;
                            appendToBigIndex = function appendToBigIndex()
                            {
                                $sandbox.documentController("insertBeforeArrayRow", "test_document_all__array_dates", {"test_document_all__date_array": {"value": "12-05-1985"}}, length + 1);
                            };
                            appendToLowIndex = function appendToLowIndex()
                            {
                                $sandbox.documentController("insertBeforeArrayRow", "test_document_all__array_dates", {"test_document_all__date_array": {"value": "12-05-1985"}}, -1);
                            };
                            expect(appendToBigIndex).toThrowError("Index must be between 0 and " + length);
                            expect(appendToLowIndex).toThrowError("Index must be between 0 and " + length);
                            done();
                        });
                    });

                    it("append first row", function testDcrow(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length;
                            $sandbox.documentController("insertBeforeArrayRow", "test_document_all__array_dates", {"test_document_all__date_array": {"value": "12-05-1985"}}, 0);
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            expect(_.first(dates).value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });

                    it("append second row", function testDcrow(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length;
                            $sandbox.documentController("insertBeforeArrayRow", "test_document_all__array_dates", {"test_document_all__date_array": {"value": "12-05-1985"}}, 2);
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            expect(dates[2].value).toEqual("12-05-1985", "Values of new cell");
                            expect(dates.length).toEqual(length + 1, "Number of row");
                            done();
                        });
                    });
                });

                describe("arrayRemoveRow", function testDcarrayRemoveRow()
                {

                    it("non array attribute", function testDcattribute(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var removeNonArrayRow = function removeNonArrayRow()
                            {
                                $sandbox.documentController("removeArrayRow", "test_document_all__date", 0);
                            };
                            expect(removeNonArrayRow).toThrowError("Attribute test_document_all__date must be an attribute of type array");
                            done();
                        });
                    });

                    it("non good index", function testDcindex(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var removeToBigIndex, removeToLowIndex, dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length - 1;
                            removeToBigIndex = function removeToBigIndex()
                            {
                                $sandbox.documentController("removeArrayRow", "test_document_all__array_dates", length + 1);
                            };
                            removeToLowIndex = function removeToLowIndex()
                            {
                                $sandbox.documentController("removeArrayRow", "test_document_all__array_dates", -1);
                            };
                            expect(removeToBigIndex).toThrowError("Index must be between 0 and " + length + " for test_document_all__array_dates");
                            expect(removeToLowIndex).toThrowError("Index must be between 0 and " + length + " for test_document_all__array_dates");
                            done();
                        });

                    });

                    it("remove one row", function testDcrow(done)
                    {
                        var $sandbox = getSandbox();
                        $sandbox.documentController(prepareDocumentController(config));
                        $sandbox.documentController("addEventListener", "ready", function testDcready()
                        {
                            var dates, length;
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            length = dates.length;
                            $sandbox.documentController("removeArrayRow", "test_document_all__array_dates", 0);
                            dates = $sandbox.documentController("getValue", "test_document_all__date_array");
                            expect(dates.length).toEqual(length - 1, "Number of row");
                            done();
                        });
                    });

                });

            });

        });
    };

});
