/*global define, console*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/frame/vFrame',
    'dcpDocument/views/document/attributeTemplate',
    'dcpDocument/i18n/documentCatalog'
], function vTabContent($, _, Backbone, Mustache, ViewAttributeFrame, attributeTemplate, i18n)
{
    'use strict';

    return Backbone.View.extend({

        tagName: "div",

        className: "dcpTab__content",
        customView: false,

        initialize: function vTabContentInitialize(options)
        {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'showTab', this.renderContent);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'haveView', this._identifyView);
            this.initializeContent = options.initializeContent;
            this.initialized = false;
            if (options.originalView !== true) {
                if (this.model.getOption("template")) {
                    this.customView = attributeTemplate.customView(this.model);
                }
            }
            this.options = options;
        },

        render: function vTabContentRender()
        {
            var hasOneContent;
            this.$el.empty().append($('<div class="dcpTab__content--loading"><span class="fa fa-spinner fa-spin"></span>'+
            i18n.___("Displaying","ddui")+'</div>'));
            this.$el.attr("id", this.model.id);
            this.$el.attr("data-attrid", this.model.id);

            hasOneContent = this.model.get("content").some(function vTabContentIsDisplayable(value)
            {
                return value.isDisplayable();
            });

            if (!hasOneContent || !this.initializeContent) {
                this.$el.append(this.model.getOption('showEmptyContent'));
                this.$el.removeClass("dcpTab__content--loading");
                this.model.trigger("renderDone", {model: this.model, $el: this.$el});
            } else {
                this.renderContent();

            }

            this.propageShowTab();


            return this;
        },

        renderContent: function vTabContentRenderContent()
        {
            var $content = this.$el, model = this.model;
            if (this.initialized === false) {
                this.$el.empty();
                if (this.customView) {
                    $content.append(this.customView);
                } else {
                    this.model.get("content").each(function vTabContentRenderContent(currentAttr)
                    {
                        var view;
                        try {
                            if (!currentAttr.isDisplayable()) {
                                return;
                            }
                            if (currentAttr.get("type") === "frame") {
                                view = new ViewAttributeFrame({model: currentAttr});
                                $content.append(view.render().$el);

                            } else {
                                //noinspection ExceptionCaughtLocallyJS
                                throw new Error("unkown type " + currentAttr.get("type") + " for id " + currentAttr.id + " for tab " + model.id);
                            }
                        } catch (e) {
                            if (window.dcp.logger) {
                                window.dcp.logger(e);
                            } else {
                                console.error(e);
                            }
                        }
                    });
                    attributeTemplate.insertDescription(this);
                    if (this.model.getOption("responsiveColumns")) {
                        this.responsiveColumns();
                    }
                }

                this.$el.removeClass("dcpTab__content--loading");
                this.model.trigger("renderDone", {model: this.model, $el: this.$el});
                this.initialized = true;
            }
            $(window.document).trigger("redrawErrorMessages");
        },




        responsiveColumns: function vTab_responsiveColumns() {
            var responseColumnsDefs=this.model.getOption("responsiveColumns") || [];
            var _this=this;
            var $fake=$("<div/>").css({position:"absolute", top:0, overflow:"hidden"});
            var $fakeWidth=$("<div/>");
            var setResponsiveClasse=function vTab_setResponsiveClasses() {
                var fWidth=$(_this.$el).width();
                var matchesResponsive=0;

                _.each(responseColumnsDefs, function vTab_setResponsiveClasses(responseColumnsInfo) {
                    if (fWidth >= responseColumnsInfo.minAbsWidth && fWidth < responseColumnsInfo.maxAbsWidth) {
                        _this.$el.addClass("dcp-column--"+responseColumnsInfo.number);
                        matchesResponsive=responseColumnsInfo.number;
                        if (responseColumnsInfo.grow === true) {
                            _this.$el.addClass("dcp-column--grow");
                        } else {
                            _this.$el.removeClass("dcp-column--grow");
                        }
                    } else {
                        _this.$el.removeClass("dcp-column--"+responseColumnsInfo.number);
                    }
                });

                if (matchesResponsive > 1) {
                    _this.$el.addClass("dcp-column");
                } else {
                    _this.$el.removeClass("dcp-column");
                }
                if (matchesResponsive !== _this.frameIsResized) {
                    _this.frameIsResized=matchesResponsive;
                    _this.model.get('content').each(function (ma) {
                        // Send resize to frame in case they have also responsive.
                        ma.trigger("resize");
                    });
                }
            };

            $("body").append($fake.append($fakeWidth));

            // Compute absolute width
            _.each(responseColumnsDefs, function vTab_computeResponsiveWidth(responseColumnsInfo) {
                if (! responseColumnsInfo.minWidth) {
                    responseColumnsInfo.minAbsWidth = 0;
                } else {
                    $fakeWidth.width(responseColumnsInfo.minWidth);
                    responseColumnsInfo.minAbsWidth = $fakeWidth.width();
                }

                if (! responseColumnsInfo.maxWidth) {
                    responseColumnsInfo.maxAbsWidth = Infinity;
                } else {
                    $fakeWidth.width(responseColumnsInfo.maxWidth);
                    responseColumnsInfo.maxAbsWidth = $fakeWidth.width();
                }
            });

            $fake.remove();
            console.log("Add RESIZE Tab", _this.model.id, this.model.cid, responseColumnsDefs);
            $(window).on("resize."+this.model.cid, setResponsiveClasse);
            _.defer(setResponsiveClasse);
        },

        propageShowTab: function vTabContentPropageShowTab()
        {
            this.model.get("content").propageEvent('showTab');
        },

        updateLabel: function vTabContentUpdateLabel()
        {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        hide: function vTabContentHide()
        {
            this.$el.hide();
        },

        show: function vTabContentShow()
        {
            this.$el.show();
        },

        _identifyView: function vAttribute_identifyView(event)
        {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        }
    });

});