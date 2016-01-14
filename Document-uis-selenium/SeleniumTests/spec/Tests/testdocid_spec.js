var webdriver = require('selenium-webdriver'),
    driver = require("../lib/initDriver.js"),
    util = require("../lib/libTesting.js"),
    docForm = require("../lib/libDocForm.js"),
    docidTest = require("../lib/libDocidTest.js");

describe('Dynacase Docid test', function formAllEdit()
{
    'use strict';
    var currentDriver, handleException = function handleException(e)
    {

        jasmine.DEFAULT_TIMEOUT_INTERVAL = 50;
        console.error('Unhandled error: ', e);
        expect(false).toBe(null);
    };

    jasmine.DEFAULT_TIMEOUT_INTERVAL = 500000; // en ms : 3min

    beforeAll(function beforeFormAllEdit(beforeDone)
    {
        var url = driver.rootUrl + "?app=DOCUMENT&initid=TST_DDUI_DOCID&viewId=!defaultCreation";

        console.log("Begin testing");
        currentDriver = driver.getDriver();
        webdriver.promise.controlFlow().on('uncaughtException', handleException);

        currentDriver.get(url).then(function x()
        {
            util.login("admin", "anakeen").then(function afterLogin()
            {
                docForm.setDocWindow(); // Init driver variables
                docidTest.initDriver();

                currentDriver.wait(function waitDocumentIsDisplayed()
                {
                    return currentDriver.isElementPresent(webdriver.By.css(".dcpDocument__frames"));
                }, 10000);

                currentDriver.wait(function waitLoadingDone()
                {
                    return webdriver.until.elementIsNotVisible(webdriver.By.css(".dcpLoading"));
                }, 5000).then(function doneInitMainPage()
                {
                    console.log("Document loaded");
                    beforeDone();
                });

            });
        });
    });

    afterAll(function afterFormAllEdit(afterDone)
    {
        console.log("Exiting... in 10s");
        webdriver.promise.controlFlow().removeListener('uncaughtException', handleException);
        currentDriver.sleep(10000); // Wait to see result
        driver.quit().then(afterDone);

    });

    it("mainInput", function testMainInput(itDone)
    {
        var now = new Date();
        now.setTime(now.getTime() + (now.getHours() - now.getUTCHours()) * 3600000);

        var refValue = now.toISOString().substr(0, 19) + " " + driver.browser;

        //------------------------------------------
        // Text : test_ddui_docid__title
        docForm.setTextValue({
            attrid: 'test_ddui_docid__titleref',
            rawValue: refValue,
            expected: {value: refValue}
        });

        docForm.setDocidValue({
            attrid: 'test_ddui_docid__title',
            selectedText: "Premier",
            filterText: "référence",
            expected: {displayValue: "Premier document de référence"}
        });

        util.saveScreenshot("docidRef").then(itDone);
    });

    it("setDocidInputs", function setDocidInputs(itDone)
    {
        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single1',
            selectedText: "Premier",
            filterText: "référence",
            expected: {displayValue: "Premier document de référence"}
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single2',
            selectedText: "Deux",
            filterText: "référence",
            expected: {displayValue: "Deuxième document de référence"}
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single3',
            selectedText: "Trois",
            filterText: "référence",
            expected: {displayValue: "Troisième document de référence"}
        });

        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple1',
            selectedText: "Premier",
            filterText: "référence",
            expected: {displayValue: ["Premier document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple1',
            selectedText: "Deux",
            filterText: "référence",
            expected: {displayValue: ["Premier document de référence", "Deuxième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple1',
            selectedText: "Trois",
            filterText: "référence",
            expected: {displayValue: ["Premier document de référence", "Deuxième document de référence", "Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple1',
            selectedText: "Quatr",
            filterText: "référence",
            expected: {displayValue: ["Premier document de référence", "Deuxième document de référence", "Troisième document de référence", "Quatrième document de référence"]}
        });

        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple2',
            selectedText: "Quatr",
            filterText: "référence",
            expected: {displayValue: ["Quatrième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple2',
            selectedText: "Trois",
            filterText: "référence",
            expected: {displayValue: ["Quatrième document de référence", "Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple2',
            selectedText: "Deux",
            filterText: "référence",
            expected: {displayValue: ["Quatrième document de référence", "Troisième document de référence", "Deuxième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple2',
            selectedText: "Premier",
            filterText: "référence",
            expected: {displayValue: ["Quatrième document de référence", "Troisième document de référence", "Deuxième document de référence", "Premier document de référence"]}
        });

        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple3',
            selectedText: "Trois",
            filterText: "référence",
            expected: {displayValue: ["Troisième document de référence"]}
        });

        docForm.addRow({attrid: 'test_ddui_docid__t_rels'});
        docForm.addRow({attrid: 'test_ddui_docid__t_rels'});
        docForm.addRow({attrid: 'test_ddui_docid__t_rels'});

        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single_array',
            selectedText: "Premier",
            filterText: "référence",
            index: 0,
            expected: {displayValue: "Premier document de référence"}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Trois",
            filterText: "référence",
            index: 0,
            expected: {displayValue: ["Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Premier",
            filterText: "référence",
            index: 0,
            expected: {displayValue: ["Troisième document de référence", "Premier document de référence"]}
        });

        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single_array',
            selectedText: "Deux",
            filterText: "référence",
            index: 1,
            expected: {displayValue: "Deuxième document de référence"}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Trois",
            filterText: "référence",
            index: 1,
            expected: {displayValue: ["Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Deux",
            filterText: "référence",
            index: 1,
            expected: {displayValue: ["Troisième document de référence", "Deuxième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Quat",
            filterText: "référence",
            index: 1,
            expected: {displayValue: ["Troisième document de référence", "Deuxième document de référence", "Quatrième document de référence"]}
        });

        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single_array',
            selectedText: "Premier",
            filterText: "référence",
            index: 2,
            expected: {displayValue: "Premier document de référence"}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Trois",
            filterText: "référence",
            index: 2,
            expected: {displayValue: ["Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_array',
            selectedText: "Quat",
            filterText: "référence",
            index: 2,
            expected: {displayValue: ["Troisième document de référence", "Quatrième document de référence"]}
        });

        docForm.addRow({attrid: 'test_ddui_docid__t_links'});
        docForm.addRow({attrid: 'test_ddui_docid__t_links'});

        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single_link',
            selectedText: "Premier",
            filterText: "référence",
            index: 0,
            expected: {displayValue: "Premier document de référence"}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_link',
            selectedText: "Trois",
            filterText: "référence",
            index: 0,
            expected: {displayValue: ["Troisième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_link',
            selectedText: "Premier",
            filterText: "référence",
            index: 0,
            expected: {displayValue: ["Troisième document de référence", "Premier document de référence"]}
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_docid__single_link',
            selectedText: "Deux",
            filterText: "référence",
            index: 1,
            expected: {displayValue: "Deuxième document de référence"}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_link',
            selectedText: "Deux",
            filterText: "référence",
            index: 1,
            expected: {displayValue: ["Deuxième document de référence"]}
        });
        docForm.addDocidValue({
            attrid: 'test_ddui_docid__multiple_link',
            selectedText: "Premier",
            filterText: "référence",
            index: 1,
            expected: {displayValue: ["Deuxième document de référence", "Premier document de référence"]}
        });

        util.saveScreenshot("docidInputs").then(itDone);
    });

    it('createDocidDoc', function testcreateDocidDoc(itDone)
    {
        console.log("create document");

        docForm.createAndClose();
        util.saveScreenshot("docIdView 2").then(itDone);

    });

    it("verifyDocidDisplay", function verifyDocidDisplay(itDone)
    {

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__title',
            expected: {
                displayText: "Premier document de référence"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single1',
            expected: {
                displayText: "Premier document de référence"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single2',
            expected: {
                displayText: "Deuxième document de référence (H)"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single3',
            expected: {
                displayText: "Troisième document de référence (P)"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple1',
            expected: {
                displayText: "Premier document de référence\nDeuxième document de référence\nTroisième document de référence\nQuatrième document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__histo1',
            expected: {
                displayText: "Voir l'historique\n\"Premier document de référence\""
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple2',
            expected: {
                displayText: "Quatrième document de référence (H)\nTroisième document de référence (H)\nDeuxième document de référence (H)\nPremier document de référence (H)"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple3',
            expected: {
                displayText: "Troisième document de référence (P)"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single_array',
            index: 0,
            expected: {
                displayText: "Premier document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single_array',
            index: 1,
            expected: {
                displayText: "Deuxième document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single_array',
            index: 2,
            expected: {
                displayText: "Premier document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple_array',
            index: 0,
            expected: {
                displayText: "Troisième document de référence\nPremier document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple_array',
            index: 1,
            expected: {
                displayText: "Troisième document de référence\nDeuxième document de référence\nQuatrième document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple_array',
            index: 2,
            expected: {
                displayText: "Troisième document de référence\nQuatrième document de référence"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single_link',
            index: 0,
            expected: {
                displayText: "Premier document de référence (P)"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__single_link',
            index: 1,
            expected: {
                displayText: "Deuxième document de référence (P)"
            }
        });

        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple_link',
            index: 0,
            expected: {
                displayText: "Troisième document de référence (P)\nPremier document de référence (P)"
            }
        });
        docForm.verifyAttributeDisplay({
            attrid: 'test_ddui_docid__multiple_link',
            index: 1,
            expected: {
                displayText: "Deuxième document de référence (P)\nPremier document de référence (P)"
            }
        });

        util.saveScreenshot("docidDisplayValue").then(itDone);
    });

    it("clickOnRelation", function clickOnRelation(itDone)
    {
        docidTest.verifyClick({
            attrid: "test_ddui_docid__title",
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single1",
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__histo1",
            expected: {
                historicTitle: "Historique de \"Premier document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single2",
            expected: {
                historicTitle: "Historique de \"Deuxième document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single3",
            expected: {
                propertiesTitle: "Propriétés de \"Troisième document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple1",
            index: 0,
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple1",
            index: 1,
            expected: {
                documentTitle: "- Deuxième document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple1",
            index: 2,
            expected: {
                documentTitle: "- Troisième document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple1",
            index: 3,
            expected: {
                documentTitle: "- Quatrième document de référence -"
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple2",
            index: 0,
            expected: {
                historicTitle: "Historique de \"Quatrième document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple2",
            index: 1,
            expected: {
                historicTitle: "Historique de \"Troisième document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple2",
            index: 2,
            expected: {
                historicTitle: "Historique de \"Deuxième document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple2",
            index: 3,
            expected: {
                historicTitle: "Historique de \"Premier document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple3",
            index: 0,
            expected: {
                propertiesTitle: "Propriétés de \"Troisième document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single_array",
            rowIndex: 0,
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single_array",
            rowIndex: 1,
            expected: {
                documentTitle: "- Deuxième document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single_array",
            rowIndex: 2,
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 0,
            index:0,
            expected: {
                documentTitle: "- Troisième document de référence -"
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 0,
            index:1,
            expected: {
                documentTitle: "- Premier document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 1,
            index:0,
            expected: {
                documentTitle: "- Troisième document de référence -"
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 1,
            index:1,
            expected: {
                documentTitle: "- Deuxième document de référence -"
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 1,
            index:2,
            expected: {
                documentTitle: "- Quatrième document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 2,
            index:0,
            expected: {
                documentTitle: "- Troisième document de référence -"
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_array",
            rowIndex: 2,
            index:1,
            expected: {
                documentTitle: "- Quatrième document de référence -"
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single_link",
            rowIndex: 0,
            expected: {
                propertiesTitle: "Propriétés de \"Premier document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__single_link",
            rowIndex: 1,
            expected: {
                propertiesTitle: "Propriétés de \"Deuxième document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__link_histo",
            rowIndex: 0,
            expected: {
                historicTitle: "Historique de \"Premier document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__link_histo",
            rowIndex: 1,
            expected: {
                historicTitle: "Historique de \"Deuxième document de référence\""
            }
        });

        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_link",
            rowIndex: 0,
            index: 0,
            expected: {
                propertiesTitle: "Propriétés de \"Troisième document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_link",
            rowIndex: 0,
            index: 1,
            expected: {
                propertiesTitle: "Propriétés de \"Premier document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_link",
            rowIndex: 1,
            index: 0,
            expected: {
                propertiesTitle: "Propriétés de \"Deuxième document de référence\""
            }
        });
        docidTest.verifyClick({
            attrid: "test_ddui_docid__multiple_link",
            rowIndex: 1,
            index: 1,
            expected: {
                propertiesTitle: "Propriétés de \"Premier document de référence\""
            }
        });

        util.saveScreenshot("docidLinks").then(itDone);
    });
})
;