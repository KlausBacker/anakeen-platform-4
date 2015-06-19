/*global define, console*/

define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'dcpDocument/models/mDocumentTab',
    'dcpDocument/views/document/menu/vMenu',
    'dcpDocument/views/document/header/vHeader',
    'dcpDocument/views/attributes/frame/vFrame',
    'dcpDocument/views/attributes/tab/vTabLabel',
    'dcpDocument/views/attributes/tab/vTabContent',
    'dcpDocument/views/document/attributeTemplate',
    'dcpDocument/models/mTransitionGraph',
    'dcpDocument/views/workflow/vTransitionGraph',
    'kendo/kendo.core',
    'dcpDocument/i18n',
    'kendo/kendo.tabstrip',
    'dcpDocument/widgets/history/wHistory',
    'dcpDocument/widgets/properties/wProperties'
], function (_, $, Backbone, Mustache, ModelDocumentTab, ViewDocumentMenu, ViewDocumentHeader,
             ViewAttributeFrame, ViewAttributeTabLabel, ViewAttributeTabContent,
             attributeTemplate, ModelTransitionGraph, ViewTransitionGraph, kendo, i18n)
{
    'use strict';

    return Backbone.View.extend({

        className: "dcpDocument container-fluid",

        /**
         * Init event
         */
        initialize: function vDocumentInitialize()
        {
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'displayLoading', this.displayLoading);
            this.listenTo(this.model, 'reload', this.cleanAndRender);
            this.listenTo(this.model, 'invalid', this.showView);
            this.listenTo(this.model, 'error', this.showView);
        },

        /**
         * Clean the associated view and re-render it
         */
        cleanAndRender: function vDocumentCleanAndRender()
        {
            this.trigger("loaderShow", i18n.___("Rendering", "ddui"), 70);
            this.$el[0].className = this.$el[0].className.replace(/\bdcpFamily.*\b/g, '');
            this.$el.removeClass("dcpDocument--view").removeClass("dcpDocument--edit");
            try {
                if (this.historyWidget) {
                    this.historyWidget.destroy();
                }
                if (this.propertiesWidget) {
                    this.propertiesWidget.destroy();
                }
                if (this.transitionGraph && this.transitionGraph.view) {
                    this.transitionGraph.view.remove();
                }
            } catch (e) {
                console.error(e);
            }
            this.trigger("cleanNotification");
            this.render();
        },

        /**
         * Render the document view
         * @returns {*}
         */
        render: function vDocumentRender()
        {
            console.time("render document view");
            var $content, model = this.model, $el = this.$el, currentView = this;
            var locale = this.model.get('locale');
            var documentView = this;
            var htmlBody = '<div class="dcpDocument__form form-horizontal">' +
                '<div class="dcpDocument__frames"></div>' +
                '<div style="display:none" class="dcpDocument__tabs">' +
                '<ul class="dcpDocument__tabs__list"></ul></div></div>';
            var $body;
            var tabPlacement = "topFix";

            this.template = this.getTemplates("body").trim();
            this.partials = this.getTemplates("sections");

            this.$el.empty();

            this.renderCss();
            this.renderJS();
            this.publishMessages();

            this.updateTitle();
            this.updateIcon();

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
                this.$el.append($(Mustache.render(this.template, renderData, this.partials)));
                attributeTemplate.completeCustomContent(this.$el, this.model, null, {initializeContent: true});

                $body = this.$el.find(".dcpDocument__body").append(htmlBody).addClass("container-fluid");
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            this.$el.addClass("dcpDocument dcpDocument--" + this.model.get("renderMode"));
            this.$el.addClass("dcpDocument dcpFamily--" + this.model.get("properties").get("family").name);
            this.trigger("loading", 10);
            //add menu
            try {
                var viewMenu = new ViewDocumentMenu({
                    model: this.model,
                    el: this.$el.find(".dcpDocument__menu:first")[0]
                }).render();

                this.listenTo(viewMenu, 'menuselected', this.actionDocument);
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            try {
                new ViewDocumentHeader({
                    model: this.model,
                    el: this.$el.find(".dcpDocument__header:first")[0]
                }).render();
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            this.trigger("loading", 20, this.model.get("attributes").length);
            //add first level attributes
            console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");
            if ($body.length > 0) {
                this.model.get("attributes").each(function vDocumentRenderAttribute(currentAttr)
                {
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
                            $content.append(view.render().$el);

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
                            viewTabLabel = new ViewAttributeTabLabel({model: tabModel});

                            viewTabContent = new ViewAttributeTabContent({
                                model: tabModel
                            });
                            tabContent = viewTabContent.render().$el;
                            if (tabModel.getOption("openFirst")) {
                                currentView.selectedTab = currentAttr.id;
                            }
                            $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);


                            if (tabModel.getOption("tabPlacement")) {
                                tabPlacement = tabModel.getOption("tabPlacement");
                            }

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
                    show: function vDocumentShowTab(event)
                    {
                        var tabId = $(event.item).data("attrid");
                        currentView.$(".dcpTab__label").removeClass("dcpLabel--active").addClass("dcpLabel--default");
                        currentView.$('.dcpLabel[data-attrid="' + tabId + '"]').addClass("dcpLabel--active").removeClass("dcpLabel--default");
                        if (documentView.selectedTab !== tabId) {
                            documentView.selectedTab = tabId;
                            documentView.recordSelectedTab(tabId);
                        }
                        _.defer(function selectOneTab()
                        {
                            if (currentView && currentView.model && currentView.model.get("attributes") ) {
                                var tab = currentView.model.get("attributes").get(tabId);
                                if (tab) {
                                    tab.trigger("showTab");
                                    viewMenu.refresh();
                                }
                            }
                        });
                    }
                });
                if (tabPlacement === "topProportional") {
                    var tabItems = $el.find(".dcpDocument__tabs__list li");
                    if (tabItems.length > 1) {
                        tabItems.css("width", Math.floor(100 / tabItems.length) - 0.5 + '%');
                    } else {
                        tabItems.css("width", "80%");
                    }
                }
                if (tabPlacement === "left") {
                    this.$(".dcpTab__content").css("min-height", this.$(".dcpDocument__tabs__list").height() + "px");
                    this.$(".dcpDocument__tabs").addClass("dcpDocument__tabs--left");
                }

                if (tabPlacement === "topFix" && this.kendoTabs) {
                    this.$(".dcpDocument__tabs").addClass("dcpDocument__tabs--fixed");

                    // Use an overflow to hide resize effects, it is delete at the end of tab resize
                    var tabList = this.$(".dcpDocument__tabs .dcpDocument__tabs__list");
                    tabList.css("overflow", "hidden").css("max-height", "2.7em");
                    $(window).on("resize.v" + this.model.cid, function ()
                    {
                        tabList.css("overflow", "hidden").css("max-height", "2.7em");
                    });
                    $(window).on("resize.v" + this.model.cid, _.debounce(_.bind(this.responsiveTabMenu, this), 100, false));

                }

                if (this.kendoTabs.length > 0 && this.kendoTabs.data("kendoTabStrip")) {
                    var selectTab = 'li[data-attrid=' + this.selectedTab + ']';
                    if (this.selectedTab && $(selectTab).length > 0) {
                        this.kendoTabs.data("kendoTabStrip").select(selectTab);
                    } else {
                        this.kendoTabs.data("kendoTabStrip").select(0);
                    }
                }
            }
            $(window.document).on("drop.v" + this.model.cid + " dragover." + this.model.cid, function vDocumentPreventDragDrop(e)
            {
                e.preventDefault();
            }).on("redrawErrorMessages.v" + this.model.cid, function vDocumentRedrawErrorMessages()
            {
                documentView.model.redrawErrorMessages();
            });
            $(window).on("resize.v" + this.model.cid, _.debounce(function ()
            {
                documentView.model.redrawErrorMessages();
            }, 100, false));


            this.$el.addClass("dcpDocument--show");

            this.resizeForFooter();
            console.timeEnd("render document view");
            this.trigger("renderDone");
            this.$el.show();
            if (tabPlacement === "topFix" && this.kendoTabs) {
                _.defer(_.bind(this.responsiveTabMenu, this)); // need to call here to have good dimensions
            }
            if (tabPlacement === "left") {
                this.$(".dcpTab__content").css("width", "calc(100% - " + ($(".dcpDocument__tabs__list").width() + 30) + "px)");
            }

            _.delay(function ()
            {
                $(".dcpLoading--init").removeClass("dcpLoading--init");
            }, 500);

            return this;
        },


        resizeForFooter: function vDocumentresizeForFooter()
        {
            var $footer = this.$el.find(".dcpDocument__footer");
            if ($footer.length > 0) {
                var footerHeight = $footer.height();
                if (footerHeight > 0) {
                    $("body").css("margin-bottom", footerHeight + "px");
                }
            }
        },

        /**
         * Add menu if needed in topFix placement tab
         */
        responsiveTabMenu: function vDocumentTabFixMenu()
        {
            var $tabLabel = this.$(".dcpDocument__tabs__list li");

            var documentWidth = this.$(".dcpDocument__tabs").width() - 12;
            var currentWidth = 0;
            var hiddens = [];
            var lastShow = null;
            var $dropSelect;
            var $dropTopSelect;
            var $kendoTabs = this.kendoTabs.data("kendoTabStrip");
            var liIndex = 0;
            var $tabs = this.$(".dcpDocument__tabs");
            var $selectedTabId = this.selectedTab;
            var currentHeight;
            var hiddenSelected = false;
            var dataSource = null;
            var iOS = /(iPad|iPhone|iPod)/g.test(navigator.userAgent);


            $tabs.find(".dcpDocument__tabs__list").css("overflow", "hidden").css("max-height", "2.7em");
            // Restore initial tabs
            $tabLabel.show();

            $tabLabel.each(function vDocumentHideTabWidth()
            {
                currentWidth += $(this).outerWidth();
                if (currentWidth > documentWidth) {
                    $(this).hide();
                    if (hiddens.length === 0) {
                        hiddens.push({
                            tooltipLabel: $(lastShow).data("tooltipLabel"),
                            label: $(lastShow).find(".k-link").text(),
                            id: $(lastShow).data("attrid"),
                            index: liIndex - 1
                        });
                    }
                    hiddens.push({
                        tooltipLabel: $(this).data("tooltipLabel"),
                        label: $(this).find(".k-link").text(),
                        id: $(this).data("attrid"),
                        index: liIndex
                    });
                } else {
                    $kendoTabs.enable($(this));
                    lastShow = this;
                }

                liIndex++;
            });

            /**
             * Need to recompute container width
             */
            $(".dcpLabel__select-hide:visible").each(function ()
            {
                var $container = $(this).closest(".k-list-container");
                var x = $container.offset().left;
                var maxWidth = ($('body').width() - x - 20) + "px";

                $container.css("max-width", maxWidth);
                $container.closest(".k-animation-container").css("max-width", maxWidth);
                $(this).css("max-width", "");
            });

            if ($tabs.data("hiddenTabsLength") === hiddens.length) {
                // Optimization if no new tabs to hide
                if (hiddens.length > 0) {
                    $kendoTabs.disable($(lastShow));
                }

                $tabs.find(".dcpDocument__tabs__list").css("overflow", "").css("max-height", "");
                return;
            }

            // Delete fixed height
            $tabLabel.css("height", '');
            // Record hidden count to optimization
            $tabs.data("hiddenTabsLength", hiddens.length);
            $tabLabel.find(".k-link").show(); // Restore original link (tab label)

            $dropTopSelect = $tabs.find(".dcpTab__label__select.k-combobox").hide();
            $tabs.find("input.dcpTab__label__select[data-role=combobox]").each(function ()
            {
                $(this).data("kendoComboBox").close();
            });


            $tabs.find(".dcpLabel--select").removeClass("dcpLabel--select k-state-active");
            $tabs.find(".dcpLabel[data-attrid=" + $selectedTabId + "]").addClass("k-state-active");


            if (hiddens.length > 0) {

                // Need to disable tab to use own events managing
                $kendoTabs.disable($(lastShow));
                // Need to force same heigth as other tabs
                currentHeight = $(lastShow).height();
                // Hide original link
                $(lastShow).find(".k-link").hide();
                // Replace it to a dropdown selector
                hiddenSelected = _.some(hiddens, function (item)
                {
                    return (item.id === $selectedTabId);
                });

                if (hiddenSelected) {
                    $(lastShow).addClass("k-state-active");
                } else {
                    if ($(lastShow).data("attrid") !== $selectedTabId) {
                        $(lastShow).removeClass("k-state-active");
                    }
                }

                $(lastShow).addClass("dcpLabel--select");
                $(lastShow).height(currentHeight - 5);
                if ($dropTopSelect.length === 0) {
                    $dropSelect = $('<input class="dcpTab__label__select" />');
                    $(lastShow).append($dropSelect);
                    $dropSelect.kendoComboBox({
                        value: hiddenSelected ? $selectedTabId : hiddens[0].id,
                        dataSource: hiddens,
                        dataTextField: "label",
                        dataValueField: "id",
                        dataBound: function (event)
                        {
                            var myTab = $(this.element).closest("li");
                            var liItem = $tabs.find("li[data-attrid=" + this.value() + "]");
                            myTab.data("tooltipLabelSelect", liItem.data("tooltipLabel"));
                        },
                        select: function (event)
                        {
                            var dataItem = this.dataSource.at(event.item.index());
                            var liItem = $tabs.find("li[data-attrid=" + dataItem.id + "]");
                            var myTab = $(this.element).closest("li");

                            myTab.data("tooltipLabelSelect", dataItem.tooltipLabel);
                            // Need to reset class and enable to really trigger show events
                            myTab.removeClass("k-state-active");
                            $kendoTabs.enable(myTab);
                            $kendoTabs.select(liItem);
                            $kendoTabs.disable(myTab);
                            myTab.addClass("k-state-active");
                            myTab.find(".k-input").blur(); // Because input is read only
                        },
                        open: function ()
                        {
                            // Need to compute width of container to see elements
                            // Set max-width to not be out of body
                            var $ul = $(this.ul);
                            var x = $(this.element).closest("li").offset().left;
                            var bodyWidth = $('body').width();
                            $ul.css("max-width", (bodyWidth - x - 20) + "px");
                            _.defer(function ()
                            {
                                $ul.closest(".k-animation-container").addClass("menu__select_container");
                            });
                        },

                        template: function (event)
                        {
                            if (event.tooltipLabel) {
                                return Mustache.render('<span class="dcpLabel__select--tooltip" data-tooltipLabel="{{tooltipLabel}}">{{label}}</span>', event);
                            }
                            return event.label;
                        }
                    });


                    $dropSelect.data("kendoComboBox").ul.addClass("dcpLabel__select-hide");
                    // The container width is computed by open event
                    $dropSelect.data("kendoComboBox").list.width("auto");
                    // No use input selector
                    $(lastShow).find("input").attr("aria-readonly", "true").prop("readonly", true);
                    $(lastShow).find(".k-select").prepend($("<span/>").addClass("dcpLabel__count"));

                    $dropSelect.data("kendoComboBox").ul.tooltip({
                        selector: "li.k-item ",
                        placement: "left",
                        container: "body",
                        html: true,
                        title: function ()
                        {
                            return $(this).find(".dcpLabel__select--tooltip").attr("data-tooltipLabel");
                        }
                    });

                } else {
                    // Reuse dropDown created previously
                    $dropTopSelect.show();
                    $dropSelect = $tabs.find("input.dcpTab__label__select[data-role=combobox]");
                    $(lastShow).append($dropTopSelect); // Move to new lastShow
                    $dropSelect.data("kendoComboBox").value(hiddenSelected ? $selectedTabId : hiddens[0].id);
                    dataSource = new kendo.data.DataSource({
                        data: hiddens
                    });
                    $dropSelect.data("kendoComboBox").setDataSource(dataSource);
                }


                // Add count in select button
                $(lastShow).find(".dcpLabel__count").text(hiddens.length);

                if (!$tabs.data("selectFixOn")) {
                    // Add callback only one time
                    $tabs.on("click", ".dcpLabel--select .k-dropdown-wrap .k-input", function ()
                    {
                        var selectedTab = $kendoTabs.select().data("attrid");
                        var selectedItem = $tabs.data("selectFixOn").data("kendoComboBox").value();
                        var liItem = $tabs.find("li[data-attrid=" + selectedItem + "]");
                        var myTab = $(this).closest("li");

                        if (selectedItem !== selectedTab) {
                            myTab.removeClass("k-state-active");
                            $kendoTabs.enable(myTab);
                            $kendoTabs.select(liItem);
                            $kendoTabs.disable(myTab);
                            myTab.addClass("k-state-active");
                        }
                    });

                    $tabs.on("focus", ".dcpLabel--select .k-dropdown-wrap .k-input", function ()
                    {
                        $(this).blur();
                    });
                }

                // Memorize dropdown to reuse it in callback and to listen only one
                $tabs.data("selectFixOn", $dropSelect);
            }
            if ('ontouchstart' in document.documentElement && iOS) {
                $("body").off('show.bs.tooltip').on('show.bs.tooltip', "[data-original-title]", function (e)
                {
                    // prevent ios double tap
                    var $tooltip = $(this);
                    if ('ontouchstart' in document.documentElement) {
                        if (!$tooltip.data("showios")) {
                            e.preventDefault();
                            $tooltip.data("showios", true);
                            _.delay(function ()
                            {
                                $tooltip.tooltip("show");
                                $tooltip.data("showios", false);
                                _.delay(function ()
                                {
                                    $tooltip.tooltip("hide");
                                }, 2000);
                            }, 500);
                        }
                    }
                });
            }


            $tabs.find(".dcpDocument__tabs__list").css("overflow", "").css("max-height", "");

        },

        /**
         *
         * Register the current tab for the current user
         *
         * @param tabId
         */
        recordSelectedTab: function vDocumentRecordSelectedTab(tabId)
        {
            if (this.model.get("initid")) {
                var tagTab = new ModelDocumentTab({"initid": this.model.get("initid"), "tabId": tabId});
                tagTab.save();
            }
        },

        /**
         * Publish associated model message
         */
        publishMessages: function vDocumentPublishMessages()
        {
            var currentView = this;
            _.each(this.model.get("messages"), function vDocumentPublishAMessage(aMessage)
            {
                currentView.trigger("showMessage", {
                    type: aMessage.type,
                    title: aMessage.contentText,
                    htmlMessage: aMessage.contentHtml
                });
            });
        },

        /**
         * Inject associated CSS in the DOM
         *
         * Inject new CSS, remove old CSS
         */
        renderCss: function vDocumentRenderCss()
        {
            // add custom css style
            var $head = $("head"),
                cssLinkTemplate = _.template('<link rel="stylesheet" type="text/css" ' +
                'href="<%= path %>" data-id="<%= key %>" data-view="true">'),
                customCss = this.model.get("customCSS");
            //Remove old CSS


            _.each($("link[data-view=true]"), function vDocumentRemoveOldCSS(currentLink)
            {
                var findCss = function (currentCss)
                {
                    return $(currentLink).data("id") === currentCss.key;
                };
                if (_.find(customCss, findCss) === undefined) {
                    $(currentLink).remove();
                }
            });
            // Inject new CSS
            _.each(customCss, function vDocumentInjectNewCSS(cssItem)
            {
                var $existsLink = $('link[rel=stylesheet][data-id=' + cssItem.key + ']');
                if ($existsLink.length === 0) {
                    $head.append(cssLinkTemplate(cssItem));
                }
            });
        },
        /**
         * Inject new JS script in the dom
         * Use the facebook style injection
         */
        renderJS: function vDocumentRenderJS()
        {
            var insertJs = function vDocumentInsertJs(path, key)
            {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.setAttribute("data-id", key);
                script.async = true;
                script.src = path;
                document.getElementsByTagName('head')[0].appendChild(script);
            };

            _.each(this.model.get("customJS"), function vDocumentHandleJS(jsItem)
            {
                var $existsLink = $('script[data-id=' + jsItem.key + ']');
                if ($existsLink.length === 0) {
                    insertJs(jsItem.path, jsItem.key);
                }
            });
        },

        /**
         * Show the history widget
         *
         */
        showHistory: function vDocumentShowHistory()
        {
            var scope = this;
            var $target = $('<div class="document-history"/>');
            this.historyWidget = $target.dcpDocumentHistory({
                documentId: this.model.get("properties").get("initid"),
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
                    hideNotice: i18n.___("Hide notices", "historyUi"),
                    filterMessages: i18n.___("Filter messages", "historyUi"),
                    linkRevision: i18n.___("See revision number #", "historyUi"),
                    loading: i18n.___("Loading ...", "historyUi"),
                    revisionDiffLabels: {
                        "title": i18n.___("Difference between two revisions", "historyDiffUi"),
                        "first": i18n.___("First document", "historyDiffUi"),
                        "second": i18n.___("Second document", "historyDiffUi"),
                        "attributeId": i18n.___("Attribute id", "historyDiffUi"),
                        "attributeLabel": i18n.___("Attribute label", "historyDiffUi"),
                        "documentHeader": i18n.___("{{title}}  (Revision : {{revision}}). <br/>Created on <em>{{revdate}}</em>", "historyDiffUi"),
                        "filterMessages": i18n.___("Filter data", "historyDiffUi"),
                        "showOnlyDiff": i18n.___("Show only differences", "historyDiffUi"),
                        "showAll": i18n.___("Show all", "historyDiffUi")
                    }
                }
            }).data("dcpDocumentHistory");

            this.historyWidget.open();
            this.historyWidget.element.on("viewRevision", function vDocumentViewRevision(event, data)
            {
                scope.model.clear();
                scope.model.set({initid: data.initid, revision: data.revision});
                scope.model.fetch();
            });
        },

        /**
         * Lock document
         *
         */
        lockDocument: function vDocumentLockDocument()
        {
            this.model.lockDocument();
        },
        /**
         * Lock document
         *
         */
        unlockDocument: function vDocumentUnLockDocument()
        {
            this.model.unlockDocument();
        },
        /**
         * Show the transition view
         *
         */
        showtransition: function vDocumentShowtransition(transition, nextState)
        {
            this.model.trigger("showTransition", nextState, transition);
        },

        /**
         * Show the transition view
         *
         */
        showTransitionGraph: function vDocumentShowtransitionGraph(transition, nextState)
        {
            var documentView = this;
            var transitionGraph = {};
            var $target = $('<div class="dcpTransitionGraph"/>');
            //Init transition model
            transitionGraph.model = new ModelTransitionGraph({
                documentId: this.model.id,
                state: this.model.get("properties").get("state")
            });


            transitionGraph.model.fetch({
                success: function ()
                {
                    //Init transition view
                    transitionGraph.view = new ViewTransitionGraph({
                        model: transitionGraph.model,
                        el: $target
                    });
                    transitionGraph.view.render();
                    transitionGraph.view.$el.on("viewTransition", function (event, nextState)
                    {
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
        showProperties: function vDocumentShowProperties()
        {
            var scope = this;
            var $target = $('<div class="document-properties"/>');

            this.propertiesWidget = $target.dcpDocumentProperties({
                documentId: this.model.get("properties").get("initid"),
                window: {
                    width: "auto",
                    height: "auto",
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
                    lastAccessDate: i18n.___("Last access date", "propertyUi"),
                    profil: i18n.___("Profil", "propertyUi"),
                    profilReference: i18n.___("Profil reference", "propertyUi"),
                    viewController: i18n.___("View controller", "propertyUi"),
                    property: i18n.___("Property", "propertyUi"),
                    propertyValue: i18n.___("Value", "propertyUi"),
                    workflow: i18n.___("Workflow", "propertyUi"),
                    activity: i18n.___("Activity", "propertyUi")
                }
            }).data("dcpDocumentProperties");

            this.propertiesWidget.open();
            this.propertiesWidget.element.on("viewDocument", function vDocumentViewDocument(event, data)
            {
                scope.model.clear();
                scope.model.set("initid", data);
                scope.model.fetch();
            });
        },

        /**
         * Update the title of the current page
         */
        updateTitle: function vDocumentUpdateTitle()
        {
            var title = this.model.get("properties").get("title");

            if (!_.isEmpty(title)) {
                document.title = title;
            }
        },

        /**
         * Update the icon of the current page
         */
        updateIcon: function vDocumentUpdateIcon()
        {
            $("link[rel='shortcut icon']").attr("href", this.model.get("properties").get("icon"));
        },

        /**
         * Delete the current document
         *
         */
        deleteDocument: function dvDocumentDocumentDelete()
        {
            this.model.deleteDocument();
        },

        /**
         * Display the loading widget
         */
        displayLoading: function vDocumentDisplayLoading(options)
        {
            var text = i18n.___("Loading", "ddui"), avance = 50;
            options = options || {};
            this.$el.append('<div class="dcpDocument--disabled"/>');
            if (options.isSaving) {
                text = i18n.___("Recording", "ddui");
                avance = 70;
            }
            this.trigger("cleanNotification");
            this.trigger("loaderShow", text, avance);
        },

        /**
         * Show the view
         *
         * Hide the loader, show the view
         */
        showView: function vDocumentShowView()
        {
            this.$el.hide();
            this.$el.find(".dcpDocument--disabled").remove();
            this.trigger("loaderHide");
            this.$el.show();
            this.model.redrawErrorMessages();
        },

        /**
         * Switch the view
         *
         * @param viewId
         */
        closeDocument: function vDocumentCloseDocument(viewId)
        {
            var confirmWindow;
            var documentView = this;
            var initid = this.model.get("initid");
            if (!viewId) {
                if (this.model.get("renderMode") === "edit") {
                    viewId = "!defaultEdition";
                } else {
                    viewId = "!defaultConsultation";
                }
            }
            if (this.model.hasAttributesChanged()) {
                confirmWindow = $('body').dcpConfirm({
                    title: i18n.___("Confirm close document", "ddui"),
                    width: "510px",
                    height: "150px",
                    maxWidth: $(window).width(),
                    messages: {
                        okMessage: i18n.___("Abord modification", "ddui"),
                        cancelMessage: i18n.___("Stay on the form", "ddui"),
                        htmlMessage: i18n.___("The form has been modified without saving", "ddui"),
                        textMessage: ''
                    },
                    confirm: function wMenuConfirm()
                    {
                        documentView.model.clear();
                        documentView.model.set("viewId", viewId);
                        documentView.model.set("initid", initid);
                        documentView.model.fetch();
                    },
                    templateData: {templates: this.model.get("templates")}
                });
                confirmWindow.data('dcpWindow').open();
            } else {
                this.model.clear();
                this.model.set("viewId", viewId);
                this.model.set("initid", initid);
                this.model.fetch();
            }
        },

        /**
         * Save the current document
         */
        saveDocument: function vDocumentSaveDocument()
        {
            this.trigger("cleanNotification");
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentSaveDisplaySuccess()
                {
                    currentView.trigger("showSuccess", {title: i18n.___("Document Recorded", "ddui")});
                });
            }
        },


        /**
         * Save and close the current document
         */
        saveAndCloseDocument: function vDocumentSaveAndCloseDocument()
        {
            this.trigger("cleanNotification");
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentSaveAndCloseSuccess()
                {
                    var initid = currentView.model.get("initid");

                    currentView.model.clear();
                    currentView.model.set("initid", initid);
                    currentView.model.set("viewId", "!defaultConsultation");
                    currentView.model.fetch();
                });
            }
        },

        /**
         * Create the current document
         */
        createDocument: function vDocumentCreateDocument()
        {
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentCreateDisplaySuccess()
                {
                    currentView.trigger("showSuccess", {title: i18n.___("Document Created", "ddui")});
                });
            }
        },

        /**
         * Create the current document
         */
        createAndCloseDocument: function vDocumentCreateDocument()
        {
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentCreateAndCloseSuccess()
                {
                    var initid = currentView.model.get("initid");

                    currentView.model.clear();
                    currentView.model.set("initid", initid);
                    currentView.model.set("viewId", "!defaultConsultation");
                    currentView.model.fetch();
                });
            }
        },

        /**
         * load another document document
         */
        loadDocument: function vDocumentLoadDocument(docid, viewId)
        {
            this.model.clear();
            this.model.set({initid: docid});
            if (viewId) {
                this.model.set({viewId: viewId});
            }
            this.model.fetch();
        },

        /**
         * Propagate menu event
         *
         * @param options
         * @returns {*}
         */
        actionDocument: function vDocumentActionDocument(options)
        {
            var event = {prevent: false};
            this.model.trigger("internalLinkSelected", event, options);
            if (event.prevent) {
                return this;
            }
            options = options.options;
            if (options[0] === "save") {
                return this.saveDocument();
            }

            if (options[0] === "saveAndClose") {
                return this.saveAndCloseDocument();
            }

            if (options[0] === "history") {
                return this.showHistory();
            }
            if (options[0] === "transition") {
                return this.showtransition(options[1], options[2]);
            }
            if (options[0] === "transitionGraph") {
                return this.showTransitionGraph();
            }
            if (options[0] === "properties") {
                return this.showProperties();
            }
            if (options[0] === "delete") {
                return this.deleteDocument();
            }
            if (options[0] === "close") {
                return this.closeDocument(options[1]);
            }
            if (options[0] === "edit") {
                return this.closeDocument("!defaultEdition");
            }
            if (options[0] === "create") {
                return this.createDocument();
            }

            if (options[0] === "createAndClose") {
                return this.createAndCloseDocument();
            }

            if (options[0] === "load") {
                return this.loadDocument(options[1], options[2]);
            }
            if (options[0] === "lock") {
                return this.lockDocument();
            }
            if (options[0] === "unlock") {
                return this.unlockDocument();
            }
        },

        /**
         * Get the template for the current view
         *
         * @param key
         * @returns {*}
         */
        getTemplates: function vDocumentGetTemplates(key)
        {
            var templates = {};
            if (this.model && this.model.get("templates")) {
                templates = this.model.get("templates");
            }
            if (templates[key]) {
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
        remove: function vDocumentRemove()
        {
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
            $(window).off("." + this.model.cid);
            $(window.document).off("." + this.model.cid);


            return Backbone.View.prototype.remove.call(this);
        }
    });

});