/*global define*/

define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'views/document/menu/vMenu',
    'views/document/header/vHeader',
    'views/attributes/frame/vFrame',
    'views/attributes/tab/vTabLabel',
    'views/attributes/tab/vTabContent',
    'kendo/kendo.core',
    'kendo/kendo.tabstrip',
    'widgets/history/wHistory',
    'widgets/properties/wProperties'
], function (_, $, Backbone, Mustache, ViewDocumentMenu, ViewDocumentHeader, ViewAttributeFrame, ViewAttributeTabLabel, ViewAttributeTabContent, kendo) {
    'use strict';

    return Backbone.View.extend({

        className : "dcpDocument container-fluid",

        /**
         * Init event
         */
        initialize : function vDocumentInitialize() {
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'request', this.displayLoading);
            this.listenTo(this.model, 'sync', this.cleanAndRender);
            this.listenTo(this.model, 'reload', this.cleanAndRender);
            this.listenTo(this.model, 'invalid', this.showView);
            this.listenTo(this.model, 'error', this.showView);
        },

        /**
         * Clean the associated view and re-render it
         */
        cleanAndRender : function vDocumentCleanAndRender() {
            this.$el.removeClass("dcpDocument--view").removeClass("dcpDocument--edit");
            try {
                if (this.historyWidget) {
                    this.historyWidget.destroy();
                }
                if (this.propertiesWidget) {
                    this.propertiesWidget.destroy();
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
        render : function vDocumentRender() {
            console.time("render document view");
            var $content, model = this.model, $el = this.$el, currentView = this;
            var locale = this.model.get('locale');
            var documentView = this;

            this.template = this.getTemplates("body");
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

            kendo.culture(locale);

            //add document base
            try {
                this.$el.append($(Mustache.render(this.template, this.model.toData(), this.partials)));
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            this.$el.addClass("dcpDocument dcpDocument--" + this.model.get("renderMode"));
            this.trigger("loading", 10);
            //add menu
            try {
                var viewMenu = new ViewDocumentMenu({
                    model : this.model,
                    el :    this.$el.find(".dcpDocument__menu:first")[0]
                }).render();

                this.listenTo(viewMenu, 'document', this.actionDocument);
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            try {
                new ViewDocumentHeader({
                    model : this.model,
                    el :    this.$el.find(".dcpDocument__header:first")[0]
                }).render();
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }
            this.trigger("loading", 20);
            //add first level attributes
            console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");

            this.model.get("attributes").each(function vDocumentRenderAttribute(currentAttr) {
                var view, viewTabLabel, viewTabContent, tabItems;
                if (!currentAttr.isDisplayable()) {
                    currentView.trigger("partRender");
                    return;
                }
                if (currentAttr.get("type") === "frame" && _.isUndefined(currentAttr.get("parent"))) {
                    try {
                        view = new ViewAttributeFrame({model : model.get("attributes").get(currentAttr.id)});
                        $content.append(view.render().$el);
                    } catch (e) {
                        if (window.dcp.logger) {
                            window.dcp.logger(e);
                        } else {
                            console.error(e);
                        }
                    }
                }
                if (currentAttr.get("type") === "tab" && _.isUndefined(currentAttr.get("parent"))) {
                    try {
                        var tabModel = model.get("attributes").get(currentAttr.id);
                        viewTabLabel = new ViewAttributeTabLabel({model : tabModel});
                        viewTabContent = new ViewAttributeTabContent({model : tabModel});
                        if (tabModel.getOption("openFirst")) {
                            currentView.selectedTab = currentAttr.id;
                            //console.log("open ", currentAttr.id);
                        }
                        $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);
                        tabItems = $el.find(".dcpDocument__tabs__list").find('li');
                        if (tabItems.length > 1) {
                            tabItems.css("width", Math.floor(100 / tabItems.length) + '%').tooltip({
                                placement : "top",
                                title :     function vDocumentTooltipTitle() {
                                    return $(this).text(); // set the element text as content of the tooltip
                                }
                            });
                        } else {
                            tabItems.css("width", "80%");
                        }

                        $el.find(".dcpDocument__tabs").append(viewTabContent.render().$el);
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
                show : function vDocumentShowTab(event) {
                    var tabId = $(event.item).data("attrid");
                    currentView.$(".dcpTab__label").removeClass("dcpLabel--active").addClass("dcpLabel--default");
                    currentView.model.get("attributes").get(tabId).trigger("showTab");
                    currentView.$('.dcpLabel[data-attrid="' + tabId + '"]').addClass("dcpLabel--active").removeClass("dcpLabel--default");
                    if (documentView.selectedTab !== tabId) {
                        documentView.selectedTab = tabId;
                        documentView.recordSelectedTab(tabId);
                    }
                }
            });

            if (this.kendoTabs.data("kendoTabStrip")) {
                var selectTab = 'li[data-attrid=' + this.selectedTab + ']';
                if (this.selectedTab && $(selectTab).length > 0) {
                    this.kendoTabs.data("kendoTabStrip").select(selectTab);
                } else {
                    this.kendoTabs.data("kendoTabStrip").select(0);
                }
            }

            $(window.document).on('drop.ddui dragover.ddui', function vDocumentPreventDragDrop(e) {
                e.preventDefault();
            }).on('redrawErrorMessages.ddui', function vDocumentRedrawErrorMessages() {
                documentView.model.redrawErrorMessages();
            });
            this.$el.addClass("dcpDocument--show");
            console.timeEnd("render document view");
            this.trigger("renderDone");
            this.$el.show();
            return this;
        },

        /**
         * @Todo rewrite this part to suppress $.ajax
         *
         * Register the current tab for the current user
         *
         * @param tabId
         */
        recordSelectedTab : function vDocumentRecordSelectedTab(tabId) {
            //var documentView = this;
            $.ajax({
                url :         "api/v1/documents/" + this.model.get("initid") + '/usertags/lasttab',
                type :        "PUT",
                dataType :    "json",
                contentType : 'application/json',
                data :        tabId
            }).fail(function vDocumentTabRegisterError(response) {
                /*documentView.model.trigger("showError", {
                    "title" :   "User Tags",
                    "message" : "Cannot record tab selection"
                });*/
            });
        },

        /**
         * Publish associated model message
         */
        publishMessages : function vDocumentPublishMessages() {
            var currentView = this;
            _.each(this.model.get("messages"), function vDocumentPublishAMessage(aMessage) {
                if (aMessage.type === "message" || aMessage.type === "notice") {
                    aMessage.type = "info";
                }
                currentView.trigger("showMessage", {
                    type :        aMessage.type,
                    title :       aMessage.contentText,
                    htmlMessage : aMessage.contentHtml
                });
            });
        },

        /**
         * Inject associated CSS in the DOM
         *
         * Inject new CSS, remove old CSS
         */
        renderCss : function vDocumentRenderCss() {
            // add custom css style
            var $target = $("head link:last"),
                cssLinkTemplate = _.template('<link rel="stylesheet" type="text/css" href="<%= path %>" data-id="<%= key %>" data-view="true">'),
                customCss = this.model.get("customCSS");
            //Remove old CSS
            _.each($("link[data-view=true]"), function vDocumentRemoveOldCSS(currentLink) {
                var findCss = function (currentCss) {
                    return currentLink.dataset.id === currentCss.key;
                };
                if (_.find(customCss, findCss) === undefined) {
                    $(currentLink).remove();
                }
            });
            // Inject new CSS
            _.each(customCss, function vDocumentInjectNewCSS(cssItem) {
                var $existsLink = $('link[rel=stylesheet][data-id=' + cssItem.key + ']');
                if ($existsLink.length === 0) {
                    $target.after(cssLinkTemplate(cssItem));
                }
            });
        },
        /**
         * Inject new JS script in the dom
         * Use the facebook style injection
         */
        renderJS : function vDocumentRenderJS() {
            var insertJs = function vDocumentInsertJs(path, key) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.setAttribute("data-id", key);
                script.async = true;
                script.src = path;
                document.getElementsByTagName('head')[0].appendChild(script);
            };

            _.each(this.model.get("customJS"), function vDocumentHandleJS(jsItem) {
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
        showHistory : function vDocumentShowHistory() {
            var scope = this;
            this.historyWidget = this.$el.dcpDocumentHistory({
                documentId : this.model.get("properties").get("initid"),
                window :     {
                    width :  "80%",
                    height : "80%"
                }
            }).data("dcpDocumentHistory");

            this.historyWidget.open();
            this.historyWidget.currentWidget.on("viewRevision", function vDocumentViewRevision(event, data) {
                scope.model.clear();
                scope.model.set({initid : data.initid, revision : data.revision});
                scope.model.fetch();
            });
        },

        /**
         * Show the properties widget
         *
         */
        showProperties : function vDocumentShowProperties() {
            var scope = this;
            this.propertiesWidget = this.$el.dcpDocumentProperties({
                documentId : this.model.get("properties").get("initid"),
                window :     {
                    width :  "400px",
                    height : "auto"
                }
            }).data("dcpDocumentProperties");

            this.propertiesWidget.open();
            this.propertiesWidget.currentWidget.on("viewDocument", function vDocumentViewDocument(event, data) {
                scope.model.clear();
                scope.model.set("initid", data);
                scope.model.fetch();
            });
        },

        /**
         * Update the title of the current page
         */
        updateTitle : function vDocumentUpdateTitle() {
            document.title = this.model.get("properties").get("title");
        },

        /**
         * Update the icon of the current page
         */
        updateIcon : function vDocumentUpdateIcon() {
            $("link[rel='shortcut icon']").attr("href", this.model.get("properties").get("icon"));
        },

        /**
         * Delete the current document
         *
         * BEWARE : the deletion delete the model => so this function trigger a reinit event that create a new model
         */
        deleteDocument : function dvDocumentDocumentDelete() {
            var currentView = this, destroy = this.model.destroy();
            if (destroy !== false && destroy.done) {
                destroy.done(function vDocumentDestroyDone(response) {
                    currentView.trigger("reinit", {
                            initid :   response.data.view.documentData.document.properties.id,
                            revision : response.data.view.documentData.document.properties.revision,
                            viewId :   response.data.properties.identifier
                        }
                    );
                });
            }
        },

        /**
         * Display the loading widget
         */
        displayLoading : function vDocumentDisplayLoading() {
            this.$el.hide();
            this.trigger("cleanNotification");
            this.trigger("loader", 0);
            this.trigger("loaderShow");
        },

        /**
         * Show the view
         *
         * Hide the loader, show the view
         */
        showView : function vDocumentShowView() {
            this.$el.hide();
            this.trigger("loader", 0);
            this.trigger("loaderHide");
            this.$el.show();
            this.model.redrawErrorMessages();
        },

        /**
         * Switch the view
         *
         * @param viewId
         */
        closeDocument : function vDocumentCloseDocument(viewId) {
            if (!viewId) {
                if (this.model.get("renderMode") === "edit") {
                    viewId = "!defaultEdition";
                } else {
                    viewId = "!defaultConsultation";
                }
            }
            if (this.model.hasAttributesChanged() && !window.confirm("It has been changed !! Are you sure ??")) {
                return;
            }
            this.model.set("viewId", viewId);
            this.model.fetch();
        },

        /**
         * Save the current document
         */
        saveDocument : function vDocumentSaveDocument() {
            this.trigger("cleanNotification");
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentDisplaySuccess() {
                    currentView.trigger("showSuccess", {title : "Document Recorded"});
                });
            }
        },

        /**
         * Create the current document
         */
        createDocument : function vDocumentCreateDocument() {
            var currentView = this, save = this.model.save();
            //Use jquery xhr delegate done to display success
            if (save && save.done) {
                save.done(function vDocumentDisplaySuccess() {
                    currentView.trigger("showSuccess", {title : "Document Saved"});
                });
            }
        },

        /**
         * Propagate menu event
         *
         * @param options
         * @returns {*}
         */
        actionDocument : function vDocumentActionDocument(options) {
            var event = {prevent : false};
            this.model.trigger("internalLinkSelected", event, options);
            if (event.prevent) {
                return this;
            }
            options = options.options;
            if (options[0] === "save") {
                return this.saveDocument();
            }
            if (options[0] === "history") {
                return this.showHistory();
            }
            if (options[0] === "properties") {
                return this.showProperties();
            }
            if (options[0] === "delete") {
                return this.deleteDocument();
            }
            if (options[0] === "save") {
                return this.saveDocument();
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
        },

        /**
         * Get the template for the current view
         *
         * @param key
         * @returns {*}
         */
        getTemplates : function vDocumentGetTemplates(key) {
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
        remove : function vDocumentRemove() {
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
            $(window.document).off(".ddui");

            return Backbone.View.prototype.remove.call(this);
        }
    });

});