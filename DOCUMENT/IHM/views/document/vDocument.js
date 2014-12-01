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

        className: "dcpDocument container-fluid",

        initialize: function initialize() {
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'sync', this.cleanAndRender);
            this.listenTo(this.model, 'invalid', this.showView);
            this.listenTo(this.model, 'error', this.showView);
        },

        cleanAndRender: function cleanAndRender() {
            this.model.trigger("cleanView");
            this.render();
        },

        render: function render() {
            console.time("render document");
            var $content, model = this.model, $el = this.$el, currentView = this;
            var locale = this.model.get('locale');

            this.template = this.getTemplates("body");
            this.partials = this.getTemplates("sections");

            this.updateTitle();
            this.updateIcon();

            if (!locale) {
                locale = "fr-FR";
            }

            kendo.culture(locale);

            //add document base
            try {
                this.$el.empty().append($(Mustache.render(this.template, this.model.toData(), this.partials)));
            } catch (e) {
                console.log(e);
            }
            this.$el.addClass("dcpDocument dcpDocument--" + this.model.get("renderMode"));
            this.trigger("loading", 10);
            //add menu
            try {
                var viewMenu = new ViewDocumentMenu({
                    model: this.model,
                    el: this.$el.find(".dcpDocument__menu")[0]
                }).render();

                this.listenTo(viewMenu, 'document', this.actionDocument);
            } catch (e) {
                console.error(e);
            }
            try {
                new ViewDocumentHeader({
                    model: this.model,
                    el: this.$el.find(".dcpDocument__header")[0]
                }).render();
            } catch (e) {
                console.error(e);
            }
            this.trigger("loading", 20);
            //add first level attributes
            console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");
            this.model.get("attributes").each(function (currentAttr) {
                var view, viewTabLabel, viewTabContent, tabItems;
                if (!currentAttr.isDisplayable()) {
                    currentView.trigger("partRender");
                    return;
                }
                if (currentAttr.get("type") === "frame" && currentAttr.get("parent") === undefined) {
                    try {
                        view = new ViewAttributeFrame({model: model.get("attributes").get(currentAttr.id)});
                        $content.append(view.render().$el);
                    } catch (e) {
                        console.error(e);
                    }
                }
                if (currentAttr.get("type") === "tab" && currentAttr.get("parent") === undefined) {
                    try {
                        viewTabLabel = new ViewAttributeTabLabel({model: model.get("attributes").get(currentAttr.id)});
                        viewTabContent = new ViewAttributeTabContent({model: model.get("attributes").get(currentAttr.id)});
                        $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);
                        tabItems = $el.find(".dcpDocument__tabs__list").find('li');
                        if (tabItems.length > 1) {
                            tabItems.css("width", Math.floor(100 / tabItems.length) + '%').tooltip({
                                placement: "top",
                                title: function (e) {
                                    return $(this).text(); // set the element text as content of the tooltip
                                }
                            });
                        } else {
                            tabItems.css("width", "80%");
                        }

                        $el.find(".dcpDocument__tabs").append(viewTabContent.render().$el);
                        $el.find(".dcpDocument__tabs").show();
                    } catch (e) {
                        console.error(e);
                    }
                }
                currentView.trigger("partRender");
            });

            $(".dcpDocument__tabs").kendoTabStrip({
                show: function () {
                    currentView.model.trigger("showTab");
                }
            }).data("kendoTabStrip").select(0);


            $(document).on('drop dragover', function (e) {
                e.preventDefault();
            });
            this.$el.addClass("dcpDocument--show");
            this.trigger("renderDone");
            console.timeEnd("render document");
            this.$el.show();
            return this;
        },

        showHistory: function documentShowHistory(data) {
            var historyWidget = $('body').dcpDocumentHistory({
                documentId: this.model.get("properties").get("initid"),
                window: {
                    width: "80%",
                    height: "80%"
                }
            }).data("dcpDocumentHistory");

            historyWidget.open();
        },

        showProperties: function documentShowProperties(data) {
            var propertiesWidget = $('body').dcpDocumentProperties({
                documentId: this.model.get("properties").get("initid"),
                window: {
                    width: "400px",
                    height: "auto"
                }
            }).data("dcpDocumentProperties");

            propertiesWidget.open();
        },

        updateTitle: function () {
            document.title = this.model.get("properties").get("title");
        },

        updateIcon: function () {
            $("link[rel='shortcut icon']").attr("href", this.model.get("properties").get("icon"));
        },

        deleteDocument: function documentDelete(data) {

            $.ajax({
                type: "DELETE",
                dataType: "json",
                contentType: 'application/json',
                url: "api/v1/documents/" + this.model.get("properties").get("initid")
            }).done(function (response) {
                console.log("delete", response);
                window.location.href = window.location.href;
            }).fail(function (xhr) {
                console.log("fail delete", xhr);

            });
        },

        saveDocument: function saveDocument() {
            this.displayLoading();
            this.model.save();
        },

        displayLoading: function () {
            this.$el.hide();
            this.trigger("loader", 0);
            this.trigger("loaderShow");
        },

        showView : function () {
            this.$el.hide();
            this.trigger("loader", 0);
            this.trigger("loaderHide");
            this.model.clearErrorMessages();
            this.$el.show();
        },

        closeDocument: function closeDocument(viewId) {
            if (!viewId) {
                if (this.model.get("renderMode") === "edit") {
                    viewId = "!defaultEdition";
                } else {
                    viewId = "!defaultConsultation";
                }
            }
            this.model.set("viewId", viewId);
            this.displayLoading();
            this.model.fetch();
        },

        /**
         * Propagate menu event
         *
         * @param options
         * @returns {*}
         */
        actionDocument: function (options) {
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
        },

        getTemplates: function getTemplates(key) {
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
        }
    });

});