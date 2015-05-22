define([
    'underscore',
    'dcpDocument/widgets/widget',
    'kendo/kendo.window'
], function (_)
{
    'use strict';

    $.widget("dcp.dcpLoading", {
        pc: 0,
        rest: 0,
        restItem: 0,
        currentWidget: null,
        doneItems: 0,
        original: null,


        _create: function dcpLoading_create()
        {
            this.initBar = this.element.find(".progress");
            this.$title = this.element.find(".dcpLoading--title");
            this.$header = this.element.find(".dcpLoading--header");

        },

        reset: function dcpLoadingReset()
        {
            this.initBar.show();
        },

        setTitle: function dcpLoadingsetTitle(val)
        {
            this.element.find('.dcpLoading--title').html(val);
        },

        modalMode: function dcpLoadingmodalMode()
        {
            var scopeElement = this.element;
            this.element.kendoWindow({
                modal: true,
                actions: [],
                visible: false,
                draggable: false,
                title: false,
                width: "400px",
                height: "100px"
            });
            this.element.show();
            this.element.data('kendoWindow').center();
            this.element.data('kendoWindow').open();
            _.defer(function ()
            {
                scopeElement.show();
            });

        },

        hide: function dcpLoadinghide()
        {

            this.element.hide();
            this.element.removeClass("dcpLoading--hide");
            if (this.element.data('kendoWindow')) {
                this.element.data('kendoWindow').close();
            }
            this.setPercent(0);
        },

        show: function dcpLoadingshow(text, pc)
        {

            if (text) {
                this.setLabel(text);
            }
            if (pc) {
                this.setPercent(pc);
            }

            this.element.show();
            this.$header.show().removeClass("dcpLoading--hide");
        },


        setLabel: function dcpLoadingsetLabel(text)
        {
            if (text) {
                this.$title.text(text);
            } else {
                this.$header.addClass("dcpLoading--hide");
            }
        },

        setPercent: function dcpLoadingsetPercent(pc)
        {
            var $initbar = this.initBar.find(".progress-bar");
            var rpc = Math.round(pc);
            this.pc = pc;
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(function ()
                {
                    $initbar.css("width", rpc + '%');
                });
            }

        },

        setNbItem: function dcpLoadingSetNbItem(restItem)
        {
            this.rest = 100 - this.pc;
            this.restItem = restItem;
        },

        addItem: function dcpLoadingAddItem(number)
        {
            number = number || 1;
            number = parseInt(number, 10);
            this.doneItems += number;
            var pv = (this.rest / this.restItem) * number;
            this.setPercent(this.pc + pv);
        },

        _destroy: function dcpLoading_destroy()
        {
            if (this.element.data('kendoWindow')) {
                this.element.data('kendoWindow').destroy();
            }

            this._trigger("destroy");
            this._super();
        }

    });
});