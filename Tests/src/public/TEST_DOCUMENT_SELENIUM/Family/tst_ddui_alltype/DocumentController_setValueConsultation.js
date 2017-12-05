function test() {
//Exemple
//Cas d'un attribut texte simple :
    window.dcp.document.documentController("setValue", "test_ddui_all__longtext", {value: "Bonjour à tous"});
//Cas d'un attribut énuméré simple : value est la clef
    window.dcp.document.documentController("setValue", "test_ddui_all__account",
        {
            value: "1074",
            displayValue: "Granget Catherine"
        }
    );
//Cas d'une relation (docid) multiple : affectation du troisième élément
    window.dcp.document.documentController("setValue", "test_ddui_all__account_array",
        {
            value: "9876",
            displayValue: "Conjecture de Hodge",
            index: 2
        }
    );
//Cas d'une date dans un tableau : affectation du premier élément
    window.dcp.document.documentController("setValue", "test_ddui_all__date_array",
        {
            value: "1999-12-25",
            index: 0
        }
    );
// Cas d'une relation (docid) multiple : affectation de 3 éléments
    window.dcp.document.documentController("setValue", "test_ddui_all__account_array",
        [
            {value: "1978", displayValue: "Équations de Navier-Stokes"},
            {value: "2032", displayValue: "Conjecture de Poincaré"},
            {value: "2123", displayValue: "Hypothèse de Riemann"}
        ]
    );
//Cas de chiffres dans un tableau : affectation d'une colonne avec 3 éléments. Le résultat produit un tableau avec 3 rangées.
    window.dcp.document.documentController("setValue", "test_ddui_all__integer_array",
        [
            {value: 209.56, displayValue: "209,5 toto"},
            {value: 187, displayValue: ""},
            {value: -12.5, displayValue: ""}

        ]
    );
// Cas de relation multiple dans un tableau : affectation de 3 liens dans la cellule "zct_annexes" de la rangée n°2. Dans ce cas, la valeur contient un tableau de valeurs. Il n'est pas possible de modifier directement un sous-index particulier des relations multiples dans les tableaux.
    window.dcp.document.documentController("setValue", "test_ddui_all__account_multiple_array",
        {
            index: 1,
            value: [
                {value: "4948", displayValue: "Conjecture de Birch et Swinnerton-Dyer"},
                {value: "1032", displayValue: "Équations de Yang-Mills"},
                {value: "2123", displayValue: "Hypothèse de Riemann"}
            ]
        }
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__title", {
            "value": "Tout complet",
            "displayValue": "Tout complet"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__account", {
            "familyRelation": "",
            "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1072&amp;latest=Y",
            "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
            "revision": -1,
            "initid": 1072,
            "fromid": 128,
            "value": "1072",
            "displayValue": "Grand Jean"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__account_multiple", [
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
                "initid": 1073,
                "fromid": 128,
                "value": "1073",
                "displayValue": "Dujardin Isabelle"
            }
        ]
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__docid", {
            "value": null,
            "displayValue": null
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__docid_multiple", []
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__date", {
            "value": "2017-10-04",
            "displayValue": "04/10/2017"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__time", {
            "value": "02:00:00",
            "displayValue": "02:00:00"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__timestamp", {
            "value": "2017-11-06 09:30:00",
            "displayValue": "06/11/2017 09:30"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__integer", {
            "value": 12,
            "displayValue": "12"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__double", {
            "value": 12.34,
            "displayValue": "12.34"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__money", {
            "value": 124,
            "displayValue": "124,00"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__password", {
            "value": "secret",
            "displayValue": "secret"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__color", {
            "value": "#8FFFCB",
            "displayValue": "#8FFFCB"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__file", {
            "value": null,
            "displayValue": null
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__image", {
            "value": null,
            "displayValue": null
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__htmltext", {
            "value": "<p><a href=\"https://commons.wikimedia.org/wiki/File:Solar_prominence_from_STEREO_spacecraft_September_29,_2008.jpg?uselang=fr\"><img alt=\"Image illustrative de l'article Soleil\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg/280px-Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg\" style=\"float:right\" /></a><br />\nUne <a href=\"https://fr.wikipedia.org/wiki/%C3%89ruption_solaire\">éruption solaire</a> vue en <a href=\"https://fr.wikipedia.org/wiki/Ultraviolet\">ultraviolet</a> avec de Le <strong>Soleil</strong> est l’<a href=\"https://fr.wikipedia.org/wiki/%C3%89toile\">étoile</a> du <a href=\"https://fr.wikipedia.org/wiki/Syst%C3%A8me_solaire\">Système solaire</a>. Dans la classification <a href=\"https://fr.wikipedia.org/wiki/Astronomie\">astronomique</a>, c’est une étoile de type <a href=\"https://fr.wikipedia.org/wiki/Naine_jaune\">naine jaune</a> d'une masse d'environ 1,9891 × 1030 kg, composée d’<a href=\"https://fr.wikipedia.org/wiki/Hydrog%C3%A8ne\">hydrogène</a>(75 % de la masse ou 92 % du volume) et d’<a href=\"https://fr.wikipedia.org/wiki/H%C3%A9lium\">hélium</a> (25 % de la masse ou 8 % du volume)<a href=\"https://fr.wikipedia.org/wiki/Soleil#cite_note-9\">9</a>. Le Soleil fait partie de la <a href=\"https://fr.wikipedia.org/wiki/Galaxie\">galaxie</a> appelée la <a href=\"https://fr.wikipedia.org/wiki/Voie_lact%C3%A9e\">Voie lactée</a> et se situe à environ 8 <a href=\"https://fr.wikipedia.org/wiki/Kiloparsec\">kpc</a> (∼26 100 <a href=\"https://fr.wikipedia.org/wiki/Ann%C3%A9e-lumi%C3%A8re\">a.l.</a>) du <a href=\"https://fr.wikipedia.org/wiki/Centre_galactique\">centre galactique</a>. Autour de lui gravitent la <a href=\"https://fr.wikipedia.org/wiki/Terre\">Terre</a> (à la vitesse de 30 km/s), sept autres <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te\">planètes</a>, au moins cinq <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te_naine\">planètes naines</a>, de très nombreux <a href=\"https://fr.wikipedia.org/wiki/Ast%C3%A9ro%C3%AFde\">astéroïdes</a> et <a href=\"https://fr.wikipedia.org/wiki/Com%C3%A8te\">comètes</a> et une bande de <a href=\"https://fr.wikipedia.org/wiki/Lumi%C3%A8re_zodiacale\">poussière</a>. Le Soleil représente à lui seul 99,86 % de la masse du Système solaire ainsi constitué, <a href=\"https://fr.wikipedia.org/wiki/Jupiter_(plan%C3%A8te)\">Jupiter</a> représentant plus des deux tiers du reste.</p>",
            "displayValue": "<p><a href=\"https://commons.wikimedia.org/wiki/File:Solar_prominence_from_STEREO_spacecraft_September_29,_2008.jpg?uselang=fr\"><img alt=\"Image illustrative de l'article Soleil\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg/280px-Solar_prominence_from_STEREO_spacecraft_September_29%2C_2008.jpg\" style=\"float:right\" /></a><br />\nUne <a href=\"https://fr.wikipedia.org/wiki/%C3%89ruption_solaire\">éruption solaire</a> vue en <a href=\"https://fr.wikipedia.org/wiki/Ultraviolet\">ultraviolet</a> avec de Le <strong>Soleil</strong> est l’<a href=\"https://fr.wikipedia.org/wiki/%C3%89toile\">étoile</a> du <a href=\"https://fr.wikipedia.org/wiki/Syst%C3%A8me_solaire\">Système solaire</a>. Dans la classification <a href=\"https://fr.wikipedia.org/wiki/Astronomie\">astronomique</a>, c’est une étoile de type <a href=\"https://fr.wikipedia.org/wiki/Naine_jaune\">naine jaune</a> d'une masse d'environ 1,9891 × 1030 kg, composée d’<a href=\"https://fr.wikipedia.org/wiki/Hydrog%C3%A8ne\">hydrogène</a>(75 % de la masse ou 92 % du volume) et d’<a href=\"https://fr.wikipedia.org/wiki/H%C3%A9lium\">hélium</a> (25 % de la masse ou 8 % du volume)<a href=\"https://fr.wikipedia.org/wiki/Soleil#cite_note-9\">9</a>. Le Soleil fait partie de la <a href=\"https://fr.wikipedia.org/wiki/Galaxie\">galaxie</a> appelée la <a href=\"https://fr.wikipedia.org/wiki/Voie_lact%C3%A9e\">Voie lactée</a> et se situe à environ 8 <a href=\"https://fr.wikipedia.org/wiki/Kiloparsec\">kpc</a> (∼26 100 <a href=\"https://fr.wikipedia.org/wiki/Ann%C3%A9e-lumi%C3%A8re\">a.l.</a>) du <a href=\"https://fr.wikipedia.org/wiki/Centre_galactique\">centre galactique</a>. Autour de lui gravitent la <a href=\"https://fr.wikipedia.org/wiki/Terre\">Terre</a> (à la vitesse de 30 km/s), sept autres <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te\">planètes</a>, au moins cinq <a href=\"https://fr.wikipedia.org/wiki/Plan%C3%A8te_naine\">planètes naines</a>, de très nombreux <a href=\"https://fr.wikipedia.org/wiki/Ast%C3%A9ro%C3%AFde\">astéroïdes</a> et <a href=\"https://fr.wikipedia.org/wiki/Com%C3%A8te\">comètes</a> et une bande de <a href=\"https://fr.wikipedia.org/wiki/Lumi%C3%A8re_zodiacale\">poussière</a>. Le Soleil représente à lui seul 99,86 % de la masse du Système solaire ainsi constitué, <a href=\"https://fr.wikipedia.org/wiki/Jupiter_(plan%C3%A8te)\">Jupiter</a> représentant plus des deux tiers du reste.</p>"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__longtext", {
            "value": "La Lune est l'unique satellite naturel de la Terre. Suivant la désignation systématique des satellites, la Lune est appelée Terre I2 ; cependant en pratique cette forme n'est pas utilisée. Elle est le cinquième plus grand satellite du Système solaire, avec un diamètre de 3 474 km. La distance moyenne séparant la Terre de la Lune est de 384 467 km3.\n\nLa Lune est le premier et le seul objet non terrestre visité par l'Homme. Le premier à y avoir marché est l'astronaute américain Neil Armstrong le 21 juillet 1969. Après lui, onze autres hommes ont foulé le sol de la Lune, tous membres du programme Apollo.",
            "displayValue": "La Lune est l'unique satellite naturel de la Terre. Suivant la désignation systématique des satellites, la Lune est appelée Terre I2 ; cependant en pratique cette forme n'est pas utilisée. Elle est le cinquième plus grand satellite du Système solaire, avec un diamètre de 3 474 km. La distance moyenne séparant la Terre de la Lune est de 384 467 km3.\n\nLa Lune est le premier et le seul objet non terrestre visité par l'Homme. Le premier à y avoir marché est l'astronaute américain Neil Armstrong le 21 juillet 1969. Après lui, onze autres hommes ont foulé le sol de la Lune, tous membres du programme Apollo."
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__text", {
            "value": "Mars est originellement le nom du dieu de la guerre dans la mythologie romaine",
            "displayValue": "Mars est originellement le nom du dieu de la guerre dans la mythologie romaine"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumlist", {
            "exists": true,
            "value": "AD",
            "displayValue": "Andorre"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumauto", {
            "exists": true,
            "value": "AD",
            "displayValue": "Andorre"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumvertical", {
            "exists": true,
            "value": "0",
            "displayValue": "0 %"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumhorizontal", {
            "exists": true,
            "value": "red",
            "displayValue": "Rouge"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumbool", {
            "exists": true,
            "value": "N",
            "displayValue": "Normal"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumserverlist", {
            "exists": true,
            "value": "AD",
            "displayValue": "Andorre"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumserverauto", {
            "exists": true,
            "value": "AD",
            "displayValue": "Andorre"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumserververtical", {
            "exists": true,
            "value": "red",
            "displayValue": "Rouge"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumserverhorizontal", {
            "exists": true,
            "value": "0",
            "displayValue": "0 %"
        }
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumserverbool", {
            "exists": true,
            "value": "C",
            "displayValue": "Critique"
        }
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__enumslist", [
            {
                "exists": true,
                "value": "DK",
                "displayValue": "Danemark"
            },
            {
                "exists": true,
                "value": "DM",
                "displayValue": "Dominique"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsauto", [
            {
                "exists": true,
                "value": "AN",
                "displayValue": "Antilles néerlandaises"
            },
            {
                "exists": true,
                "value": "BJ",
                "displayValue": "Bénin"
            },
            {
                "exists": true,
                "value": "BR",
                "displayValue": "Brésil"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsvertical", [
            {
                "exists": true,
                "value": "30",
                "displayValue": "30 %"
            },
            {
                "exists": true,
                "value": "70",
                "displayValue": "70 %"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumshorizontal", [
            {
                "exists": true,
                "value": "yellow",
                "displayValue": "Jaune"
            },
            {
                "exists": true,
                "value": "lightblue",
                "displayValue": "Bleu/Bleu ciel"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsserverlist", [
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
                "value": "CX",
                "displayValue": "Ile Christmas"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsserverauto", [
            {
                "exists": true,
                "value": "ZR",
                "displayValue": "Zaïre (République Démocratique du Congo)"
            },
            {
                "exists": true,
                "value": "BD",
                "displayValue": "Bangladesh"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsserververtical", [
            {
                "exists": true,
                "value": "30",
                "displayValue": "30 %"
            },
            {
                "exists": true,
                "value": "100",
                "displayValue": "100 %"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__enumsserverhorizontal", [
            {
                "exists": true,
                "value": "yellow",
                "displayValue": "Jaune"
            },
            {
                "exists": true,
                "value": "navyblue",
                "displayValue": "Bleu/Bleu marine"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__date_array", [
            {
                "value": "2017-10-12",
                "displayValue": "12/10/2017"
            },
            {
                "value": "2017-11-17",
                "displayValue": "17/11/2017"
            },
            {
                "value": "2017-07-05",
                "displayValue": "05/07/2017"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__time_array", [
            {
                "value": "09:00",
                "displayValue": "09:00"
            },
            {
                "value": "02:00",
                "displayValue": "02:00"
            },
            {
                "value": "13:30",
                "displayValue": "13:30"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__timestamp_array", [
            {
                "value": "2017-11-08T17:30:00",
                "displayValue": "08/11/2017 17:30"
            },
            {
                "value": "2017-06-14T11:00:00",
                "displayValue": "14/06/2017 11:00"
            },
            {
                "value": "2017-11-29T00:00:00",
                "displayValue": "29/11/2017 00:00"
            }
        ]
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__docid_array", []
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__docid_multiple_array", []
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__account_array", [
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
                "value": "1075",
                "displayValue": "tordi Albert"
            }
        ]
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__account_multiple_array", {
            index: 0,
            value:
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
                        "value": "1072",
                        "displayValue": "Grand Jean"
                    }
                ],
            index: 1,
            value:
                [

                    {
                        "familyRelation": "",
                        "url": "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=1075&amp;latest=Y",
                        "icon": "api/v1/images/assets/sizes/14x14c/dynacase-iuser.png",
                        "revision": -1,
                        "initid": 1075,
                        "fromid": 128,
                        "value": "1075",
                        "displayValue": "tordi Albert"
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
        }
    );

    window.dcp.document.documentController("setValue", "test_ddui_all__double_array", [
            {
                "value": 12.567,
                "displayValue": "12.567"
            },
            {
                "value": 31346.787632,
                "displayValue": "31346.787632"
            },
            {
                "value": 535.356756832,
                "displayValue": "535.356756832"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__integer_array", [
            {
                "value": 244,
                "displayValue": "244"
            },
            {
                "value": 68732,
                "displayValue": "68732"
            },
            {
                "value": -4563,
                "displayValue": "-4563"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__money_array", [
            {
                "value": 34.6,
                "displayValue": "34,60"
            },
            {
                "value": 36287,
                "displayValue": "36 287,00"
            },
            {
                "value": -43563.5,
                "displayValue": "-43 563,50"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__color_array", [
            {
                "value": "#FFBDBD",
                "displayValue": "#FFBDBD"
            },
            {
                "value": "#CCA6FF",
                "displayValue": "#CCA6FF"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__password_array", [
            {
                "value": "sec",
                "displayValue": "sec"
            },
            {
                "value": "vilet",
                "displayValue": "vilet"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__file_array", []
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__image_array", []
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__text_array", [
            {
                "value": "L'ours",
                "displayValue": "L'ours"
            },
            {
                "value": "Le lion",
                "displayValue": "Le lion"
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__longtext_array", [
            {
                "value": "Les ours sont chassés depuis la préhistoire pour leur viande et leur fourrure. Ils ont joué un rôle de premier plan dans la culture (mythologie, légendes, etc.) et les arts. À l'époque moderne, les populations d'ours sont victimes de pressions (comme celles des éleveurs dans les Pyrénées), de l'empiètement de l'homme sur son habitat naturel, de l'artificialisation et de la fragmentation des forêts, ainsi que du commerce illicite, notamment le marché asiatique de la bile d'ours. L'UICN a classé six espèces d'ours comme vulnérables ou menacées d'extinction. L'ours brun pourrait disparaître dans certains pays européens. Le braconnage et le commerce international des populations les plus en danger sont interdits, mais se pratiquent toujours.",
                "displayValue": "Les ours sont chassés depuis la préhistoire pour leur viande et leur fourrure. Ils ont joué un rôle de premier plan dans la culture (mythologie, légendes, etc.) et les arts. À l'époque moderne, les populations d'ours sont victimes de pressions (comme celles des éleveurs dans les Pyrénées), de l'empiètement de l'homme sur son habitat naturel, de l'artificialisation et de la fragmentation des forêts, ainsi que du commerce illicite, notamment le marché asiatique de la bile d'ours. L'UICN a classé six espèces d'ours comme vulnérables ou menacées d'extinction. L'ours brun pourrait disparaître dans certains pays européens. Le braconnage et le commerce international des populations les plus en danger sont interdits, mais se pratiquent toujours."
            },
            {
                "value": "Le lion est le deuxième plus grand félidé, après le tigre, et ainsi le plus grand carnivore d'Afrique. Un mâle mesure de 172 à 250 centimètres de long2 du bout du museau à la base de la queue et possède une queue d’en moyenne 90 centimètres3. Les mâles atteignent une masse comprise entre 145 et 225 kilogrammes à l'âge adulte3. La lionne adulte mesure de 158 à 192 centimètres2 sans la queue et possède une queue mesurant environ 85 cm. Elles pèsent entre 83 et 168 kg3 et ont une corpulence en moyenne 20 à 50 % moins importante que celle d'un mâle4.",
                "displayValue": "Le lion est le deuxième plus grand félidé, après le tigre, et ainsi le plus grand carnivore d'Afrique. Un mâle mesure de 172 à 250 centimètres de long2 du bout du museau à la base de la queue et possède une queue d’en moyenne 90 centimètres3. Les mâles atteignent une masse comprise entre 145 et 225 kilogrammes à l'âge adulte3. La lionne adulte mesure de 158 à 192 centimètres2 sans la queue et possède une queue mesurant environ 85 cm. Elles pèsent entre 83 et 168 kg3 et ont une corpulence en moyenne 20 à 50 % moins importante que celle d'un mâle4."
            }
        ]
    );
    window.dcp.document.documentController("setValue", "test_ddui_all__htmltext_array", [
            {
                "value": "<p><a href=\"https://commons.wikimedia.org/wiki/File:Ursus_arctos_Dessin_ours_brun_grand.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Ursus_arctos_Dessin_ours_brun_grand.jpg/290px-Ursus_arctos_Dessin_ours_brun_grand.jpg\" style=\"float:right\" /></a></p><p>Les <strong>ours</strong> forment la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8res\">mammifères</a> des <strong>ursidés</strong> (<strong>Ursidae</strong>), de l'ordre des <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> (<a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a>). Le <a href=\"https://fr.wikipedia.org/wiki/Grand_panda\">Grand panda</a>, dont la classification a longtemps prêté à débat, est aujourd'hui considéré comme un ours <a href=\"https://fr.wikipedia.org/wiki/Herbivore\">herbivore</a> au sein de cette famille<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-1\">1</a>,<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-2\">2</a>. Il n'existe que huit espèces d'ours vivantes réparties dans une grande variété d'<a href=\"https://fr.wikipedia.org/wiki/Habitat_(%C3%A9cologie)\">habitats</a>, à la fois dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'<a href=\"https://fr.wikipedia.org/wiki/Europe\">Europe</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Nord\">Amérique du Nord</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Sud\">Amérique du Sud</a>, et en <a href=\"https://fr.wikipedia.org/wiki/Asie\">Asie</a>.</p><p>Les ours modernes ont comme caractéristiques un corps grand, trapu et massif, un long museau, un pelage dense, des pattes plantigrades à cinq griffes non rétractiles et une queue courte. L'ours blanc est principalement carnassier. Le <a href=\"https://fr.wikipedia.org/wiki/Panda_g%C3%A9ant\">panda géant</a> se nourrit presque exclusivement de <a href=\"https://fr.wikipedia.org/wiki/Bambou\">bambou</a>. Les six autres espèces sont <a href=\"https://fr.wikipedia.org/wiki/Omnivore\">omnivores</a>, leur alimentation variée comprend essentiellement des plantes et des animaux. Sauf en période de <a href=\"https://fr.wikipedia.org/wiki/Reproduction_(biologie)\">reproduction</a> et d'éducation des jeunes, les ours sont solitaires. Généralement <a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a>, ils sont aussi éventuellement actifs la nuit ou au crépuscule, en particulier autour des zones d'habitation humaine. On les dit parfois « <a href=\"https://fr.wikipedia.org/wiki/Nocturne_(comportement_animal)\">nocto</a>-<a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a> ». Aidé par un odorat développé, l'ours peut, malgré sa corpulence, courir rapidement, nager et escalader certaines parois ou des arbres. Cavernicole, il se réfugie volontiers dans des grottes, cavernes et tanières. La plupart des espèces y passent la saison froide à dormir (<a href=\"https://fr.wikipedia.org/wiki/Hivernation\">hivernation</a>).</p><p><a href=\"https://fr.wikipedia.org/wiki/Ours_brun\">Ours brun</a> (<em>Ursus arctos</em>)</p>",
                "displayValue": "<p><a href=\"https://commons.wikimedia.org/wiki/File:Ursus_arctos_Dessin_ours_brun_grand.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Ursus_arctos_Dessin_ours_brun_grand.jpg/290px-Ursus_arctos_Dessin_ours_brun_grand.jpg\" style=\"float:right\" /></a></p><p>Les <strong>ours</strong> forment la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8res\">mammifères</a> des <strong>ursidés</strong> (<strong>Ursidae</strong>), de l'ordre des <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> (<a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a>). Le <a href=\"https://fr.wikipedia.org/wiki/Grand_panda\">Grand panda</a>, dont la classification a longtemps prêté à débat, est aujourd'hui considéré comme un ours <a href=\"https://fr.wikipedia.org/wiki/Herbivore\">herbivore</a> au sein de cette famille<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-1\">1</a>,<a href=\"https://fr.wikipedia.org/wiki/Ursidae#cite_note-2\">2</a>. Il n'existe que huit espèces d'ours vivantes réparties dans une grande variété d'<a href=\"https://fr.wikipedia.org/wiki/Habitat_(%C3%A9cologie)\">habitats</a>, à la fois dans l'hémisphère Nord et dans une partie de l'hémisphère Sud. Les ours vivent sur les continents d'<a href=\"https://fr.wikipedia.org/wiki/Europe\">Europe</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Nord\">Amérique du Nord</a>, d'<a href=\"https://fr.wikipedia.org/wiki/Am%C3%A9rique_du_Sud\">Amérique du Sud</a>, et en <a href=\"https://fr.wikipedia.org/wiki/Asie\">Asie</a>.</p><p>Les ours modernes ont comme caractéristiques un corps grand, trapu et massif, un long museau, un pelage dense, des pattes plantigrades à cinq griffes non rétractiles et une queue courte. L'ours blanc est principalement carnassier. Le <a href=\"https://fr.wikipedia.org/wiki/Panda_g%C3%A9ant\">panda géant</a> se nourrit presque exclusivement de <a href=\"https://fr.wikipedia.org/wiki/Bambou\">bambou</a>. Les six autres espèces sont <a href=\"https://fr.wikipedia.org/wiki/Omnivore\">omnivores</a>, leur alimentation variée comprend essentiellement des plantes et des animaux. Sauf en période de <a href=\"https://fr.wikipedia.org/wiki/Reproduction_(biologie)\">reproduction</a> et d'éducation des jeunes, les ours sont solitaires. Généralement <a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a>, ils sont aussi éventuellement actifs la nuit ou au crépuscule, en particulier autour des zones d'habitation humaine. On les dit parfois « <a href=\"https://fr.wikipedia.org/wiki/Nocturne_(comportement_animal)\">nocto</a>-<a href=\"https://fr.wikipedia.org/wiki/Diurne_(comportement_animal)\">diurnes</a> ». Aidé par un odorat développé, l'ours peut, malgré sa corpulence, courir rapidement, nager et escalader certaines parois ou des arbres. Cavernicole, il se réfugie volontiers dans des grottes, cavernes et tanières. La plupart des espèces y passent la saison froide à dormir (<a href=\"https://fr.wikipedia.org/wiki/Hivernation\">hivernation</a>).</p><p><a href=\"https://fr.wikipedia.org/wiki/Ours_brun\">Ours brun</a> (<em>Ursus arctos</em>)</p>"
            },
            {
                "value": "<p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p><p>Le lion mâle ne chasse qu'occasionnellement, il est chargé de combattre les intrusions sur le territoire et les menaces contre la troupe. Le lion <a href=\"https://fr.wikipedia.org/wiki/Rugissement\">rugit</a>. Il n'existe plus à l'état sauvage que 16 500 à 30 000 individus dans la <a href=\"https://fr.wikipedia.org/wiki/Savane\">savane</a> <a href=\"https://fr.wikipedia.org/wiki/Afrique\">africaine</a>, répartis en une dizaine de sous-espèces et environ 300 au <a href=\"https://fr.wikipedia.org/wiki/Parc_national_de_Gir_Forest\">parc national de Gir Forest</a> dans le nord-ouest de l'<a href=\"https://fr.wikipedia.org/wiki/Inde\">Inde</a>. Il est surnommé « le <a href=\"https://fr.wikipedia.org/wiki/Roi_des_animaux\">roi des animaux</a> » car sa crinière lui donne un aspect semblable au <a href=\"https://fr.wikipedia.org/wiki/Soleil\">Soleil</a>, qui apparaît comme « le roi des astres ». Entre 1993 et 2017, leur population a baissé de 43 %<a href=\"https://fr.wikipedia.org/wiki/Lion#cite_note-1\">1</a>.</p><p><a href=\"https://commons.wikimedia.org/wiki/File:Lion_d%27Afrique.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Lion_d%27Afrique.jpg/290px-Lion_d%27Afrique.jpg\" style=\"float:right\" /></a></p><p>Un mâle adulte.</p><table>\t<caption><a href=\"https://fr.wikipedia.org/wiki/Mammal_Species_of_the_World\">Classification selon MSW</a></caption>\t<tbody>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/R%C3%A8gne_(biologie)\">Règne</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Animal\">Animalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Embranchement_(biologie)\">Embranchement</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Chordata\">Chordata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-embranchement\">Sous-embr.</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Vertebrata\">Vertebrata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Classe_(biologie)\">Classe</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Mammalia\">Mammalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Ordre_(biologie)\">Ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-ordre\">Sous-ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Feliformia\">Feliformia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">Famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Felidae\">Felidae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-famille_(biologie)\">Sous-famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Pantherinae\">Pantherinae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Genre_(biologie)\">Genre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Panthera\">Panthera</a></td>\t\t</tr>\t</tbody></table><p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle <img alt=\"( VU )\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Status_iucn3.1_VU-fr.svg/244px-Status_iucn3.1_VU-fr.svg.png\" style=\"float:right\" />du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p>",
                "displayValue": "<p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p><p>Le lion mâle ne chasse qu'occasionnellement, il est chargé de combattre les intrusions sur le territoire et les menaces contre la troupe. Le lion <a href=\"https://fr.wikipedia.org/wiki/Rugissement\">rugit</a>. Il n'existe plus à l'état sauvage que 16 500 à 30 000 individus dans la <a href=\"https://fr.wikipedia.org/wiki/Savane\">savane</a> <a href=\"https://fr.wikipedia.org/wiki/Afrique\">africaine</a>, répartis en une dizaine de sous-espèces et environ 300 au <a href=\"https://fr.wikipedia.org/wiki/Parc_national_de_Gir_Forest\">parc national de Gir Forest</a> dans le nord-ouest de l'<a href=\"https://fr.wikipedia.org/wiki/Inde\">Inde</a>. Il est surnommé « le <a href=\"https://fr.wikipedia.org/wiki/Roi_des_animaux\">roi des animaux</a> » car sa crinière lui donne un aspect semblable au <a href=\"https://fr.wikipedia.org/wiki/Soleil\">Soleil</a>, qui apparaît comme « le roi des astres ». Entre 1993 et 2017, leur population a baissé de 43 %<a href=\"https://fr.wikipedia.org/wiki/Lion#cite_note-1\">1</a>.</p><p><a href=\"https://commons.wikimedia.org/wiki/File:Lion_d%27Afrique.jpg?uselang=fr\"><img alt=\"Description de cette image, également commentée ci-après\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Lion_d%27Afrique.jpg/290px-Lion_d%27Afrique.jpg\" style=\"float:right\" /></a></p><p>Un mâle adulte.</p><table>\t<caption><a href=\"https://fr.wikipedia.org/wiki/Mammal_Species_of_the_World\">Classification selon MSW</a></caption>\t<tbody>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/R%C3%A8gne_(biologie)\">Règne</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Animal\">Animalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Embranchement_(biologie)\">Embranchement</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Chordata\">Chordata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-embranchement\">Sous-embr.</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Vertebrata\">Vertebrata</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Classe_(biologie)\">Classe</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Mammalia\">Mammalia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Ordre_(biologie)\">Ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Carnivora\">Carnivora</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-ordre\">Sous-ordre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Feliformia\">Feliformia</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">Famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Felidae\">Felidae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Sous-famille_(biologie)\">Sous-famille</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Pantherinae\">Pantherinae</a></td>\t\t</tr>\t\t<tr>\t\t\t<th scope=\"row\"><a href=\"https://fr.wikipedia.org/wiki/Genre_(biologie)\">Genre</a></th>\t\t\t<td><a href=\"https://fr.wikipedia.org/wiki/Panthera\">Panthera</a></td>\t\t</tr>\t</tbody></table><p>Le <strong>lion</strong> (<em><strong>Panthera leo</strong></em>) est une <a href=\"https://fr.wikipedia.org/wiki/Esp%C3%A8ce\">espèce</a> de <a href=\"https://fr.wikipedia.org/wiki/Mammif%C3%A8re\">mammifères</a> <a href=\"https://fr.wikipedia.org/wiki/Carnivora\">carnivores</a> de la <a href=\"https://fr.wikipedia.org/wiki/Famille_(biologie)\">famille</a> des <a href=\"https://fr.wikipedia.org/wiki/Felidae\">félidés</a>. La femelle <img alt=\"( VU )\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Status_iucn3.1_VU-fr.svg/244px-Status_iucn3.1_VU-fr.svg.png\" style=\"float:right\" />du lion est la <strong>lionne</strong>, son petit est le <strong>lionceau</strong>. Le mâle adulte, aisément reconnaissable à son importante <a href=\"https://fr.wikipedia.org/wiki/Crini%C3%A8re\">crinière</a>, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. Un mâle adulte se nourrit de 7 kg de viande chaque jour contre 5 kgchez la <a href=\"https://fr.wikipedia.org/wiki/Femelle\">femelle</a>. Le lion est un animal <a href=\"https://fr.wikipedia.org/wiki/Gr%C3%A9garisme\">grégaire</a>, c'est-à-dire qu'il vit en larges groupes familiaux, contrairement aux autres <a href=\"https://fr.wikipedia.org/wiki/F%C3%A9lin\">félins</a>. Son espérance de vie, à l'<a href=\"https://fr.wikipedia.org/wiki/Animal_sauvage\">état sauvage</a>, est comprise entre 7 et 12 ans pour le mâle et 14 à 20 ans pour la femelle, mais il dépasse fréquemment les 30 ans en captivité.</p>"
            }
        ]
    );
}


window.dcp.document.documentController("addEventListener",
    "ready",
    function (event, documentObject, message) {
        this.documentController("showMessage", "I'm ready");
        test()

    }
);