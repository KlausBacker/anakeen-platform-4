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

    var setFirstTab = function setFirstTab()
    {
        var now=new Date();
        now.setTime(now.getTime()+(now.getHours() - now.getUTCHours())*3600000);
        //------------------------------------------
        // Text : test_ddui_all__title
        docForm.setTextValue({attrid: 'test_ddui_all__title', rawValue: now.toISOString().substr(0,19)+" "+driver.browser});

        //------------------------------------------
        // First account : test_ddui_all__account
        docForm.setDocidValue({attrid: 'test_ddui_all__account', filterText: "jean", selectedText: "Jean"});
        //------------------------------------------
        // Second account : test_ddui_all__account_multiple
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple',
            filterText: "Isabell",
            selectedText: "di@example.net"
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple',
            filterText: "Cathe",
            selectedText: "gc@example.net"
        });

        //------------------------------------------
        // Date : test_ddui_all__date

        docForm.setDateValue({
            attrid: 'test_ddui_all__date',
            today: true
        });

        //------------------------------------------
        // Time : test_ddui_all__time

        docForm.setTimeValue({
            attrid: 'test_ddui_all__time',
            selectedIndex: 6
        });

        //------------------------------------------
        // Timestamp  :test_ddui_all__timestamp
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp',
            date: "22/10/2000"
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp',
            selectedIndex: 5
        });

        //------------------------------------------
        // Integer : test_ddui_all__integer

        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer',
            number: "12345"
        });

        //------------------------------------------
        // Double : test_ddui_all__double
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double',
            number: "3,1415926535"
        });

        //------------------------------------------
        // Money : test_ddui_all__money

        docForm.setNumericValue({
            attrid: 'test_ddui_all__money',
            number: "678,7"
        });

        //------------------------------------------
        // Password : test_ddui_all__password

        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password',
            rawValue: "Secret"
        });

        //------------------------------------------
        // Color : test_ddui_all__color set to #e4e6b8
        docForm.setColorValue({
            attrid: 'test_ddui_all__color',
            hue: 64,
            saturation: 20,
            value: 90
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
            textValue: "Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.\n Il n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie"
        });

        //------------------------------------------
        // Longtext : test_ddui_all__longtext

        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext',
            rawValue: "5 continents : \nEurope,\n Amérique,\nAsie,\nOcéanie\nAfrique"
        });

        docForm.setTextValue({attrid: 'test_ddui_all__text', rawValue: "Texte de fin"});

    };

    var setEnumTab = function setEnumTab()
    {

        docForm.selectTab({attrid: 'test_ddui_all__t_tab_enums'});
        docForm.setEnumListValue({attrid: 'test_ddui_all__enumlist', selectedText: "Albanie"});
        docForm.setEnumAutoValue({attrid: 'test_ddui_all__enumauto', selectedText: "Zambie", filterText: "zamb"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumvertical', label: "30 %"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumhorizontal', label: "Vert"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumbool', label: "Normal"});

        // Single Server
        docForm.setEnumListValue({attrid: 'test_ddui_all__enumserverlist', selectedText: "Albanie"});
        docForm.setEnumAutoValue({attrid: 'test_ddui_all__enumserverauto', selectedText: "Cocos", filterText: "iles"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumserververtical', label: "Bleu"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumserverhorizontal', label: "70 %"});
        docForm.setEnumRadioValue({attrid: 'test_ddui_all__enumserverbool', label: "Normal"});

        // Multiple
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumslist', filterText: "A", selectedText: "Albanie"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumslist', filterText: "bel", selectedText: "Belgique"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumslist',
            filterText: "nouvelle",
            selectedText: "Calédonie"
        });

        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsauto', filterText: "Bar", selectedText: "Barbade"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsauto', filterText: "mo", selectedText: "Monaco"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsauto', filterText: "nouvelle", selectedText: "Zélande"});

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsvertical', label: "30 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsvertical', label: "70 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumshorizontal', label: "Vert"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumshorizontal', label: "Bleu"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumshorizontal', label: "Bleu/Bleu marine"});

        // Multiple Server
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsserverlist', filterText: "A", selectedText: "Arménie"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsserverlist', filterText: "cô", selectedText: "Ivoire"});
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverlist',
            filterText: "nouvelle",
            selectedText: "Calédonie"
        });

        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "au",
            selectedText: "Autriche"
        });
        docForm.addEnumAutoValue({
            attrid: 'test_ddui_all__enumsserverauto',
            filterText: "au",
            selectedText: "Australie"
        });
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsserverauto', filterText: "y", selectedText: "Yemen"});
        docForm.addEnumAutoValue({attrid: 'test_ddui_all__enumsserverauto', filterText: "z", selectedText: "Zaïre"});

        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsserververtical', label: "0 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsserververtical', label: "100 %"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsserverhorizontal', label: "Vert"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsserverhorizontal', label: "Jaune"});
        docForm.addEnumCheckboxValue({attrid: 'test_ddui_all__enumsserverhorizontal', label: "Bleu/Bleu ciel"});
    };

    var setDateTab = function setDateTab()
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_date'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});
        docForm.addRow({attrid: 'test_ddui_all__array_dates'});

        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            today: true,
            index: 1
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            date: "22/10/1980",
            index: 2
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            today: true,
            index: 0
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__date_array',
            date: "25/05/1977",
            index: 3
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            selectedIndex: 4,
            index: 0
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            selectedIndex: 2,
            index: 1
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            time: "2:15",
            index: 2
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__time_array',
            time: "23:00",
            index: 3
        });

        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp_array',
            selectedIndex: 4,
            index: 0
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            date: "17/05/1980 12:00",
            index: 1
        });
        docForm.setTimeValue({
            attrid: 'test_ddui_all__timestamp_array',
            selectedIndex: 23,
            index: 1
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            today: true,
            index: 2
        });
        docForm.setDateValue({
            attrid: 'test_ddui_all__timestamp_array',
            date: "25/05/1977 14:10",
            index: 3
        });
    };

    var setRelationTab = function setRelationTab()
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
            index: 1
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 0
        });
        docForm.setDocidValue({
            attrid: 'test_ddui_all__account_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 2
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 0
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 0
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 0
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "tordi",
            selectedText: "Albert",
            index: 0
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 1
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Cather",
            index: 1
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 1
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "isa",
            selectedText: "Isabelle",
            index: 2
        });
        docForm.addAccountMultipleValue({
            attrid: 'test_ddui_all__account_multiple_array',
            filterText: "gran",
            selectedText: "Jean",
            index: 2
        });
    };

    var setNumericTab = function setNumericTab()
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.addRow({attrid: 'test_ddui_all__array_numbers'});
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "12345",
            index: 0
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "12345",
            index: 1
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "12345",
            index: 2
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "0987678578,78567",
            index: 2
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "-9876",
            index: 0
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "19076,4",
            index: 0
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__double_array',
            number: "2,718281828",
            index: 1
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__integer_array',
            number: "34568",
            index: 2
        });
        docForm.setNumericValue({
            attrid: 'test_ddui_all__money_array',
            number: "0,65",
            index: 1
        });

    };

    var setMiscTab = function setMiscTab()
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
            index: 0
        });
        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password_array',
            rawValue: "Ne jamais dévoiler",
            index: 2
        });
        docForm.setPasswordValue({
            attrid: 'test_ddui_all__password_array',
            rawValue: "Invisible",
            index: 1
        });

        docForm.addRow({attrid: 'test_ddui_all__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_all__array_singleenum'});
        docForm.addRow({attrid: 'test_ddui_all__array_singleenum'});

        docForm.setEnumListValue({
            index: 0,
            attrid: 'test_ddui_all__enumlist_array',
            selectedText: "Andorre"
        });
        docForm.setEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_all__enumauto_array',
            selectedText: "Zambie", filterText: "zamb"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_all__enumvertical_array',
            label: "Vert"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_all__enumhorizontal_array',
            label: "La"
        });
        docForm.setEnumRadioValue({
            index: 0,
            attrid: 'test_ddui_all__enumbool_array',
            label: "Sans danger"
        });

        docForm.setEnumListValue({
            index: 1,
            attrid: 'test_ddui_all__enumlist_array',
            selectedText: "Arabe"
        });
        docForm.setEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumauto_array',
            selectedText: "Yemen", filterText: "ye"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_all__enumvertical_array',
            label: "Bleu"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_all__enumhorizontal_array',
            label: "Si"
        });
        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_all__enumbool_array',
            label: "Sans danger"
        });

        docForm.setEnumListValue({
            index: 2,
            attrid: 'test_ddui_all__enumlist_array',
            selectedText: "Antigua"
        });
        docForm.setEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_all__enumauto_array',
            selectedText: "Moldavie", filterText: "mo"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_all__enumvertical_array',
            label: "Jaune"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_all__enumhorizontal_array',
            label: "Do"
        });
        docForm.setEnumRadioValue({
            index: 2,
            attrid: 'test_ddui_all__enumbool_array',
            label: "Sans danger"
        });

        docForm.setEnumRadioValue({
            index: 1,
            attrid: 'test_ddui_all__enumbool_array',
            label: "Dangereux"
        });

        docForm.addRow({attrid: 'test_ddui_all__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_all__array_multipleenum'});
        docForm.addRow({attrid: 'test_ddui_all__array_multipleenum'});
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_all__enumslist_array',
            selectedText: "Antigua"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_all__enumslist_array',
            filterText: "bel",
            selectedText: "Belgique"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_all__enumslist_array',
            filterText: "fra",
            selectedText: "France"
        });
        docForm.addEnumAutoValue({
            index: 0,
            attrid: 'test_ddui_all__enumsauto_array',
            selectedText: "Moldavie",
            filterText: "mo"
        });
        docForm.addEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Rouge"
        });
        docForm.addEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Jaune"
        });
        docForm.addEnumCheckboxValue({
            index: 0,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Fa"
        });

        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumslist_array',
            selectedText: "Antigua"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumslist_array',
            filterText: "arg",
            selectedText: "Argentine"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumslist_array',
            filterText: "can",
            selectedText: "Canada"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumsauto_array',
            selectedText: "Moldavie",
            filterText: "mo"
        });
        docForm.addEnumAutoValue({
            index: 1,
            attrid: 'test_ddui_all__enumsauto_array',
            selectedText: "Bermude",
            filterText: "ber"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Jaune"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Vert"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Mi"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Fa"
        });
        docForm.addEnumCheckboxValue({
            index: 1,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Ré"
        });

        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_all__enumslist_array',
            selectedText: "Antigua"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_all__enumsauto_array',
            selectedText: "Monaco", filterText: "mo"
        });
        docForm.addEnumAutoValue({
            index: 2,
            attrid: 'test_ddui_all__enumsauto_array',
            selectedText: "Mongolie", filterText: "mo"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Bleu"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Jaune"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_all__enumsvertical_array',
            label: "Vert"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Fa"
        });
        docForm.addEnumCheckboxValue({
            index: 2,
            attrid: 'test_ddui_all__enumshorizontal_array',
            label: "Ré"
        });
    };

    var setFileTab = function setMiscTab()
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});
        docForm.addRow({attrid: 'test_ddui_all__array_files'});

        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index:0,
            filePath: driver.data.files[0]
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index:0,
            filePath: driver.data.images[0]
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index:1,
            filePath: driver.data.files[1]
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index:1,
            filePath: driver.data.images[1]
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__file_array',
            index:2,
            filePath: driver.data.files[0]
        });
        docForm.setFileValue({
            attrid: 'test_ddui_all__image_array',
            index:2,
            filePath: driver.data.images[2]
        });
    };

    var setTextTab = function setTextTab()
    {
        docForm.selectTab({attrid: 'test_ddui_all__t_tab_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});
        docForm.addRow({attrid: 'test_ddui_all__array_texts'});

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index:0,
            rawValue: "Le cheval"});
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index:0,
            rawValue: "Le cheval (Equus ferus caballus ou Equus caballus) est un grand mammifère herbivore et ongulé à sabot unique, \nappartenant aux espèces de la famille des Équidés (Equidae)."});

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index:1,
            rawValue: "Les Tortues (Testudines), ou Chéloniens"});
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index:1,
            rawValue: "Les Tortues (Testudines), ou Chéloniens, forment un ordre de reptiles dont la caractéristique est d'avoir une carapace.\nIl existe une grande variété d'espèces possédant des caractéristiques diverses, mais toutes se distinguent des autres reptiles par cette carapace qui est constituée d'un plastron au niveau du ventre et d'une dossière sur le dessus, reliés par deux ponts sur les côtés du corps.\nOn les sépare traditionnellement en trois groupes : les tortues terrestres, les tortues aquatiques, ou tortues dulçaquicoles, et les tortues marines."});

        docForm.setTextValue({
            attrid: 'test_ddui_all__text_array',
            index:2,
            rawValue: "L'Aigle"});
        docForm.setLongTextValue({
            attrid: 'test_ddui_all__longtext_array',
            index:2,
            rawValue: "L'Aigle est communément le nom vernaculaire donné à certains grands rapaces planeurs diurnes.\n C'est aussi le nom que la nomenclature aviaire en langue française donne plus précisément à 38 espèces d'oiseaux qui constituent 12 genres. Tous les aigles appartiennent à la famille des Accipitridés.\n Certaines espèces sont actuellement disparues."});

        docForm.addRow({attrid: 'test_ddui_all__array_html'});
        docForm.addRow({attrid: 'test_ddui_all__array_html'});
        docForm.setHtmlTextValue({
            attrid: "test_ddui_all__htmltext_array",
            index:0,
            textValue: "Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands mammifères plantigrades appartenant à la famille des ursidés.\nIl n'existe que huit espèces d'ours vivants, mais ils sont largement répandus dans une grande variété d'habitats, dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'Europe, d'Amérique du Nord, d'Amérique du Sud, et en Asie"
        });
        docForm.setHtmlTextValue({
            attrid: "test_ddui_all__htmltext_array",
            index:1,
            textValue: "Hérisson est un nom vernaculaire qui désigne en français divers petits mammifères insectivores disposant de poils agglomérés, durs, hérissés et piquants.\nCe nom dérive du latin ericius. Les espèces les plus connues des francophones sont le Hérisson commun (Erinaceus europaeus) et le Hérisson oriental (Erinaceus concolor) mais il existe d'autres « hérissons » sur divers continents, y compris en Asie un genre apparenté mais dont les représentants sont dépourvus de piquants : les gymnures.\nCes espèces sont parfois très éloignées sur l'arbre phylogénique, mais se ressemblent par convergence évolutive1.\nPlusieurs espèces comme le Hérisson de Madagascar ou « tangue » sont encore consommées dans l'océan Indien, y compris à la Réunion, d'autres sont au contraire protégées."
        });
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
                    return currentDriver.isElementPresent(webdriver.By.css("#disconnect"));
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

    afterEach(function afterFormAllEdit(afterDone)
    {
        console.log("Exiting... in 10s");
        webdriver.promise.controlFlow().removeListener(handleException);
        currentDriver.sleep(10000); // Wait to see result
        currentDriver.quit().then(afterDone);

    });

    it('setAllInputs', function testSetFirstTab(localDone)
    {
        setFirstTab();
        util.saveScreenshot("commonTab");

        setEnumTab();
        util.saveScreenshot("enumTab");

        setDateTab();
        util.saveScreenshot("dateTab");

        setRelationTab();
        util.saveScreenshot("relationTab");

        setNumericTab();
        util.saveScreenshot("numericTab");

        setMiscTab();
        util.saveScreenshot("miscTab");

        setFileTab();
        util.saveScreenshot("fileTab");

        setTextTab();
        util.saveScreenshot("textTab");

        docForm.createAndClose();
        currentDriver.sleep(10).then (localDone);

    });
});