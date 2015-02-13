define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'views/document/vDocument'
], function (_, $, Backbone, Mustache, ViewDocument) {
    'use strict';

    return ViewDocument.extend({

        initialize: function vAttributeInitialize(options) {
            ViewDocument.prototype.initialize.apply(this, arguments);
            this.listenTo(this.model, 'sync', this.clearError);
            this.listenTo(this.model, 'showError', this.displayError);
            this.listenTo(this.model, 'request', this.displayLoading);
            this.options = options;
        },

        displayError: function (error) {
            var workflow=this.model.get("workflow");
            var attributes=this.model.get("attributes");
            var $okButton=this.$el.find(".dcpChangeState-button-ok");
            var $cancelButton=this.$el.find(".dcpChangeState-button-cancel");
            console.log("DISPLAY ERROR", error);
            this.reactiveWidget();
            if (_.isObject(error)) {
                var errorMessage = '<div class="dcpChangeState--error">{{title}} {{{htmlMessage}}}</div>';
                $(Mustache.render(errorMessage, error)).insertBefore(this.$el.find(".dcpChangeState--buttons"));
            }
            if (attributes.length === 0) {
                $okButton.hide();
                $cancelButton.text(workflow.labels.close);
            } else {
                $okButton.text(workflow.labels.retry);
            }
        },
        clearError: function (error) {
            this.reactiveWidget();
            console.log("SYNC");
            this.$el.find(".dcpChangeState--error").remove();

            this.displayMessages(this.model.get("messages"));

        },

        reactiveWidget: function () {

            var workflow=this.model.get("workflow");
            var attributes=this.model.get("attributes");

            var $okButton=this.$el.find(".dcpChangeState-button-ok");
            var $cancelButton=this.$el.find(".dcpChangeState-button-cancel");
            if (attributes.length > 0) {
             if (workflow && workflow.labels.confirm) {

                 $okButton.text(workflow.labels.confirm);
             }
                $okButton.prop("disabled", false);
            }
            $cancelButton.prop("disabled", false);
        },

        /**
         * Display the loading widget
         */
        displayLoading: function vChangeStateDisplayLoading() {

            var loadTpl='<i class="fa fa-spinner fa-spin"></i>';
            var workflow=this.model.get("workflow");

            var $okButton=this.$el.find(".dcpChangeState-button-ok");
            var $cancelButton=this.$el.find(".dcpChangeState-button-cancel");

            if (workflow && workflow.labels.inprogress) {
                loadTpl += workflow.labels.inprogress;
            }
            this.clearError();
            $okButton.html(loadTpl).prop("disabled", true);
            $cancelButton.prop("disabled", true);
        },

        displayMessages: function (messages) {
           console.log("NEW MESSAGES", arguments);

            var tpl='<div class="dcpChangeState--message dcpChangeState--message--{{type}}">{{contentText}} {{{contentHtml}}}</div>';
            var $buttons=this.$el.find(".dcpChangeState--buttons");

            _.each(messages, function (message) {
                $(Mustache.render(tpl, message)).insertBefore($buttons);
            });

        },

        /**
         * Render the document view
         * @returns {*}
         */
        render: function vChangeStateRender() {
            var scope=this;
            var workflow=this.model.get("workflow");
            var attributes=this.model.get("attributes");
            var transition=this.model.get("workflow").transition;
            var state=this.model.get("workflow").state;
            ViewDocument.prototype.render.apply(this, arguments);
            console.log("model", this.model);


            workflow.hasAttributes=(attributes.length > 0);
            if (transition) {
                this.$el.find(".dcpChangeState--graph").append(Mustache.render(this.htmlContent(), workflow));
                if (this.options.dialogWindow) {
                    this.options.dialogWindow.kendoWindow("center");
                    this.options.dialogWindow.kendoWindow("title", workflow.transition.label);
                }

                this.$el.append(Mustache.render(this.htmlButtons, workflow));
                this.$el.find(".dcpChangeState-button-ok").on("click" , function () {
                    scope.model.save();
                }).tooltip();
                this.$el.find(".dcpChangeState-button-cancel").on("click" , function () {
                    scope.$el.kendoWindow("close");
                });

                if (attributes.length === 0) {
                    _.defer(function () {
                        scope.model.save();
                    });
                }

            } else if (state) {
                this.$el.find(".dcpChangeState--graph").append(Mustache.render(this.htmlStateContent(), workflow));

                this.$el.find(".dcpChangeState-button-close").on("click" , function () {
                    console.log("Send reload trigger", scope.$el);
                    scope.$el.kendoWindow("close");
                }).tooltip();



                scope.$el.trigger("reload");
            }





        },


        htmlContent: function () {
            return '<div class="dcpChangeState--content-activity">' +
            '<span class="dcpChangeState--activity" style="background-color:{{transition.currentState.color}}">{{transition.currentState.displayValue}}</span>' +
            '<span class="dcpChangeState--transition {{^transition.id}}dcpChangeState--transition--invalid{{/transition.id}}" >{{transition.label}}</span>' +
            '<span><i class="fa fa-chevron-right {{^transition.id}}dcpChangeState--transition--invalid{{/transition.id}}"></i></span>' +
            '<span class="dcpChangeState--activity" style="background-color:{{transition.nextState.color}}">{{transition.nextState.displayValue}}</span>' +
            '</div>';
        },
        htmlStateContent: function () {
            return '<div class="dcpChangeState--content-activity">' +
            '<span class="dcpChangeState--success" >{{labels.success}}</span>' +
            '</div><div class="dcpChangeState--buttons">'+
            '<button title="{{labels.close}}" class="dcpChangeState-button-close btn btn-default btn-sm">' +
            '{{labels.close}}' +
            '</button> </div>' ;
        },

        htmlButtons : '<div class="dcpChangeState--buttons">' +
        '</button>{{#hasAttributes}}<button class="dcpChangeState-button-cancel btn btn-default btn-sm">{{labels.cancel}}</button>{{/hasAttributes}}'+
        '<button title="{{transition.label}}" class="dcpChangeState-button-ok btn {{#transition.id}}btn-primary{{/transition.id}}  {{^transition.id}}btn-danger{{/transition.id}} btn-sm">' +
        '{{labels.confirm}}</button>' +

        '</div>'
    });
});