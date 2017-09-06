define(["mustache", "jquery", "underscore",
    "dcpDocument/i18n/documentCatalog"], function helppageCustomMain(Mustache, $, _, i18n)
{
    "use strict";

    /**
     * Display help content according to parameter locale
     * @param lang fr_FR / en_US
     * @param $document current document element
     */
    var helppageDisplayLocale = function helppageDisplayLocale(lang, $document)
    {
        var template = $("#helppage-template").text();
        var helpValues = $document.documentController("getValues");
        var htmlResult,
            locale = {title: "Untitle", description: '', chapters: []};
        var order;

        _.each(helpValues.help_sec_order, function x(item, k) {
            item.index=k;
        });

        order=_.sortBy(helpValues.help_sec_order, "value");

        _.each(order, function helppageReadyLocaleFilter(orderFork)
        {
            var index=orderFork.index;
            var chapterLang=helpValues.help_sec_lang[index];
            if (chapterLang.value === lang) {
                locale.chapters.push({
                    "order":locale.chapters.length + 1,
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

    /**
     * Scroll window to selected chapter
     * @param chapter selected chapter
     */
    var helpPageSelectChapter = function helpPageSelectChapter(chapter)
    {
        $(".helppage--select").removeClass("helppage--select");
        if ($(chapter).length === 1) {
            $(chapter).addClass("helppage--select");
            //$(chapter).get(0).scrollIntoView();
            window.scrollTo(0, $(chapter).offset().top - $(".dcpDocument__menu").height() - 40);
        }
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
            var currentLocale = i18n.getLocale().culture.replace('-', '_') || "fr_FR";

            if (window.frameElement) {
                // if document is in dialog window
                if ($(window.frameElement).closest(".k-window").length === 1) {
                    var topMenu = this.documentController("getMenus");
                    var wDocument = this;

                    // Hide all menu except lang menu
                    _.each(topMenu, function helppageReadyHideMenu(itemMenu)
                    {
                        if (itemMenu.id !== "helppage-langMenu") {
                            wDocument.documentController("getMenu", itemMenu.id).hide();
                        } else {
                            wDocument.documentController("getMenu", itemMenu.id).setCssClass("menu--right");
                        }
                    });
                    // Fix menu
                    $(window).off("scroll.ddui");
                    $(".dcpDocument__menu").addClass("menu--fixed");
                    // hide header
                    this.find(".dcpDocument__header").hide();
                }
                helppageDisplayLocale(currentLocale, this);
            } else {
                helppageDisplayLocale(currentLocale, this);
                if (window.location.hash) {
                    // Need to force hash to scroll to selected chapter
                    _.defer(function helppageReadyScroll()
                    {
                        helpPageSelectChapter(window.location.hash);
                    });
                }
            }
        }
    );

    /**
     * Select chapter and scroll to it
     */
    window.dcp.document.documentController("addEventListener",
        "custom:helppageSelect",
        {
            "name": "helppage-selectchapter",
            "documentCheck": function helppageReadyCheck(documentObject)
            {
                return documentObject.renderMode === "view" && documentObject.family.name === "HELPPAGE";
            }
        },
        function helppageDoSelectChapter(event, chapter)
        {
            helpPageSelectChapter("#CHAP-" + chapter);
        }
    );

    /**
     * Change locale when lang menu is selected
     */
    window.dcp.document.documentController("addEventListener",
        "actionClick",
        {
            "name": "helppage-changelang",
            "documentCheck": function helppageClickCheck(documentObject)
            {
                return documentObject.renderMode === "view" && documentObject.family.name === "HELPPAGE";
            }
        },
        function helppageClickChangeLocale(event, documentObject, options)
        {
            var culture;
            var langMenu, selectedChapter;
            if (options.eventId === "helppage.lang") {
                culture = options.options[0];
                selectedChapter = $(".helppage--select").attr("id");
                helppageDisplayLocale(culture, this);
                helpPageSelectChapter("#" + selectedChapter);
                langMenu = this.documentController("getMenu", $(options.target).data("menu-id"));
                this.documentController("getMenu", "helppage-langMenu").setIconUrl(langMenu.getProperties().iconUrl);
                this.documentController("getMenu", "helppage-langMenu").setHtmlLabel('(' + langMenu.getProperties().id.substr(-5, 2) + ') ');
                event.preventDefault();
            }

        }
    );
});
