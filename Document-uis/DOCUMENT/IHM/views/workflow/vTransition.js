define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'dcpDocument/views/document/vDocument',
    'dcpDocument/widgets/window/wDialog'
], function vTransition(_, $, Backbone, Mustache, ViewDocument)
{
    'use strict';

    return ViewDocument.extend({

        messages: [],

        templates: {
            htmlContent: '<div class="dcpTransition--content-activity" >' +
            ' <span class="dcpTransition--activity" style="border-color:{{transition.beginState.color}}">{{transition.beginState.displayValue}}</span>' +
            '<span class="dcpTransition--transition {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}" >{{transition.label}}</span>' +
            '<span class="dcpTransition--arrow"><span class="fa fa-caret-right fa-2x {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}"></span></span>' +
            '<span class="dcpTransition--activity" style="border-color:{{transition.endState.color}}">{{transition.endState.displayValue}}</span> ' +
            '</div>',

            htmlStateContent: '<div class="dcpTransition--content-activity">' +
            '<span class="dcpTransition--success" >{{labels.success}}</span>' +
            '</div>',

            htmlStateButtons: '<button title="{{labels.close}}" class="dcpTransition-button-close btn btn-default btn-sm">' +
            '{{labels.close}} </button>',

            htmlLoading: '<div class="dcpTransition--loading"><span class="fa fa-2x fa-spinner fa-spin"></span> {{labels.inprogress}}</div>',

            htmlButtons: '{{#hasAttributes}}<button class="dcpTransition-button-cancel btn btn-default btn-sm">{{labels.cancel}}</button>' +
            '<button title="{{transition.label}}" ' +
            'class="dcpTransition-button-ok btn {{#transition.id}}btn-primary{{/transition.id}}  {{^transition.id}}btn-danger{{/transition.id}} btn-sm">' +
            '{{labels.confirm}}</button>{{/hasAttributes}}'
        },

        events: {
            "click .dcpTransition-button-ok ": "clickOnOk",
            "click .dcpTransition-button-cancel": "clickOnCancel",
            "click .dcpTransition-button-close": "clickOnClose"
        },

        initialize: function vTransition_initialize(options)
        {
            //Call parent
            ViewDocument.prototype.initialize.apply(this, arguments);
            this.listenTo(this.model, 'showError', this.displayError);
            //this.listenTo(this.model, 'invalid', this.displayError);
            this.listenTo(this.model, 'request', this.transitionDisplayLoading);
            this.listenTo(this.model, 'hide', function vTransition_hide()
            {
                this.$el.hide();
            });
            this.listenTo(this.model, 'show', function vTransition_show()
            {
                this.$el.show();
            });
            this.listenTo(this.model, 'close', function vTransition_close()
            {
                if (this.$el.data("kendoWindow")) {
                    this.$el.kendoWindow("close");
                }
            });
            this.options = options;
        },

        remove: function vTransition_remove()
        {
            if (this.transitionWindow) {
                this.transitionWindow.close();
            }
            //Call parent
            ViewDocument.prototype.remove.apply(this, arguments);
            //Remove custom CSS
            var customCss = _.pluck(this.model.get("customCSS"), "key");
            if (customCss.length > 0) {
                _.each(customCss, function vTransition_removeLink(cssKey)
                {
                    $('link[data-view=true][data-id="' + cssKey + '"]').remove();
                });
            }
        },

        displayError: function vTransition_displayError(error)
        {
            var workflow = this.model.get("workflow"),
                attributes = this.model.get("attributes"),
                $okButton = this.$el.find(".dcpTransition-button-ok"),
                $cancelButton = this.$el.find(".dcpTransition-button-cancel"),
                errorMessage;
            this.reactiveWidget();
            if (_.isObject(error)) {
                if (error.errorCode === "offline") {
                    errorMessage = '<div class="dcpTransition--error">{{{htmlMessage}}}</div>';
                } else {
                    errorMessage = '<div class="dcpTransition--error">{{title}} {{{htmlMessage}}}</div>';
                }
                $(Mustache.render(errorMessage || "", error)).insertBefore(this.$el.find(".dcpTransition--buttons"));
            }
            if (attributes.length === 0) {
                $okButton.hide();
                $cancelButton.text(workflow.labels.close);
            } else {
                //noinspection JSUnresolvedVariable
                $okButton.text(workflow.labels.retry);
            }
        },

        cleanAndRender: function vTransition_cleanAndRender()
        {
            var workflow = this.model.get("workflow"),
                transition = workflow.transition,
                state = workflow.state;

            this.render();
            this.displayMessages(this.model.get("messages"));
            this.clearError();
            this.reactiveWidget();
            if (!transition && state) {
                this.model.trigger("success", this.messages);
            }
        },
        /**
         * Inject associated CSS in the DOM
         *
         * Inject new CSS, no remove old CSS
         */
        renderCss: function vTransitionRenderCss()
        {
            ViewDocument.prototype.renderCss.apply(this, [true]);
        },
        updateTitle: function vTransitionupdateTitle()
        {
            // No update title
        },
        updateIcon: function vTransitionupdateIcon()
        {
            // No update icon
        },
        clearError: function vTransition_clearError()
        {
            this.$el.find(".dcpTransition--error").remove();
        },

        reactiveWidget: function vTransition_reactiveWidget()
        {

            var workflow = this.model.get("workflow"),
                attributes = this.model.get("attributes"),
                $loading = this.$el.find(".dcpTransition--loading"),
                $okButton = this.$el.find(".dcpTransition-button-ok"),
                $cancelButton = this.$el.find(".dcpTransition-button-cancel");

            if (attributes.length > 0) {
                if (workflow && workflow.labels.confirm) {
                    $okButton.text(workflow.labels.confirm);
                }
                $okButton.prop("disabled", false);
            }
            $cancelButton.prop("disabled", false);

            this.$el.find(".dcpDocument--disabled").remove();
            $loading.hide();
        },

        /**
         * Display the loading widget
         */
        transitionDisplayLoading: function vTransition_transitionDisplayLoading()
        {
            var $loading = this.$el.find(".dcpTransition--loading"),
                $okButton = this.$el.find(".dcpTransition-button-ok"),
                $cancelButton = this.$el.find(".dcpTransition-button-cancel");

            $loading.show();
            this.clearError();
            $okButton.prop("disabled", true);
            $cancelButton.prop("disabled", true);
        },

        displayMessages: function vTransition_displayMessages(messages)
        {
            var currentView = this,
                template = '<div class="dcpTransition--message dcpTransition--message--{{type}}">{{contentText}} {{{contentHtml}}}</div>',
                $message = this.$el.find(".dcpTransition--messages");

            this.messages = [];

            _.each(messages, function vTransition_analyzeCurrentMessage(message)
            {
                $message.append($(Mustache.render(template || "", message)));
                //noinspection JSUnresolvedVariable
                currentView.messages.push({
                    title: message.contentText,
                    type: message.type,
                    htmlMessage: message.contentHtml
                });
            });
        },

        /**
         * Render the document view
         * @returns {*}
         */
        render: function vTransition_render()
        {
            var currentView = this,
                workflow = this.model.get("workflow"),
                attributes = this.model.get("attributes"),
                transition = workflow.transition,
                state = workflow.state;

            //Call parent
            ViewDocument.prototype.render.apply(this, arguments);

            workflow.hasAttributes = (attributes.length > 0);
            if (transition) {
                // Transition ask
                this.$el.find(".dcpTransition--header").append(Mustache.render(this.templates.htmlContent || "", workflow));
                this.$el.find(".dcpTransition--messages").append(Mustache.render(this.templates.htmlLoading || "", workflow));
                this.$el.find(".dcpTransition--buttons").append(Mustache.render(this.templates.htmlButtons || "", workflow));
                this.$el.find(".dcpTransition-button-ok").tooltip();

                if (attributes.length === 0) {
                    // Direct send transition without user control
                    _.defer(function vTransition_saveForMe()
                    {
                        var event = {prevent: false}, saveXhr;
                        currentView.model.trigger("beforeChangeState", event);
                        if (event.prevent === false) {
                            saveXhr = currentView.model.save();
                            if (saveXhr) {
                                saveXhr.then(function vTransition_direct_afterSave()
                                {
                                    currentView.model.trigger("success", currentView.getMessages());
                                }).fail(function vTransition_direct_error(response, statusTxt, errorTxt)
                                {
                                    if (errorTxt && !errorTxt.title && errorTxt.message) {
                                        errorTxt.title = errorTxt.message;
                                    }
                                    currentView.displayError(errorTxt);
                                });
                            }
                        }
                    });
                }
                this.$el.attr("data-state", state.id);
                if (transition.id) {
                    this.$el.attr("data-transition", transition.id);
                }

                // No use border color if same as background
                _.defer(function vTransition_renderWhiteOnWhite()
                {
                    currentView.$el.find(".dcpTransition--activity").each(function vTransition_renderBorderColor()
                    {
                        if (currentView.$el.css("background-color") === $(this).css("border-color")) {
                            $(this).css("border-color", "");
                        }
                    });
                });

            } else
                if (state) {
                    // Transition success
                    this.$el.find(".dcpTransition--header").append(Mustache.render(this.templates.htmlStateContent || "", workflow));
                    this.$el.find(".dcpTransition--buttons").append(Mustache.render(this.templates.htmlStateButtons || "", workflow));
                    this.$el.find(".dcpTransition-button-close").tooltip();

                }

            if (!this.transitionWindow) {
                this.transitionWindow = this.$el.dcpDialog({
                    window: {
                        // maxWidth: "600px",
                        height: "auto",
                        close: function registerCloseEvent(e)
                        {
                            var event = {prevent: false};
                            currentView.model.trigger("beforeChangeStateClose", event);
                            if (event.prevent !== false) {
                                e.preventDefault();
                            }
                        }
                    }
                }).data("dcpDialog");
                this.$el.kendoWindow("title", workflow.transition.label);
                this.transitionWindow.open();
            }
            this.trigger("renderTransitionWindowDone");
        },

        clickOnOk: function vTransition_clickOnOk()
        {
            var event = {prevent: false}, currentView = this, saveXhr;
            this.model.trigger("beforeChangeState", event);

            if (event.prevent === false) {
                saveXhr = this.model.save();
                if (saveXhr) {
                    saveXhr.then(function vTransition_clickOnOk_afterSave()
                    {
                        currentView.model.trigger("success", currentView.getMessages());
                    }).fail(function vTransition_clickOnOk_error(response, statusTxt, errorTxt)
                    {
                        if (response.responseJSON) {
                            _.each(response.responseJSON.messages, function vTransition_clickOnOk_displayError(aMessage)
                            {
                                currentView.displayError({
                                    title: aMessage.contentText
                                });
                            });
                        } else {
                            if (errorTxt && !errorTxt.title && errorTxt.message) {
                                errorTxt.title = errorTxt.message;
                                currentView.displayError(errorTxt);
                            } else
                                if (_.isString(errorTxt)) {
                                    currentView.displayError({title: errorTxt});
                                }
                        }
                    });
                }
            }
        },

        clickOnCancel: function vTransition_clickOnCancel()
        {
            this.transitionWindow.close();
        },

        clickOnClose: function vTransition_clickOnClose()
        {
            this.transitionWindow.close();
        },

        getMessages: function vTransition_getMessages()
        {
            var messages = [];
            _.each(this.model.get("messages"), function vTransition_getMessagesAMessage(aMessage)
            {
                messages.push({
                    type: aMessage.type,
                    title: aMessage.contentText,
                    htmlMessage: aMessage.contentHtml
                });
            });
            return messages;
        }
    });
});