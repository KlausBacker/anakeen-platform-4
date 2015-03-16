define([
    'underscore',
    'jquery',
    'backbone',
    'mustache',
    'dcpDocument/i18n',
    'dcpDocument/widgets/window/wDialog'
], function (_, $, Backbone, Mustache, i18n)
{
    'use strict';

    return Backbone.View.extend({

        messages: [],

        templates: {
            htmlContent: '<div class="dcpTransition--content-activity">' +
            '{{transition.currentState.displayValue}} <span class="dcpTransition--activity" style="background-color:{{transition.currentState.color}}">&nbsp;</span>' +
            '<span class="dcpTransition--transition {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}" >{{transition.label}}</span>' +
            '<span><i class="fa fa-chevron-right {{^transition.id}}dcpTransition--transition--invalid{{/transition.id}}"></i></span>' +
            '<span class="dcpTransition--activity" style="background-color:{{transition.nextState.color}}">&nbsp;</span> {{transition.nextState.displayValue}}' +
            '</div>',

            htmlStateContent: '<div class="dcpTransition--content-activity">' +
            '<span class="dcpTransition--success" >{{labels.success}}</span>' +
            '</div>',

            htmlStateButtons: '<button title="{{labels.close}}" class="dcpTransition-button-close btn btn-default btn-sm">' +
            '{{labels.close}} </button>',

            htmlLoading: '<div class="dcpTransition--loading"><i class="fa fa-2x fa-spinner fa-spin"></i> {{labels.inprogress}}</div>',

            htmlButtons: '{{#hasAttributes}}<button class="dcpTransition-button-cancel btn btn-default btn-sm">{{labels.cancel}}</button>' +
            '<button title="{{transition.label}}" ' +
            'class="dcpTransition-button-ok btn {{#transition.id}}btn-primary{{/transition.id}}  {{^transition.id}}btn-danger{{/transition.id}} btn-sm">' +
            '{{labels.confirm}}</button>{{/hasAttributes}}'
        },


        remove: function vTransitionGraph_remove()
        {
            if (this.transitionGraphWindow && this.$el.data("kendoWindow")) {
                this.transitionGraphWindow.destroy();

            }

            //Call parent
            Backbone.View.prototype.remove.apply(this, arguments);
        },

        /**
         * Render the document view
         * @returns {*}
         */
        render: function vTransitionGraph_render()
        {
            var currentView = this,
                states = this.model.get("workflowStates");


            this.$el.append($('<div class="dcpTransitionGraph--from"/>' +
            '<div class="dcpTransitionGraph--to"/>'));

            this.displayCurrentState();
            if (!this.transitionGraphWindow) {
                this.transitionGraphWindow = this.$el.dcpDialog({
                    window: {
                        height: "auto",
                        close: function registerCloseEvent(e)
                        {
                            currentView.remove();
                        },
                        activate: function ()
                        {
                            _.delay(function ()
                            {
                                currentView.displayArrows();
                                currentView.previousHeight = currentView.$el.height();
                            }, 30);
                        },
                        resize: function ()
                        {
                            var isMaximized = currentView.$el.data("kendoWindow").options.isMaximized;

                            if (!isMaximized && currentView.isMaximizedNow) {

                                currentView.$el.find(".dcpTransitionGraph--to").height(currentView.previousHeight);
                                currentView.$el.find(".dcpTransitionGraph--from").height(currentView.previousHeight);
                                currentView.isMaximizedNow = false;
                            }
                            currentView.displayArrows();

                            if (!isMaximized) {
                                currentView.previousHeight = currentView.$el.height();
                            } else {
                                currentView.isMaximizedNow = true;
                            }
                        }
                    }
                }).data("dcpDialog");
                this.$el.kendoWindow("title", i18n.___("Transition Graph"));
                this.transitionGraphWindow.open();
            }
        },


        displayCurrentState: function vTransitionGraphdisplayCurrentState()
        {
            var tpl = '<div class="dcpTransitionGraph_state dcpTransitionGraph_state--{{id}}" style="border-color:{{color}}">{{displayValue}}</div>';
            var states = this.model.get("workflowStates");
            var currentState = this.model.get("state");
            var currentView = this;

            this.$el.find(".dcpTransitionGraph--from").append(Mustache.render(tpl, currentState));

            _.each(states, function (item)
            {
                if (item.transition) {
                    currentView.$el.find(".dcpTransitionGraph--to").append(Mustache.render(tpl, item));


                }
            });


        },

        displayArrows: function ()
        {
            var states = this.model.get("workflowStates");
            var currentView = this;

            var $from = this.$el.find(".dcpTransitionGraph--from .dcpTransitionGraph_state");
            var $to;

            this.$el.find(".dcpTransitionGraph__arrow").remove();

            this.$el.find(".dcpTransitionGraph--to").height(this.$el.height());
            this.$el.find(".dcpTransitionGraph--from").height(this.$el.height());
            _.each(states, function (item)
            {
                if (item.transition) {
                    $to = currentView.$el.find(".dcpTransitionGraph--to .dcpTransitionGraph_state--" + item.id);
                    currentView.connect($from.get(0), $to.get(0), 2, item.transition.label);

                }
            });


        },


        getOffset: function getOffset(el)
        { // return element top, left, width, height
            var offset = $(el).offset();
            offset.width = $(el).outerWidth();
            offset.height = $(el).outerHeight();
            return offset;
        },

        connect: function connect(div1, div2, thickness, text)
        { // draw a line connecting elements
            var off1 = this.getOffset(div1);
            var off2 = this.getOffset(div2);

            var origin = this.getOffset(this.$el.get(0));


            // bottom right
            var x2 = off1.left + off1.width - origin.left;
            var y2 = off1.top + (off1.height / 2) - origin.top;
            // top right
            var x1 = off2.left - origin.left;
            var y1 = off2.top + (off2.height / 2) - origin.top;
            // distance
            var length = Math.sqrt(((x2 - x1) * (x2 - x1)) + ((y2 - y1) * (y2 - y1)));
            // center
            var cx = ((x1 + x2) / 2) - (length / 2);
            var cy = ((y1 + y2) / 2) - (thickness / 2);
            // angle
            var angle = Math.atan2((y1 - y2), (x1 - x2)) * (180 / Math.PI);


            //
            var htmlLine = "<div class='dcpTransitionGraph__arrow' " +
                "style=' height:{{height}}px;left:{{left}}px; top:{{top}}px; width:{{width}}px;" +
                " -moz-transform:rotate({{angle}}deg); " +
                "-webkit-transform:rotate({{angle}}deg); " +
                "-o-transform:rotate({{angle}}deg); " +
                "-ms-transform:rotate({{angle}}deg); " +
                "transform:rotate({{angle}}deg);' ><div class='dcpTransitionGraph__arrow__label'>{{text}}</div>" +
                "<i class='dcpTransitionGraph__arrow__end fa fa-2x fa-caret-up'></i> </div>";


            this.$el.append(Mustache.render(htmlLine, {
                height: thickness,
                width: length,
                top: cy,
                left: cx,
                angle: angle,
                text: text
            }));
        }

    });
});