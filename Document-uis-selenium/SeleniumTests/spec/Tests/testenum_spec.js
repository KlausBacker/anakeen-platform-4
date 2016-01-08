var webdriver = require('selenium-webdriver'),
    driver = require("../lib/initDriver.js"),
    util = require("../lib/libTesting.js"),
    docForm = require("../lib/libDocForm.js");

describe('Dynacase Enum test', function formAllEdit()
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
        console.log("before main");
        currentDriver = driver.getDriver();
        webdriver.promise.controlFlow().on('uncaughtException', handleException);
        currentDriver.get(driver.rootUrl).then(function x()
        {
            util.login("admin", "anakeen").then(function afterLogin()
            {
                console.log("login end");

                currentDriver.wait(function waitMainInterfaceIsDisplayed()
                {
                    return currentDriver.isElementPresent(webdriver.By.css(".css-disconnect-button"));
                }, 5000);

                currentDriver.get(driver.rootUrl + "?app=DOCUMENT&initid=TST_DDUI_ENUM&viewId=!defaultCreation");
                docForm.setDocWindow(); // Init driver variables

                currentDriver.wait(function waitDocumentIsDisplayed()
                {
                    return currentDriver.isElementPresent(webdriver.By.css(".dcpDocument__frames"));
                }, 5000);

                currentDriver.wait(function waitLoadingDone()
                {
                    return webdriver.until.elementIsNotVisible(webdriver.By.css(".dcpLoading"));
                }, 5000).then(function doneInitMainPage()
                {
                    console.log("begin test");
                    beforeDone();
                });

            });
        });
    });

    afterAll(function afterFormAllEdit(afterDone)
    {
        console.log("Exiting... in 10s");
        webdriver.promise.controlFlow().removeListener(handleException);
        currentDriver.sleep(10000); // Wait to see result
        currentDriver.quit().then(afterDone);

    });

    it("mainInput", function testMainInput(itDone)
    {
        var now = new Date();
        now.setTime(now.getTime() + (now.getHours() - now.getUTCHours()) * 3600000);

        var refValue = now.toISOString().substr(0, 19) + " " + driver.browser;

        //------------------------------------------
        // Text : test_ddui_enum__title
        docForm.setTextValue({
            attrid: 'test_ddui_enum__titleref',
            rawValue: refValue,
            expectedValue: refValue
        });

        docForm.setEnumListValue({
            attrid: 'test_ddui_enum__title',
            selectedText: "Jaune",
            expectedValue: "yellow"
        });

        util.saveScreenshot("enumRef").then(itDone);
    });

    it("setEnumDirectTab", function setEnumDirectTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_enums'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__enumcountry', selectedText: "Albanie", expectedValue: "AL"});

        docForm.setEnumAutoValue({
            attrid: 'test_ddui_enum__enumtext',
            selectedText: "Vert",
            filterText: "V",
            expectedValue: "green"
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "30 %", expectedValue: "30"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Normal", expectedValue: "C"});

        // Multiple
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "A",
            selectedText: "Albanie",
            expectedValue: ["AL"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "bel",
            selectedText: "Belgique",
            expectedValue: ["AL", "BE"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie",
            expectedValue: ["AL", "BE", "NC"]
        });

        docForm.selectEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "30 %", expectedValue: ["30"]});
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "70 %",
            expectedValue: ["30", "70"]
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "V",
            selectedText: "Vert",
            expectedValue: ["green"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "J",
            selectedText: "Jaune",
            expectedValue: ["green", "yellow"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine",
            expectedValue: ["green", "yellow", "navyblue"]
        });

        util.saveScreenshot("enumDirectTab").then(itDone);
    });

    it("setEnumSrvTab", function setEnumSrvTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_srv'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__srvcountry', selectedText: "Albanie", expectedValue: "AL"});

        docForm.setEnumAutoValue({
            attrid: 'test_ddui_enum__srvtext',
            selectedText: "Vert",
            filterText: "V",
            expectedValue: "green"
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvnumber', label: "1/3", expectedValue: "33.3333"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvbool', label: "Normal", expectedValue: "C"});

        // Multiple
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "A",
            selectedText: "Albanie",
            expectedValue: ["AL"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "bel",
            selectedText: "Belgique",
            expectedValue: ["AL", "BE"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie",
            expectedValue: ["AL", "BE", "NC"]
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber',
            label: "30 %",
            expectedValue: ["30"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber',
            label: "70 %",
            expectedValue: ["30", "70"]
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "V",
            selectedText: "Vert",
            expectedValue: ["green"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "J",
            selectedText: "Jaune",
            expectedValue: ["green", "yellow"]
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine",
            expectedValue: ["green", "yellow", "navyblue"]
        });

        util.saveScreenshot("enumServerTab").then(itDone);
    });

    it("setArrayEnumTab", function setArrayEnumTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_arrays'});

        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});

        // First row
        docForm.setEnumListValue({
            index: 0,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Andorre",
            expectedValue: "AD"
        });
        docForm.setEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumtext_array',
            selectedText: "La",
            expectedValue: "A"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "1/3",
            expectedValue: "33.3333"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expectedValue: "D"
        });

        // Second row
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expectedValue: "D"
        });
        docForm.setEnumListValue({
            index: 1,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Emirat",
            expectedValue: "AE"
        });
        docForm.setEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "S",
            selectedText: "Si",
            expectedValue: "Bb"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expectedValue: "66.6667"
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expectedValue: "S"
        });

        // Third row
        docForm.setEnumListValue({
            index: 2,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Barbade",
            expectedValue: "AG"
        });
        docForm.setEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expectedValue: "G"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "3/3",
            expectedValue: "100"
        });

        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expectedValue: "D"
        });

        // ------------------------
        // Second array
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            selectedText: "Barbade",
            expectedValue: ["AG"]
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Bel",
            selectedText: "Belgique",
            expectedValue: ["AG", "BE"]
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La",
            expectedValue: ["A"]
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expectedValue: ["A", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expectedValue: ["100"]
        });

        // Second row

        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Z",
            selectedText: "Zambie",
            expectedValue: ["ZM"]
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Y",
            selectedText: "Yemen",
            expectedValue: ["ZM", "YE"]
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La",
            expectedValue: ["A"]
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Si",
            expectedValue: ["A", "Bb"]
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do",
            expectedValue: ["A", "Bb", "C"]
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expectedValue: ["A", "Bb", "C", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "1/3",
            expectedValue: ["33.3333"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expectedValue: ["33.3333", "66.6667"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expectedValue: ["33.3333", "66.6667", "100"]
        });
        // Third row

        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Lucie",
            expectedValue: ["LC"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Hélène",
            expectedValue: ["LC", "SH"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Tomé",
            expectedValue: ["LC", "SH", "ST"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Pierre",
            expectedValue: ["LC", "SH", "ST", "PM"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Vincent",
            expectedValue: ["LC", "SH", "ST", "PM", "VC"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "La",
            selectedText: "Lab",
            expectedValue: ["Ab"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Sib",
            expectedValue: ["Ab", "Bb"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do#",
            expectedValue: ["Ab", "Bb", "Cd"]
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expectedValue: ["Ab", "Bb", "Cd", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "0",
            expectedValue: ["0"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expectedValue: ["0", "66.6667"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expectedValue: ["0", "66.6667", "100"]
        });
        util.saveScreenshot("enumArrayTab").then(itDone);
    });

    it("createEnumDocAndReopen", function createEnumDoc(itDone)
    {
        docForm.createAndClose();
        util.saveScreenshot("enumView");

        docForm.openMenu({listMenu: "Tests", itemMenu: "Vertical"});
        currentDriver.wait(function waitLoadingDone()
        {
            return webdriver.until.elementIsVisible(webdriver.By.css(".dcpDocument--edit"));
        }, 5000);
        currentDriver.sleep(1000).then(itDone); // Wait to see result
    });

    it("setVerticalEnumDirectTab", function setVerticalEnumDirectTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_enums'});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumcountry', label: "Danemark", expectedValue: "DK"});

        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumtext', label: "Bleu", expectedValue: "blue"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "70 %", expectedValue: "70"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Critique", expectedValue: "C"});

        // Multiple
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumscountry',
            label: "Chili",
            expectedValue: ["AL", "BE", "CL", "NC"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumscountry',
            label: "Cuba",
            expectedValue: ["AL", "BE", "CL", "CU", "NC"]
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "30 %",
            expectedValue: ["70"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "100 %",
            expectedValue: ["70", "100"]
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumstext',
            label: "Rouge",
            expectedValue: ["red", "yellow", "green", "navyblue"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumstext',
            label: "Jaune",
            expectedValue: ["red", "green", "navyblue"]
        });

        util.saveScreenshot("enumVerticalDirectTab").then(itDone);
    });

    it("setVerticalEnumServerTab", function setVerticalEnumServerTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_srv'});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvcountry', label: "Arménie", expectedValue: "AM"});

        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvtext', label: "Rouge", expectedValue: "red"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvnumber', label: "2/3", expectedValue: "66.6667"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvbool', label: "Normal", expectedValue: "N"});

        // Multiple
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvscountry', label: "Chili",
            expectedValue: ["AL", "BE", "CL", "NC"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvscountry', label: "Cuba",
            expectedValue: ["AL", "BE", "CL", "CU", "NC"]
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber', label: "30 %",
            expectedValue: ["70"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber', label: "70 %",
            expectedValue: []
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Rouge",
            expectedValue: ["red", "yellow", "green", "navyblue"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Bleu",
            expectedValue: ["red", "yellow", "green", "blue", "navyblue"]
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Jaune",
            expectedValue: ["red", "green", "blue", "navyblue"]
        });

        util.saveScreenshot("enumVerticalServerTab").then(itDone);
    });

    it("setVerticalArrayEnumTab", function setVerticalArrayEnumTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_arrays'});
        // First row
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumcountry_array',
            label: "Albanie",
            expectedValue: "AL"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumtext_array',
            label: "Si",
            expectedValue: "Bb"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expectedValue: "66.6667"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expectedValue: "D"
        });

        // Second row
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expectedValue: "S"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumcountry_array',
            label: "Suisse",
            expectedValue: "CH"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumtext_array',
            label: "Si",
            expectedValue: "Bb"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expectedValue: "66.6667"
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expectedValue: "D"
        });

        // Third row
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumcountry_array',
            label: "Bulgarie",
            expectedValue: "BG"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "Sol",
            label: "Sol",
            expectedValue: "G"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "3/3",
            expectedValue: "100"
        });

        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expectedValue: "S"
        });

        // ------------------------
        // Second array

        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Bahr",
            expectedValue: ["AG", "BE", "BH"]
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Belgique",
            expectedValue: ["AG", "BH"]
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "La",
            expectedValue: ["G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Sib",
            expectedValue: ["Bb", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expectedValue: ["66.6667", "100"]
        });

        // Second row
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Aruba",
            expectedValue: ["AW", "YE", "ZM"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Australie",
            expectedValue: ["AU", "AW", "YE", "ZM"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mi",
            expectedValue: ["A", "Bb", "C", "Eb", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mib",
            expectedValue: ["A", "Bb", "C", "G"]
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expectedValue: ["33.3333", "66.6667"]
        });
        // Third row
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Argentine",
            expectedValue: ["AR", "LC", "PM", "SH", "ST", "VC"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Australie",
            expectedValue: ["AR", "AU", "LC", "PM", "SH", "ST", "VC"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Do",
            expectedValue: ["Bb", "C", "Cd", "G", "Ab"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mib",
            expectedValue: ["Bb", "C", "Cd", "Eb", "G", "Ab"]
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expectedValue: ["0", "100"]
        });

        util.saveScreenshot("enumVerticalArray").then(itDone);
    });

    it('saveEnumDoc', function testsetMainInputs(itDone)
    {
        console.log("save document");

        docForm.saveAndClose();
        util.saveScreenshot("enumView 2");
        console.log("end setEnumInputs");

        currentDriver.sleep(10).then(itDone);

    });
});