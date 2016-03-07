define(["mustache", "jquery", "underscore",
    "dcpDocument/documentCatalog"], function helppageCustomMain(Mustache, $, _, i18n)
{
    "use strict";

    var helppageDisplayLocale = function helppageDisplayLocale(lang, documentController)
    {
        var template = $("#helppage-template").text();
        var helpValues = documentController.documentController("getValues");
        var htmlResult,
            locale = {title: "Untitle", description: '', chapters: []};

        // @TODO sort by help_sec_order
        window.console.log("values",helpValues );
        _.each(helpValues.help_sec_lang, function helppageReadyLocaleFilter(chapterLang, index)
        {
            if (chapterLang.value === lang) {
                locale.chapters.push({
                    "title": helpValues.help_sec_name[index].displayValue,
                    "content": helpValues.help_sec_text[index].displayValue,
                    "id": helpValues.help_sec_key[index].value
                });
            }
        });
        _.each(helpValues.help_lang, function helppageReadyLocaleFilter(titleLang, index)
        {
            if (titleLang.value === lang) {
                locale.title = helpValues.help_name[index].displayValue;
                locale.description = helpValues.help_description[index].displayValue;
                locale.lang = lang.substr(3, 2).toLowerCase();
            }
        });
        htmlResult = Mustache.render(template, {
            "helppage": locale
        });
        $(".helppage--content").html(htmlResult);
    };

    window.dcp.document.documentController("addEventListener",
        "ready",
        {
            "name": "helppageReady",
            "documentCheck": function helppageReadyCheck(documentObject)
            {
                return documentObject.renderMode === "view" && documentObject.family.name === "HELPPAGE";
            }
        },
        function helppageReady(/*event, documentObject*/)
        {
            var currentLocale=i18n.getLocale().culture.replace('-','_') || "fr_FR";
            helppageDisplayLocale(currentLocale, this);
            if (window.location.hash) {
                // Need to force hash to scroll to selected chapter
                _.delay(function helppageReadyScroll()
                {
                    window.location.href = window.location.href;
                    window.scrollBy(0, -100);
                }, 1);
            }
        }
    );

    window.dcp.document.documentController("addEventListener",
        "actionClick",
        {
            "name": "helppage-changelang",
            "documentCheck": function helppageReadyCheck(documentObject)
            {
                return documentObject.renderMode === "view" && documentObject.family.name === "HELPPAGE";
            }
        },
        function changeDisplayError(event, documentObject, options)
        {
            var culture;
            var langMenu;
            if (options.eventId === "helppage" && options.options[0] === "lang") {
                culture = options.options[1];
                helppageDisplayLocale(culture, this);
                langMenu = this.documentController("getMenu", $(options.target).data("menu-id"));
                this.documentController("getMenu", "helppage-langMenu").setIconUrl(langMenu.getProperties().iconUrl);
                this.documentController("getMenu", "helppage-langMenu").setHtmlLabel('('+langMenu.getProperties().id.substr(-5,2)+') ');
            }

        }
    );
});
