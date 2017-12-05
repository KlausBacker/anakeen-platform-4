/**
 * test setValue method on attributeModel during document consultation
 */

(function (window) {
    'use strict';


    function test() {


        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_0",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__title") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "Touttoutyoutou complet",
                    "displayValue": "Touttoutyoutou complet"
                };

                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_1",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__account") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "familyRelation": "",
                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1072&amp;latest=Y",
                    "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                    "revision": -1,
                    "initid": 1072,
                    "fromid": 128,
                    "value": "1070",
                    "displayValue": "petit Jean"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }

            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_2",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__account_multiple") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "familyRelation": "",
                        "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1074&amp;latest=Y",
                        "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                        "revision": -1,
                        "initid": 1074,
                        "fromid": 128,
                        "value": "1074",
                        "displayValue": "Granget Catherine"
                    },
                    {
                        "familyRelation": "",
                        "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1073&amp;latest=Y",
                        "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                        "revision": -1,
                        "initid": 1075,
                        "fromid": 128,
                        "value": "1078",
                        "displayValue": "Dujardin martinne"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_3",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__docid") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "familyRelation": "TST_DDUI_ALLTYPE",
                    "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1156&amp;latest=Y",
                    "icon": "api/v1/images/assets/sizes/14x14c/testdduiall.png",
                    "revision": -1,
                    "initid": 1156,
                    "fromid": 1051,
                    "value": "1156Bis",
                    "displayValue": "Test tout type sans titre 1156Bis"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_4",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__docid_multiple") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_5",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__date") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "2017-10-05",
                    "displayValue": "05/10/2017"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_6",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__time") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "03:00:00",
                    "displayValue": "03:00:00"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_7",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__timestamp") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "2017-11-06 09:31:00",
                    "displayValue": "06/11/2017 09:31"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_8120",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__integer") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    value: 120,
                    displayValue: "120"
                };

                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_9",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__double") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": 13.35,
                    "displayValue": "13.35"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_10",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__money") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": 1240,
                    "displayValue": "1240,00"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_11",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__password") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "secretaire",
                    "displayValue": "secretaire"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_12",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__color") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "#8FFFCB",
                    "displayValue": "#8FFFCB"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_13",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__file") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": null,
                    "displayValue": null
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_14",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__image") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": null,
                    "displayValue": null
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_15",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__htmltext") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "<p><a href=\"https://en.wikipedia.org/wiki/STEREO?uselang=fr\"><img alt=\"Image illustrative de l'article Soleil\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg/280px-Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg\" style=\"float:right\" /></a><br />\nUne <a href=\"https://fr.wikipedia.org/wiki/%C3%89ruption_solaire\">éruption solaire</a> vue en <a href=\"https://fr.wikipedia.org/wiki/Ultraviolet\">ultraviolet</a> avec de Le <strong>Soleil</strong> est l’<a href=\"https://fr.wikipedia.org/wiki/%C3%89toile\">étoile</a> du <a href=\"https://fr.wikipedia.org/wiki/Syst%C3%A8me_solaire\">Système solaire</a>. Dans la classification <a href=\"https://fr.wikipedia.org/wiki/Astronomie\">astronomique</a>, c’est une étoile de type <a href=\"https://fr.wikipedia.org/wiki/Naine_jaune\">naine jaune</a> d'une masse d'environ 1,9891 × 1030 kg, composée d’<a href=\"https://fr.wikipedia.org/wiki/Hydrog%C3%A8ne\">hydrogène</a>(75 % de la masse ou 92 % du volume) et d’<a href=\"https://fr.wikipedia.org/wiki/H%C3%A9lium\">hélium</a> (25 % de la masse ou 8 % du volume)<a href=\"https://fr.wikipedia.org/wiki/Soleil#cite_note-9\">9</a>. Le Soleil fait partie de la <a href=\"https://fr.wikipedia.org/wiki/Galaxie\">galaxie</a> appelée la <a href=\"https://fr.wikipedia.org/wiki/Voie_lact%C3%A9e\">Voie lactée</a> et se situe à environ 8 <a href=\"https://fr.wikipedia.org/wiki/Kiloparsec\">kpc</a> (∼26 100 <a href=\"https://fr.wikipedia.org/wiki/Ann%C3%A9e-lumi%C3%A8re\">a.l.</a>) du <a href=\"https://fr.wikipedia.org/wiki/Centre_galactique\">centre galactique</a>. Autour de lui gravitent la <a href=\"https://fr.wikipedia.org/wiki/Terre\">Terre</a> (à la vitesse de 30 km/s), sept autres <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te\">planètes</a>, au moins cinq <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te_naine\">planètes naines</a>, de très nombreux <a href=\"https://fr.wikipedia.org/wiki/Ast%C3%A9ro%C3%AFde\">astéroïdes</a> et <a href=\"https://fr.wikipedia.org/wiki/Com%C3%A8te\">comètes</a> et une bande de <a href=\"https://fr.wikipedia.org/wiki/Lumi%C3%A8re_zodiacale\">poussière</a>. Le Soleil représente à lui seul 99,86 % de la masse du Système solaire ainsi constitué, <a href=\"https://fr.wikipedia.org/wiki/Jupiter_(plan%C3%A8te)\">Jupiter</a> représentant plus des deux tiers du reste.</p>",
                    "displayValue": "<p><a href=\"https://en.wikipedia.org/wiki/STEREO?uselang=fr\"><img alt=\"Image illustrative de l'article Soleil\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg/280px-Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg\" style=\"float:right\" /></a><br />\nUne <a href=\"https://fr.wikipedia.org/wiki/%C3%89ruption_solaire\">éruption solaire</a> vue en <a href=\"https://fr.wikipedia.org/wiki/Ultraviolet\">ultraviolet</a> avec de Le <strong>Soleil</strong> est l’<a href=\"https://fr.wikipedia.org/wiki/%C3%89toile\">étoile</a> du <a href=\"https://fr.wikipedia.org/wiki/Syst%C3%A8me_solaire\">Système solaire</a>. Dans la classification <a href=\"https://fr.wikipedia.org/wiki/Astronomie\">astronomique</a>, c’est une étoile de type <a href=\"https://fr.wikipedia.org/wiki/Naine_jaune\">naine jaune</a> d'une masse d'environ 1,9891 × 1030 kg, composée d’<a href=\"https://fr.wikipedia.org/wiki/Hydrog%C3%A8ne\">hydrogène</a>(75 % de la masse ou 92 % du volume) et d’<a href=\"https://fr.wikipedia.org/wiki/H%C3%A9lium\">hélium</a> (25 % de la masse ou 8 % du volume)<a href=\"https://fr.wikipedia.org/wiki/Soleil#cite_note-9\">9</a>. Le Soleil fait partie de la <a href=\"https://fr.wikipedia.org/wiki/Galaxie\">galaxie</a> appelée la <a href=\"https://fr.wikipedia.org/wiki/Voie_lact%C3%A9e\">Voie lactée</a> et se situe à environ 8 <a href=\"https://fr.wikipedia.org/wiki/Kiloparsec\">kpc</a> (∼26 100 <a href=\"https://fr.wikipedia.org/wiki/Ann%C3%A9e-lumi%C3%A8re\">a.l.</a>) du <a href=\"https://fr.wikipedia.org/wiki/Centre_galactique\">centre galactique</a>. Autour de lui gravitent la <a href=\"https://fr.wikipedia.org/wiki/Terre\">Terre</a> (à la vitesse de 30 km/s), sept autres <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te\">planètes</a>, au moins cinq <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te_naine\">planètes naines</a>, de très nombreux <a href=\"https://fr.wikipedia.org/wiki/Ast%C3%A9ro%C3%AFde\">astéroïdes</a> et <a href=\"https://fr.wikipedia.org/wiki/Com%C3%A8te\">comètes</a> et une bande de <a href=\"https://fr.wikipedia.org/wiki/Lumi%C3%A8re_zodiacale\">poussière</a>. Le Soleil représente à lui seul 99,86 % de la masse du Système solaire ainsi constitué, <a href=\"https://fr.wikipedia.org/wiki/Jupiter_(plan%C3%A8te)\">Jupiter</a> représentant plus des deux tiers du reste.</p>"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_16",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__longtext") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "La Lune est lunique satellite naturel de la Terre. Suivant la désignation systématique des satellites, la Lune est appelée Terre I2 ; cependant en pratique cette forme n'est pas utilisée. Elle est le cinquième plus grand satellite du Système solaire, avec un diamètre de 3 474 km. La distance moyenne séparant la Terre de la Lune est de 384 467 km3.\n\nLa Lune est le premier et le seul objet non terrestre visité par l'Homme. Le premier à y avoir marché est l'astronaute américain Neil Armstrong le 21 juillet 1969. Après lui, onze autres hommes ont foulé le sol de la Lune, tous membres du programme Apollo.",
                    "displayValue": "La Lune est lunique satellite naturel de la Terre. Suivant la désignation systématique des satellites, la Lune est appelée Terre I2 ; cependant en pratique cette forme n'est pas utilisée. Elle est le cinquième plus grand satellite du Système solaire, avec un diamètre de 3 474 km. La distance moyenne séparant la Terre de la Lune est de 384 467 km3.\n\nLa Lune est le premier et le seul objet non terrestre visité par l'Homme. Le premier à y avoir marché est l'astronaute américain Neil Armstrong le 21 juillet 1969. Après lui, onze autres hommes ont foulé le sol de la Lune, tous membres du programme Apollo."
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_17",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__text") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "value": "Mars est originellement le nom du dieu de la guerre dans la mythologie romaine, c'est aussi une chocolaterie dans la mythologie Américaine",
                    "displayValue": "Mars est originellement le nom du dieu de la guerre dans la mythologie romaine, c'est aussi une chocolaterie dans la mythologie Américaine"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_18",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumlist") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "BCN",
                    "displayValue": "Barcelone"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_19",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumauto") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "BCN",
                    "displayValue": "Barcelone"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_20",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumvertical") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "10",
                    "displayValue": "10 %"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_21",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumhorizontal") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "blue",
                    "displayValue": "blue"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_22",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumbool") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "M",
                    "displayValue": "Middle"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_23",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumserverlist") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "RU",
                    "displayValue": "Russia"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_24",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumserverauto") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "RU",
                    "displayValue": "Russia"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_25",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumserververtical") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "blue",
                    "displayValue": "Bleu"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_26",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumserverhorizontal") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "10",
                    "displayValue": "10 %"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_27",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumserverbool") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = {
                    "exists": true,
                    "value": "G",
                    "displayValue": "Génial"
                };
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_28",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumslist") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "ES",
                        "displayValue": "Espagne"
                    },
                    {
                        "exists": true,
                        "value": "ES",
                        "displayValue": "Espagne"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_29",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsauto") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "BR",
                        "displayValue": "Bielorussia"
                    },
                    {
                        "exists": true,
                        "value": "GB",
                        "displayValue": "Gabon"
                    },
                    {
                        "exists": true,
                        "value": "GB",
                        "displayValue": "Gabon"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_30",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsvertical") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "33",
                        "displayValue": "33 %"
                    },
                    {
                        "exists": true,
                        "value": "770",
                        "displayValue": "770 %"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_31",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumshorizontal") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "green",
                        "displayValue": "Vert"
                    },
                    {
                        "exists": true,
                        "value": "lightblue",
                        "displayValue": "Bleu/Bleu ciel"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_32",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsserverlist") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "BJ",
                        "displayValue": "Bénin"
                    },
                    {
                        "exists": true,
                        "value": "CV",
                        "displayValue": "Cap Vert"
                    },
                    {
                        "exists": true,
                        "value": "FR",
                        "displayValue": "France"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_33",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsserverauto") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "ZR",
                        "displayValue": "Zaïre (République Démocratique du Congo)"
                    },
                    {
                        "exists": true,
                        "value": "SN",
                        "displayValue": "Senegale"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_34",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsserververtical") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "30",
                        "displayValue": "30 %"
                    },
                    {
                        "exists": true,
                        "value": "110",
                        "displayValue": "110 %"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_35",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__enumsserverhorizontal") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "exists": true,
                        "value": "dark",
                        "displayValue": "Sombre"
                    },
                    {
                        "exists": true,
                        "value": "navyblue",
                        "displayValue": "Bleu/Bleu marine"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_36",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__date_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "2017-13-12",
                        "displayValue": "12/13/2017"
                    },
                    {
                        "value": "2017-11-17",
                        "displayValue": "17/11/2017"
                    },
                    {
                        "value": "2017-07-05",
                        "displayValue": "05/07/2017"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_37",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__time_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "09:00",
                        "displayValue": "09:00"
                    },
                    {
                        "value": "02:20",
                        "displayValue": "02:20"
                    },
                    {
                        "value": "13:30",
                        "displayValue": "13:30"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_38",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__timestamp_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "2017-11-08T17:30:00",
                        "displayValue": "08/11/2017 17:30"
                    },
                    {
                        "value": "2017-06-13T11:00:00",
                        "displayValue": "13/06/2017 11:00"
                    },
                    {
                        "value": "2017-11-29T00:00:00",
                        "displayValue": "29/11/2017 00:00"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_39",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__docid_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_40",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__docid_multiple_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_41",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__account_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "familyRelation": "",
                        "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1074&amp;latest=Y",
                        "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                        "revision": -1,
                        "initid": 1074,
                        "fromid": 128,
                        "value": "1074",
                        "displayValue": "Granget Catherine"
                    },
                    {
                        "familyRelation": "",
                        "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1075&amp;latest=Y",
                        "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                        "revision": -1,
                        "initid": 1075,
                        "fromid": 128,
                        "value": "1070",
                        "displayValue": "Tordi yannick"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_42",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__account_multiple_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    [
                        {
                            "familyRelation": "",
                            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1073&amp;latest=Y",
                            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                            "revision": -1,
                            "initid": 1073,
                            "fromid": 128,
                            "value": "1073",
                            "displayValue": "Dujardin Isabelle"
                        },
                        {
                            "familyRelation": "",
                            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1072&amp;latest=Y",
                            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                            "revision": -1,
                            "initid": 1072,
                            "fromid": 128,
                            "value": "1079",
                            "displayValue": "Petit Jean"
                        }
                    ],
                    [
                        {
                            "familyRelation": "",
                            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1075&amp;latest=Y",
                            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                            "revision": -1,
                            "initid": 1075,
                            "fromid": 128,
                            "value": "1075",
                            "displayValue": "Tordi Albert"
                        },
                        {
                            "familyRelation": "",
                            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1073&amp;latest=Y",
                            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                            "revision": -1,
                            "initid": 1073,
                            "fromid": 128,
                            "value": "1073",
                            "displayValue": "Dujardin Isabelle"
                        },
                        {
                            "familyRelation": "",
                            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1072&amp;latest=Y",
                            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                            "revision": -1,
                            "initid": 1072,
                            "fromid": 128,
                            "value": "1072",
                            "displayValue": "Grand Jean"
                        }
                    ]
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_43",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__double_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": 12.568,
                        "displayValue": "12.567"
                    },
                    {
                        "value": 31346.787632,
                        "displayValue": "31346.787632"
                    },
                    {
                        "value": 535.356756832456789,
                        "displayValue": "535.356756832"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_44",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__integer_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": 244,
                        "displayValue": "244"
                    },
                    {
                        "value": 6873223456789009876,
                        "displayValue": "6873223456789009876"
                    },
                    {
                        "value": -4563,
                        "displayValue": "-4563"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_45",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__money_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": 34.666,
                        "displayValue": "34,666"
                    },
                    {
                        "value": 36287,
                        "displayValue": "36 287,00"
                    },
                    {
                        "value": -43563.5,
                        "displayValue": "-43 563,50"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_46",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__color_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "#FFFFFF",
                        "displayValue": "#FFFFFF"
                    },
                    {
                        "value": "#CCA6FF",
                        "displayValue": "#CCA6FF"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_47",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__password_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "heliopose",
                        "displayValue": "heliopose"
                    },
                    {
                        "value": "vilet",
                        "displayValue": "vilet"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_48",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__file_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_49",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__image_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_50",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__text_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "La tortue",
                        "displayValue": "La tortue"
                    },
                    {
                        "value": "Le lion",
                        "displayValue": "Le lion"
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_51",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__longtext_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "Les tortues sont chassés depuis la préhistoire pour leur viande et leur fourrure. Ils ont joué un rôle de premier plan dans la culture (mythologie, légendes, etc.) et les arts. À l'époque moderne, les populations d'ours sont victimes de pressions (comme celles des éleveurs dans les Pyrénées), de l'empiètement de l'homme sur son habitat naturel, de l'artificialisation et de la fragmentation des forêts, ainsi que du commerce illicite, notamment le marché asiatique de la bile d'ours. L'UICN a classé six espèces d'ours comme vulnérables ou menacées d'extinction. L'ours brun pourrait disparaître dans certains pays européens. Le braconnage et le commerce international des populations les plus en danger sont interdits, mais se pratiquent toujours.",
                        "displayValue": "Les tortues sont chassés depuis la préhistoire pour leur viande et leur fourrure. Ils ont joué un rôle de premier plan dans la culture (mythologie, légendes, etc.) et les arts. À l'époque moderne, les populations d'ours sont victimes de pressions (comme celles des éleveurs dans les Pyrénées), de l'empiètement de l'homme sur son habitat naturel, de l'artificialisation et de la fragmentation des forêts, ainsi que du commerce illicite, notamment le marché asiatique de la bile d'ours. L'UICN a classé six espèces d'ours comme vulnérables ou menacées d'extinction. L'ours brun pourrait disparaître dans certains pays européens. Le braconnage et le commerce international des populations les plus en danger sont interdits, mais se pratiquent toujours."
                    },
                    {
                        "value": "Le lion est le deuxième plus grand félidé, après le tigre, et ainsi le plus grand carnivore d'Afrique. Un mâle mesure de 172 à 250 centimètres de long2 du bout du museau à la base de la queue et possède une queue d’en moyenne 90 centimètres3. Les mâles atteignent une masse comprise entre 145 et 225 kilogrammes à l'âge adulte3. La lionne adulte mesure de 158 à 192 centimètres2 sans la queue et possède une queue mesurant environ 85 cm. Elles pèsent entre 83 et 168 kg3 et ont une corpulence en moyenne 20 à 50 % moins importante que celle d'un mâle4.",
                        "displayValue": "Le lion est le deuxième plus grand félidé, après le tigre, et ainsi le plus grand carnivore d'Afrique. Un mâle mesure de 172 à 250 centimètres de long2 du bout du museau à la base de la queue et possède une queue d’en moyenne 90 centimètres3. Les mâles atteignent une masse comprise entre 145 et 225 kilogrammes à l'âge adulte3. La lionne adulte mesure de 158 à 192 centimètres2 sans la queue et possède une queue mesurant environ 85 cm. Elles pèsent entre 83 et 168 kg3 et ont une corpulence en moyenne 20 à 50 % moins importante que celle d'un mâle4."
                    }
                ];
                attributeObject.setValue(newValue);
                console.log(attributeObject.getValue());
                if (JSON.stringify(attributeObject.getValue("current")) !== JSON.stringify(attributeObject.getValue("previous"))) {
                    $el.css("background-color", "lime");
                } else {
                    $el.css("background-color", "red");
                }
            }
        );
        window.dcp.document.documentController("addEventListener",
            "attributeReady",
            {
                "name": "doubleCheck_52",
                "documentCheck": function (documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE"
                },
                "attributeCheck": function isTitle(attribute) {
                    if (attribute.id === "test_ddui_all__htmltext_array") {
                        return true;
                    }
                }
            },
            function changeDisplayError(event, documentObject, attributeObject, $el) {
                var newValue = [
                    {
                        "value": "<p><a href=\"https://en.wikipedia.org/wiki/NASA_Deep_Space_Network\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Ursus_arctos_Dessin_ours_brun_grand.jpg/290px-Ursus_arctos_Dessin_ours_brun_grand.jpg\" style=\"float:right\" /></a></p><p>Les <strong>ours</strong> forment la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8res\">mammifères</a> des <strong>ursidés</strong> (<strong>Ursidae</strong>), de l'ordre des <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> (<a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a>). Le <a href=\"https://fr.wikipedia.org/wiki/Grand_panda\">Grand panda</a>, dont la classification a longtemps prêté à débat, est aujourd'hui considéré comme un ours <a href=\"https://fr.wikipedia.org/wiki/Herbivore\">herbivore</a> au sein de cette famille<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-1\">1</a>,<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-2\">2</a>. Il n'existe que huit espèces d'ours vivantes réparties dans une grande variété d'<a href=\"https://fr.wikipedia.org/wiki/Habitat_(%C3%A9cologie)\">habitats</a>, à la fois dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'<a href=\"https://fr.wikipedia.org/wiki/Europe\">Europe</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Nord\">Amérique du Nord</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Sud\">Amérique du Sud</a>, et en <a href=\"https://fr.wikipedia.org/wiki/Asie\">Asie</a>.</p><p>Les ours modernes ont comme caractéristiques un corps grand, trapu et massif, un long museau, un pelage dense, des pattes plantigrades à cinq griffes non rétractiles et une queue courte. L'ours blanc est principalement carnassier. Le <a href=\"https://fr.wikipedia.org/wiki/Panda_g%C3%A9ant\">panda géant</a> se nourrit presque exclusivement de <a href=\"https://fr.wikipedia.org/wiki/Bambou\">bambou</a>. Les six autres espèces sont <a href=\"https://fr.wikipedia.org/wiki/Omnivore\">omnivores</a>, leur alimentation variée comprend essentiellement des plantes et des animaux. Sauf en période de <a href=\"https://fr.wikipedia.org/wiki/Reproduction_(biologie)\">reproduction</a> et d'éducation des jeunes, les ours sont solitaires. Généralement <a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a>, ils sont aussi éventuellement actifs la nuit ou au crépuscule, en particulier autour des zones d'habitation humaine. On les dit parfois « <a href=\"https://fr.wikipedia.org/wiki/Nocturne_(comportement_animal)\">nocto</a>-<a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a> ». Aidé par un odorat développé, l'ours peut, malgré sa corpulence, courir rapidement, nager et escalader certaines parois ou des arbres. Cavernicole, il se réfugie volontiers dans des grottes, cavernes et tanières. La plupart des espèces y passent la saison froide à dormir (<a href=\"https://fr.wikipedia.org/wiki/Hivernation\">hivernation</a>).</p><p><a href=\"https://fr.wikipedia.org/wiki/Ours_brun\">Ours brun</a> (<em>Ursus arctos</em>)</p>",
                        "displayValue": "<p><a href=\"https://en.wikipedia.org/wiki/NASA_Deep_Space_Network\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Ursus_arctos_Dessin_ours_brun_grand.jpg/290px-Ursus_arctos_Dessin_ours_brun_grand.jpg\" style=\"float:right\" /></a></p><p>Les <strong>ours</strong> forment la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8res\">mammifères</a> des <strong>ursidés</strong> (<strong>Ursidae</strong>), de l'ordre des <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> (<a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a>). Le <a href=\"https://fr.wikipedia.org/wiki/Grand_panda\">Grand panda</a>, dont la classification a longtemps prêté à débat, est aujourd'hui considéré comme un ours <a href=\"https://fr.wikipedia.org/wiki/Herbivore\">herbivore</a> au sein de cette famille<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-1\">1</a>,<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-2\">2</a>. Il n'existe que huit espèces d'ours vivantes réparties dans une grande variété d'<a href=\"https://fr.wikipedia.org/wiki/Habitat_(%C3%A9cologie)\">habitats</a>, à la fois dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'<a href=\"https://fr.wikipedia.org/wiki/Europe\">Europe</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Nord\">Amérique du Nord</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Sud\">Amérique du Sud</a>, et en <a href=\"https://fr.wikipedia.org/wiki/Asie\">Asie</a>.</p><p>Les ours modernes ont comme caractéristiques un corps grand, trapu et massif, un long museau, un pelage dense, des pattes plantigrades à cinq griffes non rétractiles et une queue courte. L'ours blanc est principalement carnassier. Le <a href=\"https://fr.wikipedia.org/wiki/Panda_g%C3%A9ant\">panda géant</a> se nourrit presque exclusivement de <a href=\"https://fr.wikipedia.org/wiki/Bambou\">bambou</a>. Les six autres espèces sont <a href=\"https://fr.wikipedia.org/wiki/Omnivore\">omnivores</a>, leur alimentation variée comprend essentiellement des plantes et des animaux. Sauf en période de <a href=\"https://fr.wikipedia.org/wiki/Reproduction_(biologie)\">reproduction</a> et d'éducation des jeunes, les ours sont solitaires. Généralement <a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a>, ils sont aussi éventuellement actifs la nuit ou au crépuscule, en particulier autour des zones d'habitation humaine. On les dit parfois « <a href=\"https://fr.wikipedia.org/wiki/Nocturne_(comportement_animal)\">nocto</a>-<a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a> ». Aidé par un odorat développé, l'ours peut, malgré sa corpulence, courir rapidement, nager et escalader certaines parois ou des arbres. Cavernicole, il se réfugie volontiers dans des grottes, cavernes et tanières. La plupart des espèces y passent la saison froide à dormir (<a href=\"https://fr.wikipedia.org/wiki/Hivernation\">hivernation</a>).</p><p><a href=\"https://fr.wikipedia.org/wiki/Ours_brun\">Ours brun</a> (<em>Ursus arctos</em>)</p>"
                    },
                    {
                        "value": "<p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p><p>Le lion mâle ne chasse qu'occasionnellement, il est chargé de combattre les intrusions sur le territoire et les menaces contre la troupe. Le lion <a href=\"https://fr.wikipedia.org/wiki/Rugissement\">rugit</a>. Il n'existe plus à l'état sauvage que 16 500 à 30 000 individus dans la <a href=\"https://fr.wikipedia.org/wiki/Savane\">savane</a> <a href=\"https://fr.wikipedia.org/wiki/Afrique\">africaine</a>, répartis en une dizaine de sous-espèces et environ 300 au <a href=\"https://fr.wikipedia.org/wiki/Parc_national_de_Gir_Forest\">parc national de Gir Forest</a> dans le nord-ouest de l'<a href=\"https://fr.wikipedia.org/wiki/Inde\">Inde</a>. Il est surnommé « le <a href=\"https://fr.wikipedia.org/wiki/Roi_des_animaux\">roi des animaux</a> » car sa crinière lui donne un aspect semblable au <a href=\"https://fr.wikipedia.org/wiki/Soleil\">Soleil</a>, qui apparaît comme « le roi des astres ». Entre 1993 et 2017, leur population a baissé de 43 %<a href=\"https://fr.wikipedia.org/wiki/Lion#cite_note-1\">1</a>.</p><p><a href=\"https://commons.wikimedia.org/wiki/File:Lion_d%27Afrique.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Lion_d%27Afrique.jpg/290px-Lion_d%27Afrique.jpg\" style=\"float:right\" /></a></p><p>Un mâle adulte.</p><table>\t<caption><a href=\"https://fr.wikipedia.org/wiki/Mammal_Species_of_the_World\">Classification selon MSW</a></caption>\t<tbody>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/R%C3%A8gne_(biologie)\">Règne</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Animal\">Animalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Embranchement_(biologie)\">Embranchement</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Chordata\">Chordata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-embranchement\">Sous-embr.</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Vertebrata\">Vertebrata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Classe_(biologie)\">Classe</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Mammalia\">Mammalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Ordre_(biologie)\">Ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-ordre\">Sous-ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Feliformia\">Feliformia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">Famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Felidae\">Felidae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-famille_(biologie)\">Sous-famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Pantherinae\">Pantherinae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Genre_(biologie)\">Genre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Panthera\">Panthera</a></td>\t\t</tr>\t</tbody></table><p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle <img alt=\"( VU )\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Status_iucn3.1_VU-fr.svg/244px-Status_iucn3.1_VU-fr.svg.png\" style=\"float:right\" />du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p>",
                        "displayValue": "<p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p><p>Le lion mâle ne chasse qu'occasionnellement, il est chargé de combattre les intrusions sur le territoire et les menaces contre la troupe. Le lion <a href=\"https://fr.wikipedia.org/wiki/Rugissement\">rugit</a>. Il n'existe plus à l'état sauvage que 16 500 à 30 000 individus dans la <a href=\"https://fr.wikipedia.org/wiki/Savane\">savane</a> <a href=\"https://fr.wikipedia.org/wiki/Afrique\">africaine</a>, répartis en une dizaine de sous-espèces et environ 300 au <a href=\"https://fr.wikipedia.org/wiki/Parc_national_de_Gir_Forest\">parc national de Gir Forest</a> dans le nord-ouest de l'<a href=\"https://fr.wikipedia.org/wiki/Inde\">Inde</a>. Il est surnommé « le <a href=\"https://fr.wikipedia.org/wiki/Roi_des_animaux\">roi des animaux</a> » car sa crinière lui donne un aspect semblable au <a href=\"https://fr.wikipedia.org/wiki/Soleil\">Soleil</a>, qui apparaît comme « le roi des astres ». Entre 1993 et 2017, leur population a baissé de 43 %<a href=\"https://fr.wikipedia.org/wiki/Lion#cite_note-1\">1</a>.</p><p><a href=\"https://commons.wikimedia.org/wiki/File:Lion_d%27Afrique.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Lion_d%27Afrique.jpg/290px-Lion_d%27Afrique.jpg\" style=\"float:right\" /></a></p><p>Un mâle adulte.</p><table>\t<caption><a href=\"https://fr.wikipedia.org/wiki/Mammal_Species_of_the_World\">Classification selon MSW</a></caption>\t<tbody>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/R%C3%A8gne_(biologie)\">Règne</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Animal\">Animalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Embranchement_(biologie)\">Embranchement</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Chordata\">Chordata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-embranchement\">Sous-embr.</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Vertebrata\">Vertebrata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Classe_(biologie)\">Classe</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Mammalia\">Mammalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Ordre_(biologie)\">Ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-ordre\">Sous-ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Feliformia\">Feliformia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">Famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Felidae\">Felidae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-famille_(biologie)\">Sous-famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Pantherinae\">Pantherinae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Genre_(biologie)\">Genre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Panthera\">Panthera</a></td>\t\t</tr>\t</tbody></table><p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle <img alt=\"( VU )\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Status_iucn3.1_VU-fr.svg/244px-Status_iucn3.1_VU-fr.svg.png\" style=\"float:right\" />du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p>"
                    }
                ];
            }
        );

    }

    window.dcp.document.documentController("addEventListener",
        "ready",
        function (event, documentObject, message) {
            this.documentController("showMessage", "I'm ready");
            test()
        }
    );
})(window);


