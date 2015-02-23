define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'views/document/vDocument'
], function (_, $, Backbone, Mustache, ViewDocument) {
    'use strict';

    return ViewDocument.extend({

        messages: [],
        initialize: function vAttributeInitialize(options) {
            ViewDocument.prototype.initialize.apply(this, arguments);
            this.listenTo(this.model, 'showError', this.displayError);
            this.listenTo(this.model, 'request', this.displayLoading);
            this.options = options;
        },


        remove: function vTransitionRemove() {

            ViewDocument.prototype.remove.apply(this, arguments);
            //Remove custom CSS
            var customCss= _.pluck(this.model.get("customCSS"), "key");

            if (customCss.length > 0) {
                _.each(customCss, function (cssKey) {
                    $('link[data-view=true][data-id="'+cssKey+'"]').remove();
                });
            }
        },

        displayError: function (error) {
            var workflow = this.model.get("workflow");
            var attributes = this.model.get("attributes");
            var $okButton = this.$el.find(".dcpTransition-button-ok");
            var $cancelButton = this.$el.find(".dcpTransition-button-cancel");
            this.reactiveWidget();
            if (_.isObject(error)) {
                var errorMessage = '<div class="dcpTransition--error">{{title}} {{{htmlMessage}}}</div>';
                $(Mustache.render(errorMessage, error)).insertBefore(this.$el.find(".dcpTransition--buttons"));
            }
            if (attributes.length === 0) {
                $okButton.hide();
                $cancelButton.text(workflow.labels.close);
            } else {
                $okButton.text(workflow.labels.retry);
            }
        },
        cleanAndRender: function () {

            var workflow = this.model.get("workflow");
            var attributes = this.model.get("attributes");
            var transition = workflow.transition;
            var state = workflow.state;


            this.render();

            this.displayMessages(this.model.get("messages"));
            this.clearError();
            this.reactiveWidget();
            if (!transition && state) {
                this.$el.trigger("reload", [this.messages]);
            }

        },

        clearError: function () {
            this.$el.find(".dcpTransition--error").remove();
        },

        reactiveWidget: function () {

            var workflow = this.model.get("workflow");
            var attributes = this.model.get("attributes");

            var $loading = this.$el.find(".dcpTransition--loading");
            var $okButton = this.$el.find(".dcpTransition-button-ok");
            var $cancelButton = this.$el.find(".dcpTransition-button-cancel");
            if (attributes.length > 0) {
                if (workflow && workflow.labels.confirm) {

                    $okButton.text(workflow.labels.confirm);
                }
                $okButton.prop("disabled", false);
            }
            $cancelButton.prop("disabled", false);
            $loading.hide();
        },

        /**
         * Display the loading widget
         */
        displayLoading: function vTransitionDisplayLoading() {

            var workflow = this.model.get("workflow");

            var $loading = this.$el.find(".dcpTransition--loading");
            var $okButton = this.$el.find(".dcpTransition-button-ok");
            var $cancelButton = this.$el.find(".dcpTransition-button-cancel");

            $loading.show();

            this.clearError();
            $okButton.prop("disabled", true);
            $cancelButton.prop("disabled", true);
        },

        displayMessages: function (messages) {
            var scope = this;
            var documentModel = this.model.get("documentModel");
            var tpl = '<div class="dcpTransition--message dcpTransition--message--{{type}}">{{contentText}} {{{contentHtml}}}</div>';
            var $buttons = this.$el.find(".dcpTransition--buttons");
            var $message = this.$el.find(".dcpTransition--messages");

            this.messages = [];

            _.each(messages, function (message) {
                $message.append($(Mustache.render(tpl, message)));

                scope.messages.push({
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
        render: function vTransitionRender() {
            var scope = this;
            var workflow = this.model.get("workflow");
            var attributes = this.model.get("attributes");
            var transition = workflow.transition;
            var state = workflow.state;
            ViewDocument.prototype.render.apply(this, arguments);


            workflow.hasAttributes = (attributes.length > 0);
            if (transition) {
                // Transition ask
                this.$el.find(".dcpTransition--header").append(Mustache.render(this.htmlContent(), workflow));

                this.$el.find(".dcpTransition--messages").append(Mustache.render(this.htmlLoading, workflow));
                    this.$el.kendoWindow("center");
                    this.$el.kendoWindow("title", workflow.transition.label);


                this.$el.find(".dcpTransition--buttons").append(Mustache.render(this.htmlButtons, workflow));
                this.$el.find(".dcpTransition-button-ok").on("click", function () {
                    scope.model.save();
                }).tooltip();
                this.$el.find(".dcpTransition-button-cancel").on("click", function () {
                    scope.$el.kendoWindow("close");
                });

                if (attributes.length === 0) {
                    // Direct send transition without user control
                    _.defer(function () {
                        scope.model.save();
                    });
                }

            } else if (state) {
                // Transition success
                this.$el.find(".dcpTransition--header").append(Mustache.render(this.htmlStateContent(), workflow));

                this.$el.find(".dcpTransition--buttons").append(Mustache.render(this.htmlStateButtons, workflow));
                this.$el.find(".dcpTransition-button-close").on("click", function () {

                    scope.$el.kendoWindow("close");
                }).tooltip();


            }


        },


        htmlContent: function () {
            return '<div class="dcpTransition--content-activity">' +
            '{{transition.currentState.displayValue}} <span class="dcpTransition--activity" style="background-color:{{transition.currentState.color}}">&nbsp;</span>' +
            '<span class="dcpTransition--transition {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}" >{{transition.label}}</span>' +
            '<span><i class="fa fa-chevron-right {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}"></i></span>' +
            '<span class="dcpTransition--activity" style="background-color:{{transition.nextState.color}}">&nbsp;</span> {{transition.nextState.displayValue}}' +
            '</div>';
        },
        htmlStateContent: function () {
            return '<div class="dcpTransition--content-activity">' +
            '<span class="dcpTransition--success" >{{labels.success}}</span>' +
            '</div>'  ;
        },

        htmlStateButtons : '<button title="{{labels.close}}" class="dcpTransition-button-close btn btn-default btn-sm">' +
            '{{labels.close}}' +
            '</button>',

        htmlLoading:'<div class="dcpTransition--loading"><i class="fa fa-2x fa-spinner fa-spin"></i> {{labels.inprogress}}</div>',

        htmlButtons:

        '{{#hasAttributes}}<button class="dcpTransition-button-cancel btn btn-default btn-sm">{{labels.cancel}}</button>' +
        '<button title="{{transition.label}}" ' +
        'class="dcpTransition-button-ok btn {{#transition.id}}btn-primary{{/transition.id}}  {{^transition.id}}btn-danger{{/transition.id}} btn-sm">' +
        '{{labels.confirm}}</button>{{/hasAttributes}}'


    });
});