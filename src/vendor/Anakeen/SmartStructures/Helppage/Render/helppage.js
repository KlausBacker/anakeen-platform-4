import "./helppage.css";
import _ from "underscore";
import Mustache from "mustache";
import $ from "jquery";
import i18n from "dcpDocument/i18n/documentCatalog";

{
  /**
   * Display help content according to parameter locale
   * @param lang fr_FR / en_US
   * @param $document current document element
   */
  const helppageDisplayLocale = (lang, $document) => {
    const template = $("#helppage-template").text();
    const helpValues = $document.documentController("getValues");
    let htmlResult;
    let locale = { title: "Untitle", description: "", chapters: [] };
    let order;

    helpValues.help_sec_order.forEach((item, k) => {
      item.index = k;
    });

    order = _.sortBy(helpValues.help_sec_order, "value");

    order.forEach(orderFork => {
      const index = orderFork.index;
      const chapterLang = helpValues.help_sec_lang[index];
      if (chapterLang.value === lang) {
        locale.chapters.push({
          order: locale.chapters.length + 1,
          title: helpValues.help_sec_name[index].displayValue,
          content: helpValues.help_sec_text[index].displayValue,
          id: helpValues.help_sec_key[index].value
        });
      }
    });

    helpValues.help_lang.forEach((titleLang, index) => {
      if (titleLang.value === lang) {
        locale.title = helpValues.help_name[index].displayValue;
        locale.description = helpValues.help_description[index].displayValue;
        locale.lang = lang.substr(3, 2).toLowerCase();
      }
    });

    htmlResult = Mustache.render(template, {
      helppage: locale
    });
    $(".helppage--content").html(htmlResult);
  };

  /**
   * Scroll window to selected chapter
   * @param chapter selected chapter
   */
  const helpPageSelectChapter = chapter => {
    $(".helppage--select").removeClass("helppage--select");
    if ($(chapter).length === 1) {
      $(chapter).addClass("helppage--select");
      window.scrollTo(
        0,
        $(chapter).offset().top - $(".dcpDocument__menu").height() - 40
      );
    }
  };

  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "helppageReady",
      documentCheck: documentObject =>
        documentObject.renderMode === "view" &&
        documentObject.family.name === "HELPPAGE"
    },
    function helppageReady(/*event, documentObject*/) {
      const currentLocale =
        i18n.getLocale().culture.replace("-", "_") || "fr_FR";

      if (window.frameElement) {
        // if document is in dialog window
        if ($(window.frameElement).closest(".k-window").length === 1) {
          const topMenu = this.documentController("getMenus");

          // Hide all menu except lang menu
          topMenu.forEach(itemMenu => {
            if (itemMenu.id !== "helppage-langMenu") {
              this.documentController("getMenu", itemMenu.id).hide();
            } else {
              this.documentController("getMenu", itemMenu.id).setCssClass(
                "menu--right"
              );
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
          _.defer(function helppageReadyScroll() {
            helpPageSelectChapter(window.location.hash);
          });
        }
      }
    }
  );

  /**
   * Select chapter and scroll to it
   */
  window.dcp.document.documentController(
    "addEventListener",
    "custom:helppageSelect",
    {
      name: "helppage-selectchapter",
      documentCheck: documentObject =>
        documentObject.renderMode === "view" &&
        documentObject.family.name === "HELPPAGE"
    },
    function helppageDoSelectChapter(event, chapter) {
      helpPageSelectChapter("#CHAP-" + chapter);
    }
  );

  /**
   * Change locale when lang menu is selected
   */
  window.dcp.document.documentController(
    "addEventListener",
    "actionClick",
    {
      name: "helppage-changelang",
      documentCheck: documentObject =>
        documentObject.renderMode === "view" &&
        documentObject.family.name === "HELPPAGE"
    },
    function helppageClickChangeLocale(event, documentObject, options) {
      let culture;
      let langMenu;
      let selectedChapter;

      if (options.eventId === "helppage.lang") {
        culture = options.options[0];
        selectedChapter = $(".helppage--select").attr("id");
        helppageDisplayLocale(culture, this);
        helpPageSelectChapter("#" + selectedChapter);
        langMenu = this.documentController(
          "getMenu",
          $(options.target).data("menu-id")
        );
        this.documentController("getMenu", "helppage-langMenu").setIconUrl(
          langMenu.getProperties().iconUrl
        );
        this.documentController("getMenu", "helppage-langMenu").setHtmlLabel(
          "(" + langMenu.getProperties().id.substr(-5, 2) + ") "
        );
        event.preventDefault();
      }
    }
  );
}
