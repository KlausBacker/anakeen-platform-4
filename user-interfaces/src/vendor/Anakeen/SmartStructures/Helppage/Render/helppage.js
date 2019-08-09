import "./helppage.css";
import _ from "underscore";
import Mustache from "mustache";
import $ from "jquery";
import i18n from "dcpDocument/i18n/documentCatalog";

window.ank.smartElement.globalController.registerFunction("helpPage", controller => {
  /**
   * Display help content according to parameter locale
   * @param lang fr_FR / en_US
   * @param $document current document element
   */
  const helppageDisplayLocale = lang => {
    const template = $("#helppage-template").text();
    const helpValues = controller.getValues();
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
      window.scrollTo(0, $(chapter).offset().top - $(".dcpDocument__menu").height() - 40);
    }
  };

  controller.addEventListener(
    "ready",
    {
      name: "helppageReady",
      check: documentObject => documentObject.family.name === "HELPPAGE"
    },
    function helppageReady(/*event, documentObject*/) {
      const currentLocale = i18n.getLocale().culture.replace("-", "_") || "fr_FR";

      if (window.frameElement) {
        // if document is in dialog window
        if ($(window.frameElement).closest(".k-window").length === 1) {
          const topMenu = controller.getMenus();

          // Hide all menu except lang menu
          topMenu.forEach(itemMenu => {
            if (itemMenu.id !== "helppage-langMenu") {
              controller.getMenu(itemMenu.id).hide();
            } else {
              controller.getMenu(itemMenu.id).setCssClass("menu--right");
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
  controller.addEventListener(
    "custom:helppageSelect",
    {
      name: "helppage-selectchapter",
      check: documentObject => documentObject.family.name === "HELPPAGE"
    },
    function helppageDoSelectChapter(event, chapter) {
      helpPageSelectChapter("#CHAP-" + chapter);
    }
  );

  /**
   * Change locale when lang menu is selected
   */
  controller.addEventListener(
    "actionClick",
    {
      name: "helppage-changelang",
      check: documentObject => documentObject.family.name === "HELPPAGE"
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
        langMenu = controller.getMenu($(options.target).data("menu-id"));
        controller.getMenu("helppage-langMenu").setIconUrl(langMenu.getProperties().iconUrl);
        controller.getMenu("helppage-langMenu").setHtmlLabel("(" + langMenu.getProperties().id.substr(-5, 2) + ") ");
        event.preventDefault();
      }
    }
  );
});
