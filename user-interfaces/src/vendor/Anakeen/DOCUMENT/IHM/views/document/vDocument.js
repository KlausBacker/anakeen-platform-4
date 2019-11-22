/*global define, console*/

define([
  "underscore",
  "jquery",
  "backbone",
  "mustache",
  "dcpDocument/models/mDocumentTab",
  "dcpDocument/views/document/menu/vMenu",
  "dcpDocument/views/document/header/vHeader",
  "dcpDocument/views/attributes/frame/vFrame",
  "dcpDocument/views/attributes/tab/vTabLabel",
  "dcpDocument/views/attributes/tab/vTabContent",
  "dcpDocument/views/document/attributeTemplate",
  "dcpDocument/models/mTransitionGraph",
  "dcpDocument/views/workflow/vTransitionGraph",
  "dcpDocument/i18n/documentCatalog",
  "dcpDocument/widgets/globalController/utils/EventUtils",
  "dcpDocument/widgets/history/wHistory",
  "dcpDocument/widgets/properties/wProperties"
], function vDocument(
  _,
  $,
  Backbone,
  Mustache,
  ModelDocumentTab,
  ViewDocumentMenu,
  ViewDocumentHeader,
  ViewAttributeFrame,
  ViewAttributeTabLabel,
  ViewAttributeTabContent,
  attributeTemplate,
  ModelTransitionGraph,
  ViewTransitionGraph,
  i18n,
  EventPromiseUtils
) {
  "use strict";

  var checkTouchEvents = function checkTouchEvents() {
    //From modernizer
    var bool = false;
    if ("ontouchstart" in window || (window.DocumentTouch && window.document instanceof window.DocumentTouch)) {
      bool = true;
    }
    return bool;
  };

  // noinspection JSUnusedLocalSymbols
  return Backbone.View.extend({
    className: "dcpDocument container-fluid",

    events: {
      'click a[href^="#action/"], a[data-action], button[data-action]': "propagateActionClick",
      'click .dcpDocument__body a[href^="#"]': "handleHashClick"
    },

    /**
     * Init event
     */
    initialize: function vDocumentInitialize() {
      this.listenTo(this.model, "destroy", this.remove);
      this.listenTo(this.model, "displayLoading", this.displayLoading);
      this.listenTo(this.model, "hideLoading", this.hideLoading);
      this.listenTo(this.model, "invalid", this.showView);
      this.listenTo(this.model, "displayNetworkError", this.displayNetworkError);
      this.listenTo(this.model, "actionAttributeLink", this.doStandardAction);
      this.listenTo(this.model, "loadDocument", this.loadDocument);
      this.listenTo(this.model, "displayCloseDocument", this.displayCloseDocument);
      this.listenTo(this.model, "redrawErrorMessages", this.redrawTootips);
      this.listenTo(this.model, "doSelectTab", this.selectTab);
      this.listenTo(this.model, "doDrawTab", this.drawTab);
      this.listenTo(this.model, "dduiDocumentReady", this.cleanAndRender);
      this.listenTo(this.model, "dduiDocumentDisplayView", this.showView);
    },

    /**
     * Clean the associated view and re-render it
     */
    cleanAndRender: function vDocumentCleanAndRender() {
      this.trigger("loaderShow", i18n.___("Rendering", "ddui"), 70);
      $(".dcpStaticErrorMessage").attr("hidden", true);
      this.$el.show();
      this.$el[0].className = this.$el[0].className.replace(/\bdcpFamily.*\b/g, "");
      this.$el.removeClass("dcpDocument--view").removeClass("dcpDocument--edit");
      try {
        if (this.historyWidget) {
          this.historyWidget.destroy();
        }
        if (this.propertiesWidget) {
          this.propertiesWidget.destroy();
        }
        if (this.helpWidget) {
          this.helpWidget.destroy();
        }
        if (this.transitionGraph && this.transitionGraph.view) {
          this.transitionGraph.view.remove();
        }
      } catch (e) {
        console.error(e);
      }
      //  this.trigger("cleanNotification");
      this.render();
    },

    /**
     * Render the document view
     * @returns {*}
     */
    render: function vDocumentRender() {
      var renderPromises = [];
      var $content,
        model = this.model,
        $el = this.$el,
        currentView = this;
      var locale = this.model.get("locale");
      var documentView = this;
      var htmlBody =
        '<div class="dcpDocument__form form-horizontal">' +
        '<div class="dcpDocument__frames"></div>' +
        '<div style="display:none" class="dcpDocument__tabs">' +
        '<ul class="dcpDocument__tabs__list"></ul></div></div>';
      var $body;
      var tabPlacement = this.model.getOption("tabPlacement") || "top";
      var event = { prevent: false };
      var viewMenus = [];

      this.$el.removeClass("dcpTouch");
      if (checkTouchEvents()) {
        this.$el.addClass("dcpTouch");
      }

      this.selectedTab = this.model.getOption("openFirstTab");
      this.model.trigger("beforeRender", event);

      EventPromiseUtils.getBeforeEventPromise(event, () => {
        this.template = this.getTemplates("body").trim();
        this.partials = this.getTemplates("sections");

        // Hide parasite tooltip if any
        this.$el.find("[aria-describedby*='tooltip']").tooltip("hide");
        this.$el.empty();

        this.renderCss();
        this.publishMessages();

        if (!locale) {
          locale = "fr-FR";
        }
        $(window).off(".v" + this.model.cid);

        $(window).on("resize.v" + this.model.cid, _.bind(this.resizeForFooter, this));

        kendo.culture(locale);
        //add document base
        try {
          var renderData = this.model.toData();
          renderData.document = attributeTemplate.getTemplateModelInfo(this.model);
          this.$el.append($(Mustache.render(this.template || "", renderData, this.partials)));
          attributeTemplate
            .completeCustomContent(this.$el, this.model, null, {
              initializeContent: true
            })
            .then(() => {
              $body = this.$el
                .find(".dcpDocument__body")
                .append(htmlBody)
                .addClass("container-fluid");

              this.$el.removeClass("dcpDocument--create");
              if (this.model.get("creationFamid")) {
                this.$el.addClass("dcpDocument--create");
              }
              this.$el.addClass("dcpDocument dcpDocument--" + this.model.get("renderMode"));
              this.$el.addClass("dcpFamily--" + this.model.get("properties").get("family").name);
              this.$el.attr("data-viewid", this.model.get("viewId"));
              this.$el.attr("data-structure", this.model.get("properties").get("family").name);
              this.trigger("loading", 10);
              //add menu
              try {
                this.$el.find(".dcpDocument__menu").each(function vDocumentAddMenu() {
                  var viewMenu = new ViewDocumentMenu({
                    model: currentView.model,
                    el: this
                  });
                  renderPromises.push(viewMenu.render());
                  viewMenus.push(viewMenu);
                });
              } catch (e) {
                if (window.dcp.logger) {
                  window.dcp.logger(e);
                } else {
                  console.error(e);
                }
              }
              try {
                this.$el.find(".dcpDocument__header").each(function vDocumentAddHeader() {
                  renderPromises.push(
                    new ViewDocumentHeader({
                      model: currentView.model,
                      el: this
                    }).render()
                  );
                });
              } catch (e) {
                if (window.dcp.logger) {
                  window.dcp.logger(e);
                } else {
                  console.error(e);
                }
              }
              this.trigger("loading", 20, this.model.get("attributes").length);
              //add first level attributes

              $content = this.$el.find(".dcpDocument__frames");
              if ($body && $body.length > 0) {
                this.model.get("attributes").each(function vDocumentRenderAttribute(currentAttr) {
                  var view, viewTabLabel, viewTabContent;
                  if (!currentAttr.isDisplayable()) {
                    currentView.trigger("partRender");
                    return;
                  }
                  if (currentAttr.get("type") === "frame" && _.isEmpty(currentAttr.get("parent"))) {
                    try {
                      view = new ViewAttributeFrame({
                        model: model.get("attributes").get(currentAttr.id)
                      });
                      renderPromises.push(view.render());
                      $content.append(view.$el);
                    } catch (e) {
                      if (window.dcp.logger) {
                        window.dcp.logger(e);
                      } else {
                        console.error(e);
                      }
                    }
                  }
                  if (currentAttr.get("type") === "tab" && _.isEmpty(currentAttr.get("parent"))) {
                    try {
                      var tabModel = model.get("attributes").get(currentAttr.id);
                      var tabContent;
                      viewTabLabel = new ViewAttributeTabLabel({ model: tabModel });

                      viewTabContent = new ViewAttributeTabContent({
                        model: tabModel
                      });
                      renderPromises.push(viewTabContent.render());

                      tabContent = viewTabContent.$el;

                      $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);

                      $el.find(".dcpDocument__tabs").append(tabContent);
                      $el.find(".dcpDocument__tabs").show();
                    } catch (e) {
                      if (window.dcp.logger) {
                        window.dcp.logger(e);
                      } else {
                        console.error(e);
                      }
                    }
                  }
                  currentView.trigger("partRender");
                });

                this.kendoTabs = this.$(".dcpDocument__tabs").kendoTabStrip({
                  tabPosition: tabPlacement,
                  animation: {
                    open: {
                      duration: 100,
                      effects: "fadeIn"
                    }
                  },
                  select: function vDocumentKendoSelectTab(event) {
                    var tabId = $(event.item).data("attrid");
                    var tab = currentView.model.get("attributes").get(tabId);

                    if (tab) {
                      tab.isRealSelected = true;
                      tab.trigger("attributeBeforeTabSelect", event, tabId);
                    }
                  },
                  activate: function vDocumentShowTab(event) {
                    var tabId = $(event.item).data("attrid");
                    var scrollY = $(window).scrollTop();
                    currentView
                      .$(".dcpTab__label")
                      .removeClass("dcpLabel--active")
                      .addClass("dcpLabel--default");
                    currentView
                      .$('.dcpLabel[data-attrid="' + tabId + '"]')
                      .addClass("dcpLabel--active")
                      .removeClass("dcpLabel--default");
                    if (documentView.selectedTab !== tabId) {
                      documentView.selectedTab = tabId;
                      documentView.recordSelectedTab(tabId);
                    }
                    _.defer(function selectOneTab() {
                      if (currentView && currentView.model && currentView.model.get("attributes")) {
                        var tab = currentView.model.get("attributes").get(tabId);
                        if (tab) {
                          tab.trigger("showTab", event);
                          _.each(viewMenus, function(viewMenu) {
                            viewMenu.refresh();
                          });
                          _.defer(function() {
                            $(window).scrollTop(scrollY);
                          });
                        }
                      }
                    });
                    if (!this._dcpNotFirstactivate) {
                      _.delay(_.bind(documentView.scrollTobVisibleTab, documentView), 500);
                      this._dcpNotFirstactivate = true;
                    }
                  }
                });
                if (tabPlacement === "topProportional") {
                  var tabItems = $el.find(".dcpDocument__tabs__list li");
                  if (tabItems.length > 1) {
                    tabItems.css("width", Math.floor(100 / tabItems.length) - 0.5 + "%");
                  } else {
                    tabItems.css("width", "80%");
                  }
                }
                if (tabPlacement === "left") {
                  this.$(".dcpTab__content").css("min-height", this.$(".dcpDocument__tabs__list").height() + "px");
                  this.$(".dcpDocument__tabs").addClass("dcpDocument__tabs--left");
                }

                if (tabPlacement === "top" && this.kendoTabs) {
                  this.$(".dcpDocument__tabs").addClass("dcpDocument__tabs--fixed");
                  $(window).on("resize.v" + this.model.cid, _.debounce(_.bind(this.scrollTabList, this), 100, false));
                  _.delay(_.bind(this.scrollTabList, this), 500);
                }

                if (this.kendoTabs.length > 0 && this.kendoTabs.data("kendoTabStrip")) {
                  var selectTab = 'li[data-attrid="' + this.selectedTab + '"]';
                  if (this.selectedTab && $(selectTab).length > 0) {
                    this.kendoTabs.data("kendoTabStrip").select(selectTab);
                  } else {
                    this.kendoTabs.data("kendoTabStrip").select(0);
                  }
                }
              }
              $(window.document)
                .on("drop.v" + this.model.cid + " dragover.v" + this.model.cid, function vDocumentPreventDragDrop(e) {
                  e.preventDefault();
                })
                .on("redrawErrorMessages.v" + this.model.cid, function vDocumentRedrawErrorMessages() {
                  documentView.redrawTootips();
                });
              $(window).on(
                "resize.v" + this.model.cid,
                _.debounce(
                  function vDocumentResizeDebounce() {
                    documentView.redrawTootips();
                    documentView.scrollTobVisibleTab();
                  },
                  100,
                  false
                )
              );

              this.$el.addClass("dcpDocument--show");

              this.resizeForFooter();
              Promise.all(renderPromises).then(
                _.bind(function vDocumentRenderDone() {
                  this.trigger("renderDone");
                }, this)
              );
              this.$el.show();

              if (tabPlacement === "left") {
                this.$(".dcpTab__content").css(
                  "width",
                  "calc(100% - " + ($(".dcpDocument__tabs__list").width() + 30) + "px)"
                );
              }

              _.delay(function vDocumentEndLoading() {
                $(".dcpLoading--init").removeClass("dcpLoading--init");

                if (documentView.model.getOption("stickyTabs") !== undefined) {
                  var menuHeight = 0;
                  if (documentView.model.getOption("stickyTabs") === "auto") {
                    menuHeight = documentView.$el.find(".dcpDocument__menu").height();
                  } else {
                    menuHeight = documentView.model.getOption("stickyTabs");
                  }
                  var $tab = documentView.$el.find(".dcpDocument__tabs");
                  var $tabList = documentView.$el.find(".dcpDocument__tabs__list");
                  $tabList.css("top", menuHeight);

                  $tabList.append($tab.find("> .k-tabstrip-next"));
                  $tabList.prepend($tab.find("> .k-tabstrip-prev"));
                  $(window).on(
                    "resize.v" + documentView.model.cid,
                    _.debounce(function() {
                      var $tabList = documentView.$el.find(".dcpDocument__tabs__list");
                      $tabList.append($tab.find("> .k-tabstrip-next"));
                      $tabList.prepend($tab.find("> .k-tabstrip-prev"));
                    }, 200)
                  );

                  $tab.addClass("tab--sticky");
                }
              }, 500);
            });
        } catch (e) {
          console.error(e);
          this.model.trigger("showError", {
            title: e.message
          });
        }
      });

      return this;
    },

    selectTab: function VDocumentSelectTab(tabId) {
      if (tabId) {
        if (!Number.isInteger(tabId)) {
          tabId = this.$el.find('li.dcpTab__label[data-attrid="' + tabId + '"]');
        }
        this.kendoTabs.data("kendoTabStrip").select(tabId);
      }
    },
    drawTab: function VDocumentDrawTab(tabId) {
      if (tabId) {
        var tab = this.model.get("attributes").get(tabId);
        if (tab) {
          tab.trigger("showTab", event);
        }
      }
    },

    resizeForFooter: function vDocumentresizeForFooter() {
      var $footer = this.$el.find(".dcpDocument__footer");
      if ($footer.length > 0) {
        var footerHeight = $footer.height();
        if (footerHeight > 0) {
          $("body").css("margin-bottom", footerHeight + "px");
        }
      }
    },

    /**
     * Scroll to visible tab label
     */
    scrollTobVisibleTab: function vDocumentscrollTobVisibleTab() {
      var kendoTabStrip = this.kendoTabs ? this.kendoTabs.data("kendoTabStrip") : null;
      if (kendoTabStrip && kendoTabStrip._scrollableModeActive) {
        kendoTabStrip._scrollTabsToItem(this.kendoTabs.find("li.k-state-active"));
      }
    },

    scrollTabList: function vDocumentScrollTabList() {
      var kendoTabStrip = this.kendoTabs.data("kendoTabStrip");

      if (kendoTabStrip) {
        kendoTabStrip.resize();
      }
    },
    /**
     * Redraw messages for the error displayed
     * Change placement of tooltips
     */
    redrawTootips: function vDocumentredrawTootips() {
      var $tooltips = $(".tooltip:visible");

      $tooltips.each(function() {
        var bTooltip = $(this).data("bs.tooltip");
        if (bTooltip) {
          bTooltip.hide();
          bTooltip.show();
        }
      });
    },

    /**
     *
     * Register the current tab for the current user
     *
     * @param tabId
     */
    recordSelectedTab: function vDocumentRecordSelectedTab(tabId) {
      if (!this.model._formConfiguration) {
        if (this.model.get("initid")) {
          var tagTab = new ModelDocumentTab({
            initid: this.model.get("initid"),
            tabId: tabId
          });
          tagTab.save();
        }
      }
    },

    /**
     * Publish associated model message
     */
    publishMessages: function vDocumentPublishMessages() {
      var currentView = this;
      _.each(this.model.get("messages"), function vDocumentPublishAMessage(aMessage) {
        currentView.trigger("showMessage", {
          type: aMessage.type,
          title: aMessage.contentText,
          htmlMessage: aMessage.contentHtml
        });
      });
    },

    /**
     * Publish associated model message
     */
    showMessage: function vDocumentShowMessage(message) {
      this.trigger("showMessage", {
        type: message.type,
        title: message.contentText,
        htmlMessage: message.contentHtml
      });
    },

    /**
     * Inject associated CSS in the DOM
     *
     * Inject new CSS, remove old CSS
     */
    renderCss: function vDocumentRenderCss(/* noRemove */) {
      const customCss = this.model.get("customCSS");
      this.trigger("renderCss", customCss);
    },

    /**
     * Show the history widget
     *
     */
    showHistory: function vDocumentShowHistory(docid) {
      var scope = this;
      var $target = $('<div class="document-history"/>');
      this.historyWidget = $target
        .dcpDocumentHistory({
          documentId: docid || this.model.get("properties").get("initid"),
          window: {
            width: "80%",
            height: "80%",
            title: i18n.___("Document History", "historyUi")
          },
          labels: {
            version: i18n.___("Version", "historyUi"),
            revision: i18n.___("Rev", "historyUi"),
            state: i18n.___("State", "historyUi"),
            activity: i18n.___("Activity", "historyUi"),
            owner: i18n.___("Owner", "historyUi"),
            code: i18n.___("Code", "historyUi"),
            date: i18n.___("Date", "historyUi"),
            diff: i18n.___("Diff", "historyUi"),
            level: i18n.___("Level", "historyUi"),
            message: i18n.___("Message", "historyUi"),
            pastRevision: i18n.___("past Revision", "historyUi"),
            showDetail: i18n.___("Show details", "historyUi"),
            hideDetail: i18n.___("Hide details", "historyUi"),
            showNotice: i18n.___("Show notices", "historyUi"),
            noOneNotice: i18n.___("No one notices", "historyUi"),
            hideNotice: i18n.___("Hide notices", "historyUi"),
            filterMessages: i18n.___("Filter messages", "historyUi"),
            linkRevision: i18n.___("See revision number #", "historyUi"),
            historyTitle: i18n.___("History for {{title}}", "historyUi"),
            loading: i18n.___("Loading ...", "historyUi"),
            revisionDiffLabels: {
              title: i18n.___("Difference between two revisions", "historyDiffUi"),
              first: i18n.___("First document", "historyDiffUi"),
              second: i18n.___("Second document", "historyDiffUi"),
              attributeId: i18n.___("Attribute id", "historyDiffUi"),
              attributeLabel: i18n.___("Attribute label", "historyDiffUi"),
              documentHeader: i18n.___(
                "{{title}}  (Revision : {{revision}}). <br/>Created on <em>{{mdate}}</em>",
                "historyDiffUi"
              ),
              filterMessages: i18n.___("Filter data", "historyDiffUi"),
              showOnlyDiff: i18n.___("Show only differences", "historyDiffUi"),
              showAll: i18n.___("Show all", "historyDiffUi")
            }
          }
        })
        .data("dcpDocumentHistory");

      this.historyWidget.open();
      this.historyWidget.element.on("viewRevision", function vDocumentViewRevision(event, data) {
        scope.model.fetchDocument({
          initid: data.initid,
          revision: data.revision
        });
      });
    },

    /**
     * Lock document
     *
     */
    lockDocument: function vDocumentLockDocument() {
      this.model.lockDocument();
    },
    /**
     * Lock document
     *
     */
    unlockDocument: function vDocumentUnLockDocument() {
      this.model.unlockDocument();
    },
    /**
     * Show the transition view
     *
     */
    showtransition: function vDocumentShowtransition(transition, nextState) {
      this.model.trigger("showTransition", nextState, transition);
    },

    /**
     * Show the transition view
     *
     */
    showTransitionGraph: function vDocumentShowtransitionGraph() {
      var documentView = this;
      var transitionGraph = {};
      var $target = $('<div class="dcpTransitionGraph"/>');
      //Init transition model
      transitionGraph.model = new ModelTransitionGraph({
        documentId: this.model.id,
        state: this.model.get("properties").get("state")
      });

      transitionGraph.model.fetch({
        success: function vDocumentTransitionSuccess() {
          //Init transition view
          transitionGraph.view = new ViewTransitionGraph({
            model: transitionGraph.model,
            el: $target
          });
          transitionGraph.view.render();
          transitionGraph.view.$el.on("viewTransition", function vDocumentTransitionView(event, nextState) {
            transitionGraph.view.remove();
            documentView.model.trigger("showTransition", nextState);
          });
        }
      });

      this.transitionGraph = transitionGraph;
    },
    /**
     * Show the properties widget
     *
     */
    showProperties: function vDocumentShowProperties(docid) {
      var scope = this;
      var $target = $('<div class="document-properties"/>');

      this.propertiesWidget = $target
        .dcpDocumentProperties({
          documentId: docid || this.model.get("properties").get("initid"),
          window: {
            width: "500px",
            height: "80%",
            title: i18n.___("Document properties", "propertyUi")
          },
          labels: {
            identifier: i18n.___("Identifier", "propertyUi"),
            title: i18n.___("Title", "propertyUi"),
            logicalName: i18n.___("Logical name", "propertyUi"),
            revision: i18n.___("Revision number", "propertyUi"),
            version: i18n.___("Version", "propertyUi"),
            family: i18n.___("Family", "propertyUi"),
            lockedBy: i18n.___("Locked by", "propertyUi"),
            createdBy: i18n.___("Created by", "propertyUi"),
            notLocked: i18n.___("Not locked", "propertyUi"),
            confidential: i18n.___("Confidential", "propertyUi"),
            notConfidential: i18n.___("Not confidential", "propertyUi"),
            creationDate: i18n.___("Creation date", "propertyUi"),
            lastModificationDate: i18n.___("Last modification date", "propertyUi"),
            profil: i18n.___("Profil", "propertyUi"),
            profilReference: i18n.___("Profil reference", "propertyUi"),
            viewController: i18n.___("View controller", "propertyUi"),
            property: i18n.___("Property", "propertyUi"),
            propertiesTitle: i18n.___("Properties for {{title}}", "propertyUi"),
            propertyValue: i18n.___("Value", "propertyUi"),
            workflow: i18n.___("Workflow", "propertyUi"),
            activity: i18n.___("Activity", "propertyUi")
          }
        })
        .data("dcpDocumentProperties");

      this.propertiesWidget.open();
      this.propertiesWidget.element.on("viewDocument", function vDocumentViewDocument(event, data) {
        scope.model.fetchDocument({ initid: data });
      });
    },

    /**
     * Delete the current document
     *
     */
    deleteDocument: function dvDocumentDocumentDelete() {
      this.model.deleteDocument();
    },

    /**
     * Display the loading widget
     */
    displayLoading: function vDocumentDisplayLoading(options) {
      var text = i18n.___("Loading", "ddui"),
        avance = 50;
      options = options || {};
      if (this.$el.find(".dcpDocument--disabled") === 0) {
        this.$el.append('<div class="dcpDocument--disabled"/>');
      }
      if (options.isSaving) {
        text = i18n.___("Recording", "ddui");
        avance = 70;
      }
      if (options.text) {
        text = options.text;
      }
      this.trigger("cleanNotification");
      this.trigger("loaderShow", text, avance);
    },

    /**
     * Hide the loading widget
     */
    hideLoading: function vDocumentHideLoading() {
      this.trigger("loaderHide");
    },

    /**
     * Show the view
     *
     * Hide the loader, show the view
     */
    showView: function vDocumentShowView() {
      this.$el.hide();
      this.$el.find(".dcpDocument--disabled").remove();
      this.trigger("loaderHide");
      this.$el.show();
      this.redrawTootips();
    },

    /**
     * Switch the view
     *
     * @param viewId
     */
    closeDocument: function vDocumentCloseDocument(viewId) {
      if (!viewId) {
        if (this.model.get("renderMode") === "edit") {
          viewId = "!defaultEdition";
        } else {
          viewId = "!defaultConsultation";
        }
      }

      this.model.unautolock().then(() => {
        this.loadDocument({
          initid: this.model.get("initid"),
          viewId: viewId
        });
      });
    },

    /**
     * Save the current document
     */
    saveDocument: function vDocumentSaveDocument() {
      this.trigger("cleanNotification");
      var currentView = this,
        saveDocument = this.model.saveDocument();
      //Use promise and display success when done
      if (saveDocument && saveDocument.then) {
        saveDocument.then(function vDocumentSaveDisplaySuccess() {
          currentView.trigger("showSuccess", {
            htmlMessage: Mustache.render(i18n.___("<b>{{title}}</b> is recorded", "ddui"), {
              title: currentView.model.get("properties").get("title")
            })
          });
        });
      }
    },

    /**
     * Save and close the current document
     */
    saveAndCloseDocument: function vDocumentSaveAndCloseDocument(viewId) {
      this.trigger("cleanNotification");
      var currentView = this,
        saveDocument = this.model.saveDocument();
      if (saveDocument && saveDocument.then) {
        saveDocument.then(function vDocumentSaveAndCloseSuccess() {
          var initid = currentView.model.get("initid");

          currentView.model.fetchDocument({
            initid: initid,
            viewId: viewId || "!defaultConsultation"
          });
        });
      }
    },

    /**
     * Create the current document
     */
    createDocument: function vDocumentCreateDocument() {
      var currentView = this,
        saveDocument = this.model.saveDocument();
      if (saveDocument && saveDocument.then) {
        saveDocument.then(function vDocumentCreateDisplaySuccess() {
          currentView.trigger("showSuccess", {
            title: Mustache.render(i18n.___("{{title}} Created", "ddui"), {
              title: currentView.model.getModelProperties("title").title || "Smart Element"
            })
          });
        });
      }
    },

    /**
     * Create the current document
     */
    createAndCloseDocument: function vDocumentCreateDocument(viewId) {
      var currentView = this,
        saveDocument = this.model.saveDocument();
      if (saveDocument && saveDocument.then) {
        saveDocument.then(function vDocumentCreateAndCloseSuccess() {
          var initid = currentView.model.get("initid");

          currentView.model.fetchDocument({
            initid: initid,
            viewId: viewId || "!defaultConsultation"
          });
        });
      }
    },

    displayCloseDocument: function vDocumentdisplayCloseDocument(success, error) {
      var confirmWindow;

      if (this.model.hasAttributesChanged()) {
        confirmWindow = $("body").dcpConfirm({
          title: i18n.___("Confirm close document", "ddui"),
          width: "45rem",
          height: "12rem",
          maxWidth: $(window).width(),
          messages: {
            okMessage: i18n.___("Abort modification", "ddui"),
            cancelMessage: i18n.___("Stay on the form", "ddui"),
            htmlMessage: i18n.___("The form has been modified without saving", "ddui"),
            textMessage: ""
          },
          confirm: function wMenuConfirm() {
            $("body").dcpConfirm("destroy");
            success();
          },
          cancel: function wLoadCancel() {
            $("body").dcpConfirm("destroy");
            error({
              errorMessage: {
                code: "USERCANCEL",
                contentText: i18n.___("User has cancelled the action.", "ddui")
              },
              eventPrevented: true
            });
          },
          templateData: { templates: this.model.get("templates") }
        });
        confirmWindow.data("dcpWindow").open();
      } else {
        success();
      }
    },

    /**
     * load another document document  : confirm if modified
     * options : {initid, viewId, revision}
     * callbacks : {success, error}
     */
    loadDocument: function vDocumentLoadDocument(options, callbacks) {
      callbacks = callbacks || {};

      this.model
        .fetchDocument({
          initid: options.initid,
          viewId: options.viewId,
          revision: options.revision
        })
        .then(callbacks.success, callbacks.error);
    },

    /**
     * Restore the deleted document
     *
     * @returns {exports}
     */
    restoreDocument: function vDocumentRestoreDocument() {
      this.model.restoreDocument();
    },

    propagateActionClick: function vDocumentPropagateActionClick(event) {
      var $target = $(event.currentTarget),
        action,
        options,
        eventOptions,
        internalEvent = {
          prevent: false
        };

      event.preventDefault();
      if (event.stopPropagation) {
        event.stopPropagation();
      }

      action = $target.data("action") || $target.attr("href");
      options = action.substring(8).split(":");
      eventOptions = {
        target: event.target,
        eventId: options.shift(),
        options: options
      };

      this.model.trigger("internalLinkSelected", internalEvent, eventOptions);

      return EventPromiseUtils.getBeforeEventPromise(
        internalEvent,
        () => {
          return this.doStandardAction(internalEvent, eventOptions);
        },
        () => {
          return this;
        }
      );
    },

    handleHashClick: function vDocumenthandleHashClick(event) {
      var $target = $(event.currentTarget),
        href = $target.attr("href");

      if (!href || !href.substring || href.substring(0, 7) === "#action") {
        return;
      }

      event.preventDefault();
      if (event.stopPropagation) {
        event.stopPropagation();
      }

      window.location.hash = href;
    },

    /**
     * Propagate menu event
     *test
     * @param event
     * @param options
     * @returns {*}
     */
    doStandardAction: function vDocumentdoStandardAction(event, options) {
      var eventArgs = options.options;

      if (options.eventId === "document.save") {
        return this.saveDocument();
      }
      if (options.eventId === "document.saveAndClose") {
        return this.saveAndCloseDocument(eventArgs[0]);
      }
      if (options.eventId === "document.history") {
        return this.showHistory(eventArgs[0]);
      }
      if (options.eventId === "document.transition") {
        return this.showtransition(eventArgs[0], eventArgs[1]);
      }
      if (options.eventId === "document.transitionGraph") {
        return this.showTransitionGraph();
      }
      if (options.eventId === "document.properties") {
        return this.showProperties(eventArgs[0]);
      }
      if (options.eventId === "document.delete") {
        return this.deleteDocument();
      }
      if (options.eventId === "document.close") {
        return this.closeDocument(eventArgs[0]);
      }
      if (options.eventId === "document.edit") {
        return this.closeDocument("!defaultEdition");
      }
      if (options.eventId === "document.create") {
        return this.createDocument();
      }
      if (options.eventId === "document.createAndClose") {
        return this.createAndCloseDocument(eventArgs[0]);
      }
      if (options.eventId === "document.load") {
        return this.loadDocument({
          initid: eventArgs[0],
          viewId: eventArgs[1],
          revision: eventArgs[2]
        });
      }
      if (options.eventId === "document.lock") {
        return this.lockDocument();
      }
      if (options.eventId === "document.unlock") {
        return this.unlockDocument();
      }
      if (options.eventId === "document.restore") {
        return this.restoreDocument();
      }
    },

    displayNetworkError: function vDocument_displayNetworkError() {
      this.$el.hide();
      const wrapper = this.$el.closest(".smart-element-wrapper");
      wrapper.find(".dcpStaticErrorMessage").removeAttr("hidden");
    },

    /**
     * Get the template for the current view
     *
     * @param key
     * @returns {*}
     */
    getTemplates: function vDocumentGetTemplates(key) {
      var templates = {};
      if (this.model && this.model.get("templates")) {
        templates = this.model.get("templates");
      }
      if (!_.isUndefined(templates[key])) {
        return templates[key];
      }
      // Get from a gobal element (for unittest)
      if (window.dcp && window.dcp.templates && window.dcp.templates[key]) {
        return window.dcp.templates[key];
      }
      throw new Error("Unknown template  " + key);
    },

    /**
     * Destroy the associated widget and suppress event listener before remov the dom
     *
     * @returns {*}
     */
    remove: function vDocumentRemove() {
      try {
        if (this.kendoTabs && this.kendoTabs.data("kendoTabStrip")) {
          this.kendoTabs.data("kendoTabStrip").destroy();
        }
      } catch (e) {
        if (window.dcp.logger) {
          window.dcp.logger(e);
        } else {
          console.error(e);
        }
      }
      $(window).off(".v" + this.model.cid);
      $(window.document).off(".v" + this.model.cid);

      return Backbone.View.prototype.remove.call(this);
    }
  });
});
