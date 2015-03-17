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
            var currentView = this;


            this.$el.append($('<div class="dcpTransitionGraph--from"/>' +
            '<div class="dcpTransitionGraph--to"/>'));

            this.displayCurrentState();

            // Init Events
            this.$el.on("mouseover", ".dcpTransitionGraph--to .dcpTransitionGraph_state", function ()
            {
                var to = $(this).data("to");
                currentView.$el.find(".dcpTransitionGraph__arrow--" + to).addClass("dcpTransitionGraph__arrow--selected");
            });
            this.$el.on("mouseout", ".dcpTransitionGraph--to .dcpTransitionGraph_state", function ()
            {

                currentView.$el.find(".dcpTransitionGraph__arrow").removeClass("dcpTransitionGraph__arrow--selected");
            });

            this.$el.on("click", ".dcpTransitionGraph--to .dcpTransitionGraph_state", function ()
            {
                var to = $(this).data("to");
                currentView.$el.trigger("viewTransition", to);

            });

            this.$el.find(".dcpTransitionGraph--to .dcpTransitionGraph_state").tooltip({
                placement: "top",
                html: true
            });

            if (!this.transitionGraphWindow) {
                this.transitionGraphWindow = this.$el.dcpDialog({
                    window: {
                        height: "auto",
                        close: function registerCloseEvent(e)
                        {
                            currentView.remove();
                        },
                        activate: function vTransitionGraph_windowActivate()
                        {
                            currentView.displayArrows();
                            currentView.previousHeight = currentView.$el.height();
                        },
                        resize: function vTransitionGraph_windowResize()
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
                this.$el.kendoWindow("title", i18n.___("Transition Graph", "ddui"));
                this.transitionGraphWindow.open();
            }
        },


        displayCurrentState: function vTransitionGraphdisplayCurrentState()
        {
            var tpl = '<div class="dcpTransitionGraph_state" data-to="{{id}}" title="{{title}}" style="border-color:{{color}}">{{displayValue}}</div>';
            var states = this.model.get("workflowStates");
            var currentState = this.model.get("state");
            var currentView = this;

            this.$el.find(".dcpTransitionGraph--from").append(Mustache.render(tpl, currentState));

            _.each(states, function (item)
            {
                if (item.transition) {
                    item.title = Mustache.render(i18n.___("Apply transition {{label}}", "ddui"), {label: item.transition.label});
                    currentView.$el.find(".dcpTransitionGraph--to").append(Mustache.render(tpl, item));
                }
            });
        },

        displayArrows: function vTransitionGraph_displayArrows()
        {
            var states = this.model.get("workflowStates");
            var currentView = this;

            var $from = this.$el.find(".dcpTransitionGraph--from .dcpTransitionGraph_state");
            var $to;

            this.$el.find(".dcpTransitionGraph__arrow").remove();

            this.$el.find(".dcpTransitionGraph--to").height(this.$el.height());
            this.$el.find(".dcpTransitionGraph--from").height(this.$el.height());
            _.each(states, function vTransitionGraph_connectStates(item)
            {
                if (item.transition) {
                    $to = currentView.$el.find(".dcpTransitionGraph--to .dcpTransitionGraph_state[data-to=" + item.id + "]");
                    currentView.connect($from.get(0), $to.get(0), 2, item.transition.label, item.id);

                }
            });


        },

        /**
         * return element top, left, width, height
         * @param el DOM element
         * @returns {*|jQuery}
         */
        getOffset: function vTransitionGraph_getOffset(el)
        {
            var offset = $(el).offset();
            offset.width = $(el).outerWidth();
            offset.height = $(el).outerHeight();
            return offset;
        },


        /**
         * draw a line connecting elements
         * @param div1 from div
         * @param div2 to div
         * @param thickness of the arraow
         * @param text label
         * @param id identifier
         */
        connect: function vTransitionGraph_connect(div1, div2, thickness, text, id)
        {
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
            var htmlLine = "<div class='dcpTransitionGraph__arrow dcpTransitionGraph__arrow--{{id}}' " +
                "style=' height:{{height}}px;left:{{left}}px; top:{{top}}px; width:{{width}}px;" +
                " -moz-transform:rotate({{angle}}deg); " +
                "-webkit-transform:rotate({{angle}}deg); " +
                "-o-transform:rotate({{angle}}deg); " +
                "-ms-transform:rotate({{angle}}deg); " +
                "transform:rotate({{angle}}deg);' ><div class='dcpTransitionGraph__arrow__label'>{{text}}</div>" +
                "<i class='dcpTransitionGraph__arrow__end fa fa-2x fa-caret-right'></i> </div>";


            this.$el.append(Mustache.render(htmlLine, {
                id: id,
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