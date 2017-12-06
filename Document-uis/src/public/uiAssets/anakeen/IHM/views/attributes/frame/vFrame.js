/*global define, console*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute',
    'dcpDocument/views/attributes/array/vArray',
    'dcpDocument/views/document/attributeTemplate'
], function require_vFrame($, _, Backbone, Mustache, ViewAttribute, ViewAttributeArray, attributeTemplate)
{
    'use strict';

    return Backbone.View.extend({

        className: "card card-default dcpFrame",
        customView: false,
        displayLabel: true,

        events: {
            "click .dcpFrame--collapsable": "toggle",
            'click a[href^="#action/"], a[data-action], button[data-action]': 'externalLinkSelected'
        },

        initialize: function vFrame_initialize(options)
        {

            if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
                this.displayLabel = false;
            }
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'change:errorMessage', this.setError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'resize', this.setResponsiveClasse);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'haveView', this._identifyView);

            this.options = options;
        },

        render: function vFrame_render() {
            var currentView = this;
            return (new Promise(_.bind(function vFrame_renderPromise(resolve, reject) {
                var customRender,
                    $content,
                    labelElement,
                    contentElement = '',
                    customView = null,
                    event = {prevent: false},
                    contentData,
                    promiseAttributes = [];

                currentView.model.trigger("beforeRender", event, {model: currentView.model, $el: currentView.$el});
                if (event.prevent) {
                    resolve(currentView);
                    return currentView;
                }

                if (currentView.options.originalView !== true) {
                    if (currentView.model.getOption("template")) {
                        customRender = attributeTemplate.renderCustomView(currentView.model);
                        currentView.customView = customRender.$el;
                        promiseAttributes.push(customRender.promise);
                    }
                }

                contentData = currentView.model.toData(null, true);
                if (currentView.model.getOption("attributeLabel")) {
                    contentData.label = currentView.model.getOption("attributeLabel");
                }
                contentData.collapsable = (contentData.renderOptions.collapse !== "none");

                currentView.templateLabel = currentView.model.getTemplates().attribute.frame.label;
                labelElement = $(Mustache.render(currentView.templateLabel || "", contentData));

                if (currentView.customView) {
                    contentElement = currentView.customView;
                    contentElement.addClass("dcpFrame__content dcpFrame__content--open");
                } else {
                    currentView.templateContent = currentView.model.getTemplates().attribute.frame.content;
                    contentElement = $(Mustache.render(currentView.templateContent || "", contentData));
                }
                currentView.$el.empty();
                if (currentView.displayLabel === true) {
                    currentView.$el.append(labelElement);
                }
                currentView.$el.append(contentElement);
                currentView.$el.attr("data-attrid", currentView.model.id);

                $content = currentView.$el.find(".dcpFrame__content");
                var hasOneContent = currentView.model.get("content").some(function vFrame_getDisplayable(value) {
                    return value.isDisplayable();
                });

                if (!currentView.customView) {
                    if (!hasOneContent) {
                        $content.append(currentView.model.getOption('showEmptyContent'));
                    } else {
                        currentView.model.get("content").each(function vFrame_AnalyzeContent(currentAttr) {
                            var attributeView;
                            if (!currentAttr.isDisplayable()) {
                                return;
                            }
                            try {
                                customView = null;
                                if (currentAttr.get("isValueAttribute")) {
                                    attributeView = new ViewAttribute({
                                        model: currentAttr,
                                        customView: customView
                                    });
                                    promiseAttributes.push(attributeView.render());
                                    $content.append(attributeView.$el);
                                    return;
                                }
                                if (currentAttr.get("type") === "array") {
                                    attributeView = new ViewAttributeArray({
                                        model: currentAttr
                                    });
                                    promiseAttributes.push(attributeView.render());
                                    $content.append(attributeView.$el);
                                }
                            } catch (e) {
                                $content.append('<h1 class="bg-danger"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>Unable to render ' + currentAttr.id + '</h1>');
                                if (window.dcp.logger) {
                                    window.dcp.logger(e);
                                } else {
                                    console.error(e);
                                }
                            }
                        });
                    }

                    attributeTemplate.insertDescription(currentView);
                }

                if (currentView.model.getOption("collapse") === "collapse") {
                    currentView.toggle(null, true);
                }
                Promise.all(promiseAttributes).then(function allRenderDone() {
                    currentView.model.trigger("renderDone", {model: currentView.model, $el: currentView.$el});
                    resolve(currentView);
                }).catch(reject);
            }), this));

        },

        setResponsiveClasse: function vFrame_setResponsiveClasse() {
            var _this=this;
            var fWidth=0;
            var matchesResponsive=0;
            var responseColumnsDefs=this.model.getOption("responsiveColumns") || [];
            var isTopBottom=false;
            var isGrow=false;

            if (responseColumnsDefs.length > 0) {
                fWidth=$(this.$el).width();

                if (fWidth <= 0 ) {
                    return;
                }
                var $vattrs=this.$el.find("> .dcpFrame__content > .row");

                _.each(responseColumnsDefs, function vFrame_setResponsiveClasses(responseColumnsInfo) {
                    if (fWidth >= responseColumnsInfo.minAbsWidth && fWidth < responseColumnsInfo.maxAbsWidth) {

                        matchesResponsive = responseColumnsInfo.number;
                        if (responseColumnsInfo.grow === true) {
                            _this.$el.addClass("dcp-column--grow");
                            isGrow=true;
                        } else {
                            _this.$el.removeClass("dcp-column--grow");
                            isGrow=false;
                        }
                        isTopBottom=responseColumnsInfo.direction === "topBottom";

                        if (isGrow) {
                            if ($vattrs.length < matchesResponsive) {
                                matchesResponsive = $vattrs.length;
                            } else if (isTopBottom) {
                                var rowNumber=Math.ceil($vattrs.length / matchesResponsive);
                                for (var i=matchesResponsive; i--; i>1) {
                                    if (Math.ceil($vattrs.length / i) === rowNumber) {
                                        // Decrease column number if not enough data to avoid empty columns
                                        matchesResponsive=i;
                                    }
                                }
                            }
                        }

                        if (matchesResponsive > 1) {
                            _this.$el.addClass("dcp-column--" + matchesResponsive);
                        }
                    } else {
                        _this.$el.removeClass("dcp-column--" + responseColumnsInfo.number);
                    }
                });


                if (matchesResponsive > 1) {
                    _this.$el.addClass("dcp-column");
                    if (matchesResponsive !== this.frameColumnNumber) {

                        this.frameColumnNumber=matchesResponsive;

                        if (isTopBottom) {
                            this.$el.addClass("dcp-column--topbottom");
                            this.$el.removeClass("dcp-column--leftright");
                        } else {
                            this.$el.removeClass("dcp-column--topbottom");
                            this.$el.addClass("dcp-column--leftright");
                        }
                    }
                } else {
                    this.frameColumnNumber=matchesResponsive;
                    this.$el.removeClass("dcp-column");
                    this.$el.removeClass("dcp-column--topbottom");
                    this.$el.removeClass("dcp-column--leftright");
                    this.$el.removeClass("dcp-column--grow");
                }
            }
        },

        responsiveColumns: function vFrame_responsiveColumns() {
            var responseColumnsDefs=this.model.getOption("responsiveColumns") || [];
            var _this=this;
            var $fake=$("<div/>").css({position:"absolute", top:0, overflow:"hidden"});
            var $fakeWidth=$("<div/>");


            $("body").append($fake.append($fakeWidth));

            // Compute absolute width
            _.each(responseColumnsDefs, function vFrame_computeResponsiveWidth(responseColumnsInfo) {
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
            $(window).on("resize.v"+this.model.cid, _.bind(this.setResponsiveClasse, this));
            _.defer(_.bind(this.setResponsiveClasse, this));
        },

        getAttributeModel: function vFrame_getAttributeModel(attributeId)
        {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        setError: function vFrame_setError(event, data)
        {
            if (data) {
                this.$el.find(".dcpFrame__label").addClass("has-error");
            } else {
                this.$el.find(".dcpFrame__label").removeClass("has-error");
            }
        },

        updateLabel: function vFrame_updateLabel()
        {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        toggle: function vFrame_toggle(event, hideNow)
        {
            var $contentElement = this.$(".dcpFrame__content");
            this.$(".dcp__frame__caret").toggleClass("fa-caret-right fa-caret-down");
            $contentElement.toggleClass("dcpFrame__content--open dcpFrame__content--close");
            if (hideNow) {
                $contentElement.hide();
            } else {
                $contentElement.slideToggle(200);
                if ($contentElement.hasClass("dcpFrame__content--open")) {
                    this.model.getDocumentModel().redrawErrorMessages();
                }
            }
        },

        hide: function vFrame_hide()
        {
            this.$el.hide();
        },

        show: function vFrame_show()
        {
            this.$el.show();
        },

        externalLinkSelected: function vAttributeExternalLinkSelected(event)
        {
            var $target = $(event.currentTarget),
                action,
                options,
                eventOptions,
                documentModel,
                internalEvent = {
                    prevent: false
                };

            event.preventDefault();
            if (event.stopPropagation) {
                event.stopPropagation();
            }

            action = $target.data('action') || $target.attr("href");
            options = action.substring(8).split(":");
            eventOptions = {
                target: event.target,
                index: -1,
                eventId: options.shift(),
                options: options
            };
            documentModel = this.model.getDocumentModel();

            this.model.trigger("internalLinkSelected", internalEvent, eventOptions);
            if (event.prevent) {
                return this;
            }

            documentModel.trigger("actionAttributeLink", internalEvent, eventOptions);

            return this;
        },

        _identifyView: function vFrame_identifyView(event)
        {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        },

        /**
         * Destroy the associated widget and suppress event listener before remov the dom
         *
         * @returns {*}
         */
        remove: function vFrame_Remove()
        {
            $(window).off(".v" + this.model.cid);

            return Backbone.View.prototype.remove.call(this);
        }
    });

});
