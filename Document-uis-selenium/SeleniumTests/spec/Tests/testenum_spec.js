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
        driver.quit().then(afterDone);

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
            expected: {value: refValue}
        });

        docForm.setEnumListValue({
            attrid: 'test_ddui_enum__title',
            selectedText: "Jaune",
            expected: {value: "yellow"}
        });

        util.saveScreenshot("enumRef").then(itDone);
    });

    it("setEnumDirectTab", function setEnumDirectTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_enums'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__enumcountry', selectedText: "Albanie", expected: {value: "AL"}});

        docForm.setEnumAutoValue({
            attrid: 'test_ddui_enum__enumtext',
            selectedText: "Vert",
            filterText: "V",
            expected: {value: "green"}
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "30 %", expected: {value: "30"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Normal", expected: {value: "C"}});

        // Multiple
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "A",
            selectedText: "Albanie",
            expected: {value: ["AL"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "bel",
            selectedText: "Belgique",
            expected: {value: ["AL", "BE"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie",
            expected: {value: ["AL", "BE", "NC"]}
        });

        docForm.selectEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "30 %", expected: {value: ["30"]}});
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "70 %",
            expected: {value: ["30", "70"]}
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "V",
            selectedText: "Vert",
            expected: {value: ["green"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "J",
            selectedText: "Jaune",
            expected: {value: ["green", "yellow"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine",
            expected: {value: ["green", "yellow", "navyblue"]}
        });

        util.saveScreenshot("enumDirectTab").then(itDone);
    });

    it("setEnumSrvTab", function setEnumSrvTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_srv'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__srvcountry', selectedText: "Albanie", expected: {value: "AL"}});

        docForm.setEnumAutoValue({
            attrid: 'test_ddui_enum__srvtext',
            selectedText: "Vert",
            filterText: "V",
            expected: {value: "green"}
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvnumber', label: "1/3", expected: {value: "33.3333"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvbool', label: "Normal", expected: {value: "C"}});

        // Multiple
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "A",
            selectedText: "Albanie",
            expected: {value: ["AL"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "bel",
            selectedText: "Belgique",
            expected: {value: ["AL", "BE"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie",
            expected: {value: ["AL", "BE", "NC"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber',
            label: "30 %",
            expected: {value: ["30"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber',
            label: "70 %",
            expected: {value: ["30", "70"]}
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "V",
            selectedText: "Vert",
            expected: {value: ["green"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "J",
            selectedText: "Jaune",
            expected: {value: ["green", "yellow"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine",
            expected: {value: ["green", "yellow", "navyblue"]}
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
            expected: {value: "AD"}
        });
        docForm.setEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumtext_array',
            selectedText: "La",
            expected: {value: "A"}
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "1/3",
            expected: {value: "33.3333"}
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expected: {value: "D"}
        });

        // Second row
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expected: {value: "D"}
        });
        docForm.setEnumListValue({
            index: 1,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Emirat",
            expected: {value: "AE"}
        });
        docForm.setEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "S",
            selectedText: "Si",
            expected: {value: "Bb"}
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expected: {value: "66.6667"}
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expected: {value: "S"}
        });

        // Third row
        docForm.setEnumListValue({
            index: 2,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Barbade",
            expected: {value: "AG"}
        });
        docForm.setEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expected: {value: "G"}
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "3/3",
            expected: {value: "100"}
        });

        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expected: {value: "D"}
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
            expected: {value: ["AG"]}
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Bel",
            selectedText: "Belgique",
            expected: {value: ["AG", "BE"]}
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La",
            expected: {value: ["A"]}
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expected: {value: ["A", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expected: {value: ["100"]}
        });

        // Second row

        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Z",
            selectedText: "Zambie",
            expected: {value: ["ZM"]}
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Y",
            selectedText: "Yemen",
            expected: {value: ["ZM", "YE"]}
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La",
            expected: {value: ["A"]}
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Si",
            expected: {value: ["A", "Bb"]}
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do",
            expected: {value: ["A", "Bb", "C"]}
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expected: {value: ["A", "Bb", "C", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "1/3",
            expected: {value: ["33.3333"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expected: {value: ["33.3333", "66.6667"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expected: {value: ["33.3333", "66.6667", "100"]}
        });
        // Third row

        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Lucie",
            expected: {value: ["LC"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Hélène",
            expected: {value: ["LC", "SH"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Tomé",
            expected: {value: ["LC", "SH", "ST"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Pierre",
            expected: {value: ["LC", "SH", "ST", "PM"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Vincent",
            expected: {value: ["LC", "SH", "ST", "PM", "VC"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "La",
            selectedText: "Lab",
            expected: {value: ["Ab"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Sib",
            expected: {value: ["Ab", "Bb"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do#",
            expected: {value: ["Ab", "Bb", "Cd"]}
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol",
            expected: {value: ["Ab", "Bb", "Cd", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "0",
            expected: {value: ["0"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expected: {value: ["0", "66.6667"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expected: {value: ["0", "66.6667", "100"]}
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
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumcountry', label: "Danemark", expected: {value: "DK"}});

        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumtext', label: "Bleu", expected: {value: "blue"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "70 %", expected: {value: "70"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Critique", expected: {value: "C"}});

        // Multiple
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumscountry',
            label: "Chili",
            expected: {value: ["AL", "BE", "CL", "NC"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumscountry',
            label: "Cuba",
            expected: {value: ["AL", "BE", "CL", "CU", "NC"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "30 %",
            expected: {value: ["70"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumsnumber',
            label: "100 %",
            expected: {value: ["70", "100"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumstext',
            label: "Rouge",
            expected: {value: ["red", "yellow", "green", "navyblue"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__enumstext',
            label: "Jaune",
            expected: {value: ["red", "green", "navyblue"]}
        });

        util.saveScreenshot("enumVerticalDirectTab").then(itDone);
    });

    it("setVerticalEnumServerTab", function setVerticalEnumServerTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_srv'});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvcountry', label: "Arménie", expected: {value: "AM"}});

        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvtext', label: "Rouge", expected: {value: "red"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvnumber', label: "2/3", expected: {value: "66.6667"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvbool', label: "Normal", expected: {value: "N"}});

        // Multiple
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvscountry', label: "Chili",
            expected: {value: ["AL", "BE", "CL", "NC"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvscountry', label: "Cuba",
            expected: {value: ["AL", "BE", "CL", "CU", "NC"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber', label: "30 %",
            expected: {value: ["70"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvsnumber', label: "70 %",
            expected: {value: []}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Rouge",
            expected: {value: ["red", "yellow", "green", "navyblue"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Bleu",
            expected: {value: ["red", "yellow", "green", "blue", "navyblue"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_enum__srvstext', label: "Jaune",
            expected: {value: ["red", "green", "blue", "navyblue"]}
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
            expected: {value: "AL"}
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumtext_array',
            label: "Si",
            expected: {value: "Bb"}
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expected: {value: "66.6667"}
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expected: {value: "D"}
        });

        // Second row
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expected: {value: "S"}
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumcountry_array',
            label: "Suisse",
            expected: {value: "CH"}
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumtext_array',
            label: "Si",
            expected: {value: "Bb"}
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3",
            expected: {value: "66.6667"}
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux",
            expected: {value: "D"}
        });

        // Third row
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumcountry_array',
            label: "Bulgarie",
            expected: {value: "BG"}
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "Sol",
            label: "Sol",
            expected: {value: "G"}
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "3/3",
            expected: {value: "100"}
        });

        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger",
            expected: {value: "S"}
        });

        // ------------------------
        // Second array

        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Bahr",
            expected: {value: ["AG", "BE", "BH"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Belgique",
            expected: {value: ["AG", "BH"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "La",
            expected: {value: ["G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Sib",
            expected: {value: ["Bb", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expected: {value: ["66.6667", "100"]}
        });

        // Second row
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Aruba",
            expected: {value: ["AW", "YE", "ZM"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Australie",
            expected: {value: ["AU", "AW", "YE", "ZM"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mi",
            expected: {value: ["A", "Bb", "C", "Eb", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mib",
            expected: {value: ["A", "Bb", "C", "G"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3",
            expected: {value: ["33.3333", "66.6667"]}
        });
        // Third row
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Argentine",
            expected: {value: ["AR", "LC", "PM", "SH", "ST", "VC"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            label: "Australie",
            expected: {value: ["AR", "AU", "LC", "PM", "SH", "ST", "VC"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Do",
            expected: {value: ["Bb", "C", "Cd", "G", "Ab"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            label: "Mib",
            expected: {value: ["Bb", "C", "Cd", "Eb", "G", "Ab"]}
        });
        docForm.selectEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3",
            expected: {value: ["0", "100"]}
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