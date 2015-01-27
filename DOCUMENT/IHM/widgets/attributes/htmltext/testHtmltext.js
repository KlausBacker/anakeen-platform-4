/*global require*/
require([
    'widgets/attributes/htmltext/loaderHtmltext',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/htmltext/htmltextTestAttribute'
], function (widget, defaultTestSuite, htmltextTestSuite) {
    "use strict";

    defaultTestSuite("htmltext : read", widget, {}, {value : "<p>Lorem <strong>Ispum</strong></p>"});
    defaultTestSuite("htmltext : write", widget, {mode : "write", noDestroyTesting:true, useRender:true}, {value : "<p>Lorem <strong>Ispum</strong></p>"});




    htmltextTestSuite("htmltext : spec", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            height:"200px",
            toolbar : "Basic"
        }
    }, {value : "<h1>200px Basic</h1>"},{
        height:200,
        icons : ["cke_button__bold_icon","cke_button__italic_icon","cke_button__numberedlist_icon"],
        notIcons : ["cke_button__source_icon","cke_button__image_icon","cke_button__subscript_icon"]
    });

    htmltextTestSuite("htmltext : spec", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            height:"150px",
            toolbar : "Default"
        }
    }, {value : "<h1>200px Default</h1>"},{
        height:150,
        icons : ["cke_button__bold_icon","cke_button__italic_icon",
            "cke_button__numberedlist_icon","cke_button__source_icon","cke_button__image_icon",
            "cke_button__blockquote_icon"],
        notIcons : []
    });
    htmltextTestSuite("htmltext : spec", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            height:"120px",
            toolbar : "Simple"
        }
    }, {value : "<h1>120px Simple</h1>"},{
        height:120,
        icons : ["cke_button__bold_icon","cke_button__italic_icon",
            "cke_button__numberedlist_icon","cke_button__source_icon",
            "cke_button__image_icon"],
        notIcons : ["cke_button__subscript_icon","cke_button__blockquote_icon"]
    });
    htmltextTestSuite("htmltext : spec", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            height:"250px",
            toolbar : "Full",
            toolbarStartupExpanded:false
        }
    }, {value : "<h1>250px Full</h1>"},{
        height:250,
        icons : ["cke_button__bold_icon","cke_button__italic_icon",
            "cke_button__numberedlist_icon","cke_button__source_icon",
            "cke_button__blockquote_icon","cke_button__image_icon"],
        notIcons : []
    });




    htmltextTestSuite("htmltext : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<pre>{{{value}}}</pre>"
        }

    }, {value : "<h2>Formatage de la valeur</h2>"},{formatValue:"<pre><h2>Formatage de la valeur" +
    "</h2></pre>"});



    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});