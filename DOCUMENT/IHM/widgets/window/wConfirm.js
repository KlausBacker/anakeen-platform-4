define([
    'underscore',
    'mustache',
    'widgets/window/wWindow'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpConfirm", $.dcp.dcpWindow,  {

        options : {
            modal:true,
            templateData:window.dcp.documentData,
            messages : {
                textMessage:'Are you sure ?',
                htmlMessage: '',
                okMessage:"Ok",
                cancelMessage:"Cancel"
            },
            cancel : function () {
                this.destroy();
            },
            confirm : function () {

            },
            height : "150px"

        },


        _create : function () {
            this.options.templateData.messages=this.options.messages;
            this.currentWidget=$(Mustache.render(this._getWindowTemplate('confirm'), this.options.templateData));
            this.element.append(this.currentWidget);
            this.currentWidget.kendoWindow(this.options);
            this.currentWidget.data("kendoWindow").center();

            var scoppedCancel = _.bind(this.options.cancel, this);
            this.currentWidget.find('.button--cancel').kendoButton({
                click: function() {
                    scoppedCancel();
                }
            });
            var scoppedConfirm = _.bind(this.options.confirm, this);
            var scope=this;
            this.currentWidget.find('.button--ok').kendoButton({
                click: function() {
                    scoppedConfirm();
                    scope.destroy();
                }
            });

            this.element.data("dcpWindow", this);
           // this.currentWidget.find('button').kendoButton();
        }


    });
});