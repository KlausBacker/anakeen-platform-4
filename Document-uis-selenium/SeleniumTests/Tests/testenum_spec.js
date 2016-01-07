var webdriver = require('selenium-webdriver'),
    driver = require("./initDriver.js"),
    util = require("./libTesting.js"),
    docForm = require("./libDocForm.js");

describe('Dynacase Enum test', function formAllEdit()
{
    'use strict';
    var currentDriver, handleException = function handleException(e)
    {

        jasmine.getEnv().defaultTimeoutInterval = 50; //
        console.error('Unhandled error: ', e);
        expect(false).toBe(null);
    };

    var setMainInputs = function setMainInputs()
    {
        var now = new Date();
        now.setTime(now.getTime() + (now.getHours() - now.getUTCHours()) * 3600000);
        //------------------------------------------
        // Text : test_ddui_enum__title
        docForm.setTextValue({
            attrid: 'test_ddui_enum__titleref',
            rawValue: now.toISOString().substr(0, 19) + " " + driver.browser
        });

        docForm.setEnumListValue({attrid: 'test_ddui_enum__title', selectedText: "Jaune"});
    };

    var setEnumDirectTab = function setEnumDirectTab()
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_enums'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__enumcountry', selectedText: "Albanie"});

        docForm.setEnumAutoValue({attrid: 'test_ddui_enum__enumtext', selectedText: "Vert", filterText: "V"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "30 %"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Normal"});

        // Multiple
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__enumscountry', filterText: "A", selectedText: "Albanie"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__enumscountry', filterText: "bel", selectedText: "Belgique"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie"
        });

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "30 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "70 %"});

        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__enumstext', filterText: "V", selectedText: "Vert"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__enumstext', filterText: "J", selectedText: "Jaune"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__enumstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine"
        });
    };

    var setEnumSrvTab = function setEnumSrvTab()
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_srv'});
        docForm.setEnumListValue({attrid: 'test_ddui_enum__srvcountry', selectedText: "Albanie"});

        docForm.setEnumAutoValue({attrid: 'test_ddui_enum__srvtext', selectedText: "Vert", filterText: "V"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvnumber', label: "1/3"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__srvbool', label: "Normal"});

        // Multiple
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__srvscountry', filterText: "A", selectedText: "Albanie"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__srvscountry', filterText: "bel", selectedText: "Belgique"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvscountry',
            filterText: "nouvelle",
            selectedText: "Calédonie"
        });

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__srvsnumber', label: "30 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__srvsnumber', label: "70 %"});

        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__srvstext', filterText: "V", selectedText: "Vert"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_enum__srvstext', filterText: "J", selectedText: "Jaune"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_enum__srvstext',
            filterText: "B",
            selectedText: "Bleu/Bleu marine"
        });

    };

    var setArrayEnumTab = function setArrayEnumTab()
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_arrays'});

        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_singleenum'});

        // First row
        docForm.setEnumListValue({
            index: 0,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Andorre"
        });
        docForm.setEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumtext_array',
            selectedText: "La"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "1/3"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger"
        });

        // Second row
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger"
        });
        docForm.setEnumListValue({
            index: 1,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Emirat"
        });
        docForm.setEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "S",
            selectedText: "Si"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "2/3"
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Dangereux"
        });

        // Third row
        docForm.setEnumListValue({
            index: 2,
            attrid: 'test_ddui_enum__enumcountry_array',
            selectedText: "Barbade"
        });
        docForm.setEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumtext_array',
            filterText: "Sol",
            selectedText: "Sol"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumnumber_array',
            label: "3/3"
        });

        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_enum__enumbool_array',
            label: "Sans danger"
        });

        // ------------------------
        // Second array
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_enum__array_multipleenum'});

        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            selectedText: "Barbade"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Bel",
            selectedText: "Belgique"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol"
        });
        docForm.addEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3"
        });

        // Second row

        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Z",
            selectedText: "Zambie"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Y",
            selectedText: "Yemen"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "La"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Si"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "1/3"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3"
        });
        // Third row

        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Lucie"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Hélène"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Tomé"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Pierre"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumscountry_array',
            filterText: "Saint",
            selectedText: "Vincent"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "La",
            selectedText: "Lab"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Sib"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            selectedText: "Do#"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_enum__enumstext_array',
            filterText: "Sol",
            selectedText: "Sol"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "0"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "2/3"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_enum__enumsnumber_array',
            label: "3/3"
        });
    };

    var setVerticalEnumDirectTab = function setVerticalEnumDirectTab()
    {
        docForm.selectTab({attrid: 'test_ddui_enum__t_tab_enums'});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumcountry', label: "Danemark"});

        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumtext', label: "Bleu"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumnumber', label: "70 %"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_enum__enumbool', label: "Critique"});

        // Multiple
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumscountry', label: "Chili"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumscountry', label: "Cuba"});

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "30 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumsnumber', label: "100 %"});

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumstext', label: "Rouge"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_enum__enumstext', label: "Jaune"});

    };

    jasmine.getEnv().defaultTimeoutInterval = 500000; // en ms : 3min
    beforeEach(function beforeFormAllEdit(beforeDone)
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

    afterEach(function afterFormAllEdit(afterDone)
    {
        console.log("Exiting... in 10s");
        webdriver.promise.controlFlow().removeListener(handleException);
        currentDriver.sleep(10000); // Wait to see result
        currentDriver.quit().then(afterDone);

    });

    it('setEnumInputs', function testsetMainInputs(localDone)
    {
        console.log("begin setEnumInputs");
        setMainInputs();
        util.saveScreenshot("enumRef");

        setEnumDirectTab();
        util.saveScreenshot("enumDirectTab");
        setEnumSrvTab();
        util.saveScreenshot("enumServerTab");
        setArrayEnumTab();
        util.saveScreenshot("enumArrayTab");

        docForm.createAndClose();
        util.saveScreenshot("enumView");

        docForm.openMenu({listMenu: "Tests", "itemMenu": "Vertical"});
         currentDriver.wait(function waitLoadingDone()
                {
                    return webdriver.until.elementIsVisible(webdriver.By.css(".dcpDocument--edit"));
                }, 5000);
         currentDriver.sleep(1000); // Wait to see result

        setVerticalEnumDirectTab();
        util.saveScreenshot("enumVerticalEdit");
        console.log("end setEnumInputs");

        currentDriver.sleep(10).then(localDone);

    });
});