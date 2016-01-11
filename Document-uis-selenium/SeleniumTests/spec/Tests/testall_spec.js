var webdriver = require('selenium-webdriver'),
    driver = require("../lib/initDriver.js"),
    util = require("../lib/libTesting.js"),
    docForm = require("../lib/libDocForm.js");

describe('Dynacase basic test', function formAllEdit()
{
    'use strict';
    var currentDriver, handleException = function handleException(e)
    {

        jasmine.getEnv().defaultTimeoutInterval = 50; //
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

                currentDriver.get(driver.rootUrl + "?app=TEST_DOCUMENT_SELENIUM");
                var btnCreate = webdriver.By.css('.btn[data-familyname="TST_DDUI_ALLTYPE"]');

                currentDriver.wait(function waitCreateButton()
                {
                    return currentDriver.isElementPresent(btnCreate);
                }, 6000);
                currentDriver.findElement(btnCreate).click();

                docForm.setDocWindow(webdriver.By.css("iframe.dcpDocumentWrapper"));
                currentDriver.wait(function waitDocumentIsDisplayed()
                {
                    return currentDriver.isElementPresent(webdriver.By.css(".dcpDocument__frames"));
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
        console.log("Exiting... in 5s");
        webdriver.promise.controlFlow().removeListener(handleException);
        currentDriver.sleep(5000); // Wait to see result
        driver.quit().then(afterDone);
    });

    it("setFirstTab", function setFirstTab(itDone)
    {
        var now = new Date();
        now.setTime(now.getTime() + (now.getHours() - now.getUTCHours()) * 3600000);
        //------------------------------------------
        // Text : test_ddui_all__title
        docForm.setTextValue({
            attrid: 'test_ddui_all__title',
            rawValue: now.toISOString().substr(0, 19) + " " + driver.browser,
            expected: {value: "{{value}}"}
        });

        //------------------------------------------
        // First account : test_ddui_all__account
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account',
            filterText: "jean",
            selectedText: "Jean",
            expected: {displayValue: "Grand Jean"}
        });
        //------------------------------------------
        // Second account : test_ddui_all__account_multiple
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple',
            filterText: "Isabell",
            selectedText: "di@example.net",
            expected: {displayValue: ["Dujardin Isabelle"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple',
            filterText: "Cathe",
            selectedText: "gc@example.net",
            expected: {displayValue: ["Dujardin Isabelle", "Granget Catherine"]}
        });

        //------------------------------------------
        // Date : test_ddui_all__date

        docForm.setDateValue({
            attrid: 'test_ddui_all__date',
            today: true,
            expected: {value: (new Date()).toISOString().substr(0, 10)}
        });

        //------------------------------------------
        // Time : test_ddui_all__time

        docForm.setTimeValue({
            attrid: 'test_ddui_all__time',
            selectedIndex: 6,
            expected: {value: "02:30"}
        });

        //------------------------------------------
        // Timestamp  :test_ddui_all__timestamp
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp',
            date: "22/10/2000 00:00",
            expected: {value: "2000-10-22T00:00:00"}
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp',
            selectedIndex: 5,
            expected: {value: "2000-10-22T02:00:00"}
        });

        //------------------------------------------
        // Integer : test_ddui_all__integer

        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer',
            number: "12345",
            expected: {value: 12345}
        });

        //------------------------------------------
        // Double : test_ddui_all__double
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double',
            number: "3,1415926535",
            expected: {value: 3.1415926535}
        });

        //------------------------------------------
        // Money : test_ddui_all__money

        docForm.setNumericValue({
            attrid: 'test_ddui_all__money',
            number: "678,70",
            expected: {value: 678.7}
        });

        //------------------------------------------
        // Password : test_ddui_all__password

        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password',
            rawValue: "Secret",
            expected: {value: "{{value}}"}
        });

        //------------------------------------------
        // Color : test_ddui_all__color set to #e4e6b8
        docForm.setColorValue({
            attrid: 'test_ddui_all__color',
            hue: 64,
            saturation: 20,
            value: 90,
            expected: {value: undefined}// precision "#e2e6b7" or #e4e6b9
        });

        //------------------------------------------
        // File : test_ddui_all__file

        docForm.setFileValue({
            attrid: 'test_ddui_all__file',
            filePath: driver.data.filePath
        });

        //------------------------------------------
        // Image : test_ddui_all__image

        docForm.setFileValue({
            attrid: 'test_ddui_all__image',
            filePath: driver.data.imagePath
        });

        //------------------------------------------
        // Htmltext :test_ddui_all__htmltext
        docForm.setHtmlTextValue({
            attrid: "test_ddui_all__htmltext",
            textValue: "Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.\n Il n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie",
            expected: {
                value: "<p>Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.</p>" + "\n\n" +
                "<p>&nbsp;Il n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie</p>" + "\n"
            }
        });

        //------------------------------------------
        // Longtext : test_ddui_all__longtext

        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext',
            rawValue: "5 continents : \nEurope,\n Amérique,\nAsie,\nOcéanie\nAfrique",
            expected: {value: "{{value}}"}
        });

        docForm.setTextValue({
            attrid: 'test_ddui_all__text',
            rawValue: "Texte de fin",
            expected: {value: "{{value}}"}
        });

        util.saveScreenshot("commonTab").then(itDone);
    });

    it("setEnumTab", function setEnumTab(itDone)
    {

        docForm.selectTab({attrid: 'test_ddui_all__t_tab_enums'});
        docForm.setEnumListValue({attrid: 'test_ddui_all__enumlist', selectedText: "Albanie", expected: {value: "AL"}});

        docForm.setEnumAutoValue({
            attrid: 'test_ddui_all__enumauto',
            selectedText: "Zambie",
            filterText: "zamb",
            expected: {value: "ZM"}
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumvertical', label: "30 %", expected: {value: "30"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumhorizontal', label: "Vert", expected: {value: "green"}});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumbool', label: "Normal", expected: {value: "C"}});

        // Single Server
        docForm.setEnumListValue({
            attrid: 'test_ddui_all__enumserverlist',
            selectedText: "Albanie",
            expected: {value: "AL"}
        });
        docForm.setEnumAutoValue({
            attrid: 'test_ddui_all__enumserverauto',
            selectedText: "Cocos",
            filterText: "iles",
            expected: {value: "CC"}
        });
        docForm.setEnumRadioValue({
            attrid: 'test_ddui_all__enumserververtical',
            label: "Bleu",
            expected: {value: "blue"}
        });
        docForm.setEnumRadioValue({
            attrid: 'test_ddui_all__enumserverhorizontal',
            label: "30",
            expected: {value: "30"}
        });
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumserverbool', label: "Normal", expected: {value: "C"}});

        // Multiple
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumslist',
            filterText: "A",
            selectedText: "Albanie",
            expected: {value: ["AL"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumslist',
            filterText: "bel",
            selectedText: "Belgique",
            expected: {value: ["AL", "BE"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumslist',
            filterText: "nouvelle",
            selectedText: "Calédonie", expected: {value: ["AL", "BE", "NC"]}
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsauto',
            filterText: "Bar",
            selectedText: "Barbade",
            expected: {value: ["BB"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsauto',
            filterText: "mo",
            selectedText: "Monaco",
            expected: {value: ["BB", "MC"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsauto',
            filterText: "nouvelle",
            selectedText: "Zélande",
            expected: {value: ["BB", "MC", "NZ"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsvertical',
            label: "30 %",
            expected: {value: ["30"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsvertical',
            label: "70 %",
            expected: {value: ["30", "70"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumshorizontal',
            label: "Vert",
            expected: {value: ["green"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumshorizontal',
            label: "Bleu",
            expected: {value: ["green", "blue"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumshorizontal',
            label: "Bleu/Bleu marine",
            expected: {value: ["green", "blue", "navyblue"]}
        });

        // Multiple Server
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverlist',
            filterText: "A",
            selectedText: "Arménie",
            expected: {value: ["AM"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverlist',
            filterText: "cô",
            selectedText: "Ivoire",
            expected: {value: ["AM", "CI"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverlist',
            filterText: "nouvelle",
            selectedText: "Calédonie", expected: {value: ["AM", "CI", "NC"]}
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "au",
            selectedText: "Autriche", expected: {value: ["AT"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "au",
            selectedText: "Australie", expected: {value: ["AT", "AU"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "y",
            selectedText: "Yemen",
            expected: {value: ["AT", "AU", "YE"]}
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "z",
            selectedText: "Zaïre",
            expected: {value: ["AT", "AU", "YE", "ZR"]}
        });

        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsserververtical',
            label: "0 %",
            expected: {value: ["0"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsserververtical',
            label: "100 %",
            expected: {value: ["0", "100"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsserverhorizontal',
            label: "Vert",
            expected: {value: ["green"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsserverhorizontal',
            label: "Jaune",
            expected: {value: ["yellow", "green"]}
        });
        docForm.selectEnumCheckboxValue({
            attrid: 'test_ddui_all__enumsserverhorizontal',
            label: "Bleu/Bleu ciel",
            expected: {value: ["yellow", "green", "lightblue"]}
        });

        util.saveScreenshot("enumTab").then(itDone);
    });

    it("setDateTab", function setDateTab(itDone)
    {
        var today = (new Date()).toISOString().substr(0, 10);
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_date'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});

        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            today: true,
            index: 1,
            expected: {value: today}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            date: "22/10/1980",
            index: 2,
            expected: {value: "1980-10-22", displayValue: "22/10/1980"}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            today: true,
            index: 0,
            expected: {value: today}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            date: "25/05/1977",
            index: 3,
            expected: {value: "1977-05-25", displayValue: "25/05/1977"}
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            selectedIndex: 4,
            index: 0
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            selectedIndex: 2,
            index: 1,
            expected: {value: "00:30", displayValue: "00:30"}
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            time: "2:15",
            index: 2,
            expected: {value: "02:15", displayValue: "02:15"}
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            time: "23:00",
            index: 3,
            expected: {value: "23:00", displayValue: "23:00"}
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp_array',
            selectedIndex: 4,
            index: 0,
            expected: {value: today + "T01:30:00"}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            date: "17/05/1980 12:00",
            index: 1,
            expected: {value: "1980-05-17T12:00:00", displayValue: "17/05/1980 12:00"}
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp_array',
            selectedIndex: 2,
            index: 1,
            expected: {value: "1980-05-17T00:30:00"}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            today: true,
            index: 2,
            expected: {value: today + "T00:00:00"}
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            date: "25/05/1977 14:10",
            index: 3,
            expected: {value: "1977-05-25T14:10:00", displayValue: "25/05/1977 14:10"}
        });
        util.saveScreenshot("dateTab").then(itDone);
    });

    it("setRelationTab", function setRelationTab(itDone)
    {

        docForm.selectTab({attrid: 'test_ddui_all__t_tab_relations'});

        docForm.addRow({attrid: 'test_ddui_all__array_docid'});

        docForm.addRow({attrid: 'test_ddui_all__array_account'});

        docForm.addRow({attrid: 'test_ddui_all__array_account'});
        docForm.addRow({attrid: 'test_ddui_all__array_account'});
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account_array',
            filterText: "jean",
            selectedText: "Jean",
            index: 1,
            expected: {displayValue: "Grand Jean"}
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 0,
            expected: {displayValue: "Dujardin Isabelle"}
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 2,
            expected: {displayValue: "Granget Catherine"}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 0,
            expected: {displayValue: ["Granget Catherine"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 0,
            expected: {displayValue: ["Granget Catherine", "Grand Jean"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 0,
            expected: {displayValue: ["Granget Catherine", "Grand Jean", "Dujardin Isabelle"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "tordi",
            selectedText: "Albert",
            index: 0,
            expected: {displayValue: ["Granget Catherine", "Grand Jean", "Dujardin Isabelle", "Tordi Albert"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 1,
            expected: {displayValue: ["Dujardin Isabelle"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 1,
            expected: {displayValue: ["Dujardin Isabelle", "Granget Catherine"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 1,
            expected: {displayValue: ["Dujardin Isabelle", "Granget Catherine", "Grand Jean"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 2,
            expected: {displayValue: ["Dujardin Isabelle"]}
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 2,
            expected: {displayValue: ["Dujardin Isabelle", "Grand Jean"]}
        });

        util.saveScreenshot("relationTab").then(itDone);
    });

    it("setNumericTab", function setNumericTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "12345",
            index: 0,
            expected: {value: 12345}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "12345",
            index: 1,
            expected: {value: 12345}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "12345",
            index: 2,
            expected: {value: 12345}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "0987678578,78567",
            index: 2,
            expected: {value: 987678578.78567}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "-9876",
            index: 0,
            expected: {value: -9876}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "19076,4",
            index: 0,
            expected: {value: 19076.4}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "2,718281828",
            index: 1,
            expected: {value: 2.718281828}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "34568",
            index: 2,
            expected: {value: 34568}
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "0,65",
            index: 1,
            expected: {value: 0.65}
        });
        util.saveScreenshot("numericTab").then(itDone);
    });

    it("setMiscTab", function setMiscTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_misc'});
        docForm.addRow({attrid: 'test_ddui_all__array_misc'});
        docForm.addRow({attrid: 'test_ddui_all__array_misc'});
        docForm.addRow({attrid: 'test_ddui_all__array_misc'});

        docForm.setColorValue({
            attrid: 'test_ddui_all__color_array',
            hue: 50,
            saturation: 70,
            value: 90,
            index: 0
        });

        docForm.setColorValue({
            attrid: 'test_ddui_all__color_array',
            hue: 127,
            saturation: 63,
            value: 62,
            index: 1
        });
        docForm.setColorValue({
            attrid: 'test_ddui_all__color_array',
            hue: 237,
            saturation: 24,
            value: 71,
            index: 2
        });

        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password_array',
            rawValue: "Secret",
            index: 0,
            expected: {value: "{{value}}", displayValue: "*****"}
        });
        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password_array',
            rawValue: "Ne jamais dévoiler",
            index: 2,
            expected: {value: "{{value}}", displayValue: "*****"}
        });
        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password_array',
            rawValue: "Invisible",
            index: 1,
            expected: {value: "{{value}}", displayValue: "*****"}
        });

        util.saveScreenshot("miscTab").then(itDone);
    });

    it("setFileTab", function setFileTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});

        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index: 0,
            filePath: driver.data.files[0],
            expected: {displayValue: driver.data.files[0].split(/\/|\\/).reverse()[0]}
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index: 0,
            filePath: driver.data.images[0],
            expected: {displayValue: driver.data.images[0].split(/\/|\\/).reverse()[0]}
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index: 1,
            filePath: driver.data.files[1],
            expected: {displayValue: driver.data.files[1].split(/\/|\\/).reverse()[0]}
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index: 1,
            filePath: driver.data.images[1],
            expected: {displayValue: driver.data.images[1].split(/\/|\\/).reverse()[0]}
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index: 2,
            filePath: driver.data.files[0],
            expected: {displayValue: driver.data.files[0].split(/\/|\\/).reverse()[0]}
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index: 2,
            filePath: driver.data.images[2],
            expected: {displayValue: driver.data.images[2].split(/\/|\\/).reverse()[0]}
        });
        util.saveScreenshot("fileTab").then(itDone);
    });

    it("setTextTab", function setTextTab(itDone)
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index: 0,
            rawValue: "Le cheval",
            expected: {value: "{{value}}"}
        });
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index: 0,
            rawValue: "Le cheval (Equus ferus caballus ou Equus caballus) est un grand mammifère herbivore et ongulé à sabot unique, \nappartenant aux espèces de la famille des Équidés (Equidae).",
            expected: {value: "{{value}}"}
        });

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index: 1,
            rawValue: "Les Tortues (Testudines), ou Chéloniens",
            expected: {value: "{{value}}"}
        });
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index: 1,
            rawValue: "Les Tortues (Testudines), ou Chéloniens, forment un ordre de reptiles dont la caractéristique est d'avoir une carapace.\nIl existe une grande variété d'espèces possédant des caractéristiques diverses, mais toutes se distinguent des autres reptiles par cette carapace qui est constituée d'un plastron au niveau du ventre et d'une dossière sur le dessus, reliés par deux ponts sur les côtés du corps.\nOn les sépare traditionnellement en trois groupes : les tortues terrestres, les tortues aquatiques, ou tortues dulçaquicoles, et les tortues marines.",
            expected: {value: "{{value}}"}
        });

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index: 2,
            rawValue: "L'Aigle",
            expected: {value: "{{value}}"}
        });
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index: 2,
            rawValue: "L'Aigle est communément le nom vernaculaire donné à certains grands rapaces planeurs diurnes.\n C'est aussi le nom que la nomenclature aviaire en langue française donne plus précisément à 38 espèces d'oiseaux qui constituent 12 genres. Tous les aigles appartiennent à la famille des Accipitridés.\n Certaines espèces sont actuellement disparues.",
            expected: {value: "{{value}}"}
        });

        docForm.addRow({attrid: 'test_ddui_all__array_html'});
        docForm.addRow({attrid: 'test_ddui_all__array_html'});
        docForm.setHtmlTextValue({
            attrid: "test_ddui_all__htmltext_array",
            index: 0,
            textValue: "Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.\nIl n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie",
            expected: {
                value: "<p>Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.</p>" +
                "\n\n" +
                "<p>Il n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie</p>" + "\n"
            }
        });
        docForm.setHtmlTextValue({
            attrid: "test_ddui_all__htmltext_array",
            index: 1,
            textValue: "Hérisson est un nom vernaculaire qui désigne en français divers petits mammifères insectivores disposant de poils agglomérés, durs, hérissés et piquants.\nCe nom dérive du latin ericius. Les espèces les plus connues des francophones sont le Hérisson commun (Erinaceus europaeus) et le Hérisson oriental (Erinaceus concolor) mais il existe d'autres « hérissons » sur divers continents, y compris en Asie un genre apparenté mais dont les représentants sont dépourvus de piquants : les gymnures.\nCes espèces sont parfois très éloignées sur l'arbre phylogénique, mais se ressemblent par convergence évolutive1.\nPlusieurs espèces comme le Hérisson de Madagascar ou « tangue » sont encore consommées dans l'océan Indien, y compris à la Réunion, d'autres sont au contraire protégées.",
            expected: {
                value: "<p>Hérisson est un nom vernaculaire qui désigne en français divers petits mammifères insectivores disposant de poils agglomérés, durs, hérissés et piquants.</p>" +
                "\n\n" +
                "<p>Ce nom dérive du latin ericius. Les espèces les plus connues des francophones sont le Hérisson commun (Erinaceus europaeus) et le Hérisson oriental (Erinaceus concolor) mais il existe d'autres « hérissons » sur divers continents, y compris en Asie un genre apparenté mais dont les représentants sont dépourvus de piquants : les gymnures.</p>" +
                "\n\n" +
                "<p>Ces espèces sont parfois très éloignées sur l'arbre phylogénique, mais se ressemblent par convergence évolutive1.</p>" +
                "\n\n" +
                "<p>Plusieurs espèces comme le Hérisson de Madagascar ou « tangue » sont encore consommées dans l'océan Indien, y compris à la Réunion, d'autres sont au contraire protégées.</p>" + "\n"
            }
        });
        util.saveScreenshot("textTab").then(itDone);
    });

    it('createAndClose', function testSetFirstTab(itDone)
    {
        docForm.createAndClose();
        util.saveScreenshot("viewCreate");
        currentDriver.sleep(10).then(itDone);
    });
});