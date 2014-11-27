/*global define*/

define([
    'underscore',
    'backbone',
    'mustache',
    'views/document/menu/vMenu',
    'views/document/header/vHeader',
    'views/attributes/frame/vFrame',
    'views/attributes/tab/vTabLabel',
    'views/attributes/tab/vTabContent',
    'kendo/kendo.core',
    'widgets/history/wHistory',
    'widgets/properties/wProperties'
], function (_, Backbone, Mustache, ViewDocumentMenu, ViewDocumentHeader, ViewAttributeFrame, ViewAttributeTabLabel, ViewAttributeTabContent, kendo) {
    'use strict';

    return Backbone.View.extend({

        className : "dcpDocument container-fluid",

        initialize : function () {
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = this.getTemplates("body");
            this.partials = this.getTemplates("sections");
        },

        render : function () {
            var $content, model = this.model, $el = this.$el, currentView = this;
            var locale = this.model.get('locale');
            // console.time("render doc");

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
            //console.time("render menu");
            try {
                var viewMenu = new ViewDocumentMenu({
                    model : this.model,
                    el :    this.$el.find(".dcpDocument__menu")[0]
                }).render();

                this.listenTo(viewMenu, 'document:history', this.showHistory);
                this.listenTo(viewMenu, 'document:properties', this.showProperties);
                this.listenTo(viewMenu, 'document:delete', this.deleteDocument);
            } catch (e) {
                console.error(e);
            }
            try {
                new ViewDocumentHeader({
                    model : this.model,
                    el :    this.$el.find(".dcpDocument__header")[0]
                }).render();
            } catch (e) {
                console.error(e);
            }
            // console.timeEnd("render menu");
            this.trigger("loading", 20);
            //add first level attributes
            //  console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");
            this.model.get("attributes").each(function (currentAttr) {
                var view, viewTabLabel, viewTabContent, tabItems;
                if (!currentAttr.isDisplayable()) {
                    currentView.trigger("partRender");
                    return;
                }
                if (currentAttr.get("type") === "frame" && currentAttr.get("parent") === undefined) {
                    try {
                        view = new ViewAttributeFrame({model : model.get("attributes").get(currentAttr.id)});
                        $content.append(view.render().$el);
                    } catch (e) {
                        console.error(e);
                    }
                }
                if (currentAttr.get("type") === "tab" && currentAttr.get("parent") === undefined) {
                    try {
                        viewTabLabel = new ViewAttributeTabLabel({model : model.get("attributes").get(currentAttr.id)});
                        viewTabContent = new ViewAttributeTabContent({model : model.get("attributes").get(currentAttr.id)});
                        $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);
                        tabItems = $el.find(".dcpDocument__tabs__list").find('li');
                        if (tabItems.length > 1) {
                            tabItems.css("width", Math.floor(100 / tabItems.length) + '%').kendoTooltip({
                                position : "top",
                                content :  function (e) {
                                    var target = e.target; // the element for which the tooltip is shown
                                    return $(target).text(); // set the element text as content of the tooltip
                                }
                            });
                        } else {
                            tabItems.css("width", "80%");
                            tabItems.css("width", "80%");
                        }

                        $el.find(".dcpDocument__tabs__content").append(viewTabContent.render().$el);
                        $el.find(".dcpDocument__tabs").show();
                    } catch (e) {
                        console.error(e);
                    }
                }
                currentView.trigger("partRender");
            });
            if ($el.find('.dcpDocument__tabs__list a:first').tab) {
                $el.find('.dcpDocument__tabs__list a:first').tab('show');
            }
            $(document).on('drop dragover', function (e) {
                e.preventDefault();
            });
            this.$el.addClass("dcpDocument--show");
            this.trigger("renderDone");
            return this;
        },

        showHistory :    function documentShowHistory(data) {
            var historyWidget = $('body').dcpDocumentHistory({
                documentId : this.model.get("properties").get("initid"),
                window :     {
                    width :  "80%",
                    height : "80%"
                }
            }).data("dcpDocumentHistory");

            historyWidget.open();
        },

        showProperties : function documentShowProperties(data) {

            var propertiesWidget = $('body').dcpDocumentProperties({
                documentId : this.model.get("properties").get("initid"),
                window :     {
                    width :  "400px",
                    height : "auto"
                }
            }).data("dcpDocumentProperties");

            propertiesWidget.open();
        },

        deleteDocument : function documentDelete(data) {

            $.ajax({
                type :        "DELETE",
                dataType :    "json",
                contentType : 'application/json',
                url : "api/v1/documents/" + this.model.get("properties").get("initid")
            }).done(function (response) {
                console.log("delete", response);
                window.location.href = window.location.href;
            }).fail(function (xhr) {
                console.log("fail delete", xhr);

            });
        },

        getTemplates : function getTemplates(key) {
            var templates = {};
            if (this.model && this.model.get("templates")) {
                templates = this.model.get("templates");
            }
            if (templates[key]) {
                return templates[key];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates[key]) {
                return window.dcp.templates[key];
            }
            throw new Error("Unknown template  " + key);
        }
    });

});